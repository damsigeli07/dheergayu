<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/payhere_config.php';
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (PAYHERE_MODE !== 'sandbox') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Local finalize allowed only in sandbox mode']);
    exit;
}

function getExistingCartId(mysqli $conn, ?int $userId, string $sessionId): ?int
{
    if ($userId) {
        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
    } else {
        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE session_id = ? LIMIT 1");
        $stmt->bind_param('s', $sessionId);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['cart_id'] : null;
}

function getCartItems(mysqli $conn, int $cartId): array
{
    $stmt = $conn->prepare("SELECT product_id, product_type, product_name, price, quantity, image FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function resolveInventoryProductId(mysqli $conn, string $productName): ?int
{
    $stmt = $conn->prepare("SELECT product_id FROM products WHERE name = ? LIMIT 1");
    $stmt->bind_param('s', $productName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['product_id'] : null;
}

function deductStock(mysqli $conn, int $productId, int $qty, string $productName): void
{
    $batchStmt = $conn->prepare("
        SELECT batch_id, batch_number, quantity
        FROM batches
        WHERE product_id = ? AND quantity > 0
        ORDER BY exp ASC, batch_id ASC
    ");
    $batchStmt->bind_param('i', $productId);
    $batchStmt->execute();
    $batches = $batchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $batchStmt->close();

    $available = 0;
    foreach ($batches as $b) {
        $available += (int)$b['quantity'];
    }
    if ($available < $qty) {
        throw new Exception('Insufficient stock for ' . $productName);
    }

    $left = $qty;
    $updateStmt = $conn->prepare("UPDATE batches SET quantity = quantity - ? WHERE product_id = ? AND batch_number = ?");
    foreach ($batches as $batch) {
        if ($left <= 0) {
            break;
        }
        $take = min($left, (int)$batch['quantity']);
        $batchNumber = (string)$batch['batch_number'];
        $updateStmt->bind_param('iis', $take, $productId, $batchNumber);
        $updateStmt->execute();
        $left -= $take;
    }
    $updateStmt->close();
}

try {
    $orderId = trim((string)($_POST['order_id'] ?? ''));
    if ($orderId === '') {
        throw new Exception('Missing order_id');
    }

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $sessionId = session_id();
    $cartId = getExistingCartId($conn, $userId, $sessionId);
    if (!$cartId) {
        throw new Exception('Cart not found');
    }

    $checkStmt = $conn->prepare("SELECT id FROM orders WHERE order_id = ? AND status = 'paid' LIMIT 1");
    $checkStmt->bind_param('s', $orderId);
    $checkStmt->execute();
    $alreadyPaid = $checkStmt->get_result()->num_rows > 0;
    $checkStmt->close();
    if ($alreadyPaid) {
        echo json_encode(['success' => true, 'message' => 'Order already finalized']);
        exit;
    }

    $items = getCartItems($conn, $cartId);
    if (empty($items)) {
        throw new Exception('Cart is empty');
    }

    $amount = 0.0;
    foreach ($items as $item) {
        $amount += ((float)$item['price']) * ((int)$item['quantity']);
    }
    $shipping = $amount > 5000 ? 0 : 250;
    $totalAmount = $amount + $shipping;

    $conn->begin_transaction();

    foreach ($items as $item) {
        $name = trim((string)$item['product_name']);
        $qty = (int)$item['quantity'];
        if ($qty <= 0 || $name === '') {
            continue;
        }
        $inventoryProductId = resolveInventoryProductId($conn, $name);
        if (!$inventoryProductId) {
            throw new Exception('Product not found in inventory: ' . $name);
        }
        deductStock($conn, $inventoryProductId, $qty, $name);
    }

    $orderItemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);
    $customerName = trim((string)($_SESSION['user_name'] ?? 'Guest User'));
    $customerEmail = trim((string)($_SESSION['user_email'] ?? ''));
    $customerPhone = trim((string)($_SESSION['user_phone'] ?? ''));

    $insertStmt = $conn->prepare("
        INSERT INTO orders (
            order_id, payment_id, user_id, amount, currency, payment_method, status,
            customer_name, customer_email, customer_phone, delivery_address, delivery_city, order_items
        ) VALUES (?, '', ?, ?, 'LKR', 'payhere', 'paid', ?, ?, ?, '', '', ?)
        ON DUPLICATE KEY UPDATE
            amount = VALUES(amount),
            status = 'paid',
            order_items = VALUES(order_items),
            updated_at = NOW()
    ");
    $insertStmt->bind_param(
        'sidssss',
        $orderId,
        $userId,
        $totalAmount,
        $customerName,
        $customerEmail,
        $customerPhone,
        $orderItemsJson
    );
    $insertStmt->execute();
    $insertStmt->close();

    $clearStmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $clearStmt->bind_param('i', $cartId);
    $clearStmt->execute();
    $clearStmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Order finalized locally']);
} catch (Exception $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        @$conn->rollback();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
