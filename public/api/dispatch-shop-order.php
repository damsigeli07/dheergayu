<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../config/config.php';

$orderId = trim($_POST['order_id'] ?? '');
if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'order_id required']);
    exit;
}

// Fetch the order
$stmt = $conn->prepare("SELECT order_items, dispatch_status FROM orders WHERE order_id = ? AND status = 'paid' LIMIT 1");
$stmt->bind_param('s', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}
if ($order['dispatch_status'] === 'dispatched') {
    echo json_encode(['success' => false, 'error' => 'Already dispatched']);
    exit;
}

// Parse order_items string e.g. "Samahan Herbal Drink x2, Paspanguwa Pack x1"
$items = [];
foreach (explode(',', $order['order_items']) as $part) {
    $part = trim($part);
    if (preg_match('/^(.+)\s+x(\d+)$/i', $part, $m)) {
        $items[] = ['name' => trim($m[1]), 'qty' => (int)$m[2]];
    }
}

// Reduce stock FEFO (soonest expiry first) for each item
foreach ($items as $item) {
    // Look up product_id
    $ps = $conn->prepare("SELECT product_id FROM products WHERE name = ? LIMIT 1");
    $ps->bind_param('s', $item['name']);
    $ps->execute();
    $prod = $ps->get_result()->fetch_assoc();
    $ps->close();
    if (!$prod) continue;

    $productId = (int)$prod['product_id'];
    $qty = $item['qty'];

    // FEFO: consume batches with soonest expiry first
    $bs = $conn->prepare("SELECT batch_id, quantity FROM batches WHERE product_id = ? AND quantity > 0 ORDER BY exp ASC, created_at ASC");
    $bs->bind_param('i', $productId);
    $bs->execute();
    $batches = $bs->get_result()->fetch_all(MYSQLI_ASSOC);
    $bs->close();

    foreach ($batches as $b) {
        if ($qty <= 0) break;
        if ((int)$b['quantity'] >= $qty) {
            $newQ = (int)$b['quantity'] - $qty;
            $u = $conn->prepare("UPDATE batches SET quantity = ? WHERE batch_id = ?");
            $u->bind_param('ii', $newQ, $b['batch_id']);
            $u->execute();
            $u->close();
            $qty = 0;
        } else {
            $qty -= (int)$b['quantity'];
            $zero = 0;
            $u = $conn->prepare("UPDATE batches SET quantity = ? WHERE batch_id = ?");
            $u->bind_param('ii', $zero, $b['batch_id']);
            $u->execute();
            $u->close();
        }
    }

    if ($qty > 0) {
        error_log("dispatch-shop-order: stock shortfall for '{$item['name']}', still needs $qty units");
    }
}

// Mark as dispatched
$upd = $conn->prepare("UPDATE orders SET dispatch_status = 'dispatched' WHERE order_id = ?");
$upd->bind_param('s', $orderId);
$upd->execute();
$upd->close();
$conn->close();

echo json_encode(['success' => true]);
