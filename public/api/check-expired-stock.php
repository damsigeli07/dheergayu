<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

// Accepts either order_items string (shop orders), product_id, or product_source
$orderItems    = trim($_POST['order_items'] ?? '');
$productId     = (int)($_POST['product_id'] ?? 0);
$productSource = trim($_POST['product_source'] ?? '');

$today = date('Y-m-d');
$expiredProducts = [];

if ($productSource) {
    $s = $conn->prepare("SELECT COUNT(*) AS cnt FROM batches WHERE product_source = ? AND exp < ?");
    $s->bind_param('ss', $productSource, $today);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    if ($row['cnt'] > 0) $expiredProducts[] = $productSource . ' products';
} elseif ($productId) {
    $s = $conn->prepare("SELECT COUNT(*) AS cnt FROM batches WHERE product_id = ? AND exp < ?");
    $s->bind_param('is', $productId, $today);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    if ($row['cnt'] > 0) $expiredProducts[] = 'this product';
} elseif ($orderItems) {
    // Parse "Product Name x2, Other Product x1"
    foreach (explode(',', $orderItems) as $part) {
        $part = trim($part);
        if (!preg_match('/^(.+)\s+x(\d+)$/i', $part, $m)) continue;
        $name = trim($m[1]);
        $ps = $conn->prepare("SELECT product_id FROM products WHERE name = ? LIMIT 1");
        $ps->bind_param('s', $name);
        $ps->execute();
        $prod = $ps->get_result()->fetch_assoc();
        $ps->close();
        if (!$prod) continue;
        $pid = (int)$prod['product_id'];
        $bs = $conn->prepare("SELECT COUNT(*) AS cnt FROM batches WHERE product_id = ? AND exp < ?");
        $bs->bind_param('is', $pid, $today);
        $bs->execute();
        $row = $bs->get_result()->fetch_assoc();
        $bs->close();
        if ($row['cnt'] > 0) $expiredProducts[] = $name;
    }
}

$conn->close();
echo json_encode([
    'has_expired' => !empty($expiredProducts),
    'products'    => $expiredProducts,
]);
