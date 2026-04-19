<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$orders = [];

$stmt = $conn->prepare("
    SELECT id, order_id, amount, currency, status, payment_method, order_items, created_at
    FROM orders
    WHERE user_id = ?
      AND order_items NOT LIKE 'Consultation #%'
      AND order_items NOT LIKE 'Treatment #%'
      AND order_items NOT LIKE 'Treatment Plan #%'
      AND order_items NOT LIKE 'Session #%'
      AND order_items NOT LIKE 'Dispensed:%'
    ORDER BY created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();

function deliveryStatus(string $status, string $createdAt): string
{
    if ($status !== 'paid') {
        return 'Awaiting Payment';
    }
    return 'Order Confirmed';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/products.css?v=<?php echo time(); ?>">
    <style>
        .orders-wrap { max-width: 1100px; margin: 30px auto; padding: 0 15px; }
        .orders-card { background: #fff; border-radius: 12px; padding: 18px; margin-bottom: 16px; box-shadow: 0 8px 22px rgba(0,0,0,.08); }
        .orders-top { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
        .orders-id { font-weight: 700; color: #5d7240; }
        .orders-status { font-size: 13px; font-weight: 700; color: #fff; padding: 4px 10px; border-radius: 20px; background: #6f8f4f; }
        .orders-meta { font-size: 14px; color: #555; margin: 4px 0; }
        .orders-items { margin-top: 10px; padding-left: 18px; color: #333; }
        .orders-delivery { margin-top: 10px; background: #f3f8ed; border-left: 4px solid #6f8f4f; padding: 8px 10px; font-size: 14px; color: #334; }
        .empty-box { background: #fff; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 8px 22px rgba(0,0,0,.08); }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="/dheergayu/public/assets/images/Patient/logo_modern.png" alt="Dheergayu Logo">
                <h1>DHEERGAYU <br> <span>AYURVEDIC MANAGEMENT CENTER</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="/dheergayu/app/Views/Patient/home.php" class="back-btn">← Back to Home</a>
            </div>
        </div>
    </header>

    <div class="orders-wrap">
        <div class="page-header">
            <h1 class="main-title">My Orders</h1>
            <div class="page-description">
                <p>Track your product purchases and delivery progress handled by our third-party delivery partner.</p>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-box">
                <h3>No orders yet</h3>
                <p>Once you place a product order, it will appear here.</p>
                <a class="back-btn" href="/dheergayu/app/Views/Patient/products.php">Browse Products</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <?php
                    $decodedItems = json_decode((string)$order['order_items'], true);
                    $items = is_array($decodedItems) ? $decodedItems : [];
                    $delivery = deliveryStatus((string)$order['status'], (string)$order['created_at']);
                ?>
                <div class="orders-card">
                    <div class="orders-top">
                        <div class="orders-id">Order #<?= htmlspecialchars($order['order_id']) ?></div>
                        <div class="orders-status"><?= htmlspecialchars(ucfirst($order['status'])) ?></div>
                    </div>
                    <div class="orders-meta">Total: Rs. <?= number_format((float)$order['amount'], 2) ?> (<?= htmlspecialchars($order['currency']) ?>)</div>
                    <div class="orders-meta">Payment: <?= htmlspecialchars($order['payment_method']) ?> | Date: <?= htmlspecialchars(date('M d, Y h:i A', strtotime($order['created_at']))) ?></div>

                    <?php if (!empty($items)): ?>
                        <ul class="orders-items">
                            <?php foreach ($items as $item): ?>
                                <li>
                                    <?= htmlspecialchars((string)($item['product_name'] ?? $item['name'] ?? 'Product')) ?>
                                    x<?= (int)($item['quantity'] ?? 1) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="orders-meta">Items: <?= htmlspecialchars((string)$order['order_items']) ?></div>
                    <?php endif; ?>

                    <div class="orders-delivery">
                        Delivery Partner: QuickLanka Express (3rd Party) <br>
                        Delivery Status: <strong><?= htmlspecialchars($delivery) ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
