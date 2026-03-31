<?php
require_once __DIR__ . '/../../../config/payhere_config.php';
require_once __DIR__ . '/../../../config/config.php';

function logPayhere(string $message): void
{
    $logFile = __DIR__ . '/../../../logs/payhere_notifications.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

function getCartItems(mysqli $conn, int $cartId): array
{
    $stmt = $conn->prepare("SELECT product_id, product_type, product_name, price, quantity, image FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function resolveInventoryProductId(mysqli $conn, array $item): ?int
{
    $candidateName = trim((string)($item['product_name'] ?? ''));
    if ($candidateName === '') {
        return null;
    }
    $stmt = $conn->prepare("SELECT product_id FROM products WHERE name = ? LIMIT 1");
    $stmt->bind_param('s', $candidateName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['product_id'] : null;
}

function deductBatchStock(mysqli $conn, int $productId, int $qtyNeeded, string $productName): void
{
    $batchStmt = $conn->prepare("
        SELECT batch_number, quantity
        FROM batches
        WHERE product_id = ? AND quantity > 0
        ORDER BY exp ASC, batch_id ASC
    ");
    $batchStmt->bind_param('i', $productId);
    $batchStmt->execute();
    $batches = $batchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $batchStmt->close();

    $available = 0;
    foreach ($batches as $batch) {
        $available += (int)$batch['quantity'];
    }
    if ($available < $qtyNeeded) {
        throw new Exception('Insufficient stock for "' . $productName . '". Required: ' . $qtyNeeded . ', Available: ' . $available);
    }

    $remaining = $qtyNeeded;
    $updateStmt = $conn->prepare("UPDATE batches SET quantity = quantity - ? WHERE product_id = ? AND batch_number = ?");
    foreach ($batches as $batch) {
        if ($remaining <= 0) {
            break;
        }
        $take = min($remaining, (int)$batch['quantity']);
        $batchNumber = (string)$batch['batch_number'];
        $updateStmt->bind_param('iis', $take, $productId, $batchNumber);
        $updateStmt->execute();
        $remaining -= $take;
    }
    $updateStmt->close();
}

try {
    $merchantId = $_POST['merchant_id'] ?? '';
    $orderId = $_POST['order_id'] ?? '';
    $payhereAmount = $_POST['payhere_amount'] ?? '';
    $payhereCurrency = $_POST['payhere_currency'] ?? '';
    $statusCode = (string)($_POST['status_code'] ?? '');
    $md5sig = $_POST['md5sig'] ?? '';
    $paymentId = $_POST['payment_id'] ?? '';
    $customUserId = (int)($_POST['custom_1'] ?? 0);
    $customCartId = (int)($_POST['custom_2'] ?? 0);

    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $customerName = trim($firstName . ' ' . $lastName);
    $customerEmail = $_POST['email'] ?? '';
    $customerPhone = $_POST['phone'] ?? '';
    $deliveryAddress = $_POST['address'] ?? '';
    $deliveryCity = $_POST['city'] ?? '';

    logPayhere(json_encode($_POST));

    if (!verifyPayherePayment($merchantId, $orderId, $payhereAmount, $payhereCurrency, $statusCode, $md5sig)) {
        http_response_code(400);
        echo 'Invalid payment verification';
        exit;
    }

    if ($orderId === '') {
        throw new Exception('Missing order_id');
    }

    $statusMap = [
        '2' => 'paid',
        '0' => 'pending',
        '-1' => 'cancelled',
        '-2' => 'failed',
        '-3' => 'failed',
    ];
    $orderStatus = $statusMap[$statusCode] ?? 'failed';

    if ($orderStatus !== 'paid') {
        $stmt = $conn->prepare("
            INSERT INTO orders (
                order_id, payment_id, user_id, amount, currency, payment_method, status,
                customer_name, customer_email, customer_phone, delivery_address, delivery_city, order_items
            ) VALUES (?, ?, ?, ?, ?, 'payhere', ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                payment_id = VALUES(payment_id),
                status = VALUES(status),
                amount = VALUES(amount),
                currency = VALUES(currency),
                customer_name = VALUES(customer_name),
                customer_email = VALUES(customer_email),
                customer_phone = VALUES(customer_phone),
                delivery_address = VALUES(delivery_address),
                delivery_city = VALUES(delivery_city),
                order_items = VALUES(order_items),
                updated_at = NOW()
        ");
        $emptyItems = '';
        $stmt->bind_param(
            'ssidssssssss',
            $orderId,
            $paymentId,
            $customUserId,
            $payhereAmount,
            $payhereCurrency,
            $orderStatus,
            $customerName,
            $customerEmail,
            $customerPhone,
            $deliveryAddress,
            $deliveryCity,
            $emptyItems
        );
        $stmt->execute();
        $stmt->close();
        echo 'Payment status recorded';
        exit;
    }

    if ($customCartId <= 0) {
        throw new Exception('Missing cart_id in custom_2');
    }

    $conn->begin_transaction();

    $items = getCartItems($conn, $customCartId);
    if (empty($items)) {
        throw new Exception('No cart items found for paid order');
    }

    $orderItemsText = json_encode($items, JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("
        INSERT INTO orders (
            order_id, payment_id, user_id, amount, currency, payment_method, status,
            customer_name, customer_email, customer_phone, delivery_address, delivery_city, order_items
        ) VALUES (?, ?, ?, ?, ?, 'payhere', 'paid', ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            payment_id = VALUES(payment_id),
            status = 'paid',
            amount = VALUES(amount),
            currency = VALUES(currency),
            customer_name = VALUES(customer_name),
            customer_email = VALUES(customer_email),
            customer_phone = VALUES(customer_phone),
            delivery_address = VALUES(delivery_address),
            delivery_city = VALUES(delivery_city),
            order_items = VALUES(order_items),
            updated_at = NOW()
    ");
    $stmt->bind_param(
        'ssidsssssss',
        $orderId,
        $paymentId,
        $customUserId,
        $payhereAmount,
        $payhereCurrency,
        $customerName,
        $customerEmail,
        $customerPhone,
        $deliveryAddress,
        $deliveryCity,
        $orderItemsText
    );
    $stmt->execute();
    $stmt->close();

    foreach ($items as $item) {
        $qty = (int)($item['quantity'] ?? 0);
        if ($qty <= 0) {
            continue;
        }
        $inventoryProductId = resolveInventoryProductId($conn, $item);
        if (!$inventoryProductId) {
            throw new Exception('Product not found in inventory: ' . ($item['product_name'] ?? 'Unknown'));
        }
        deductBatchStock($conn, $inventoryProductId, $qty, (string)$item['product_name']);
    }

    $clearStmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $clearStmt->bind_param('i', $customCartId);
    $clearStmt->execute();
    $clearStmt->close();

    $conn->commit();
    echo 'Payment processed successfully';
} catch (Exception $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        @$conn->rollback();
    }
    logPayhere('ERROR: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error processing payment';
}
?>