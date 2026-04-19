<?php
// /dheergayu/app/Views/Patient/payment_success.php
session_start();
require_once __DIR__ . '/../../../config/config.php';

$orderId   = htmlspecialchars($_GET['order_id']   ?? '');
$simulated = isset($_GET['simulated']);

// ── Server-side: finalise order if not already done ───────────────────────────
// This handles the PayHere return_url redirect.
// If IPN already processed it, process-order.php will detect 'already_done' and skip.
$processResult = null;
if ($orderId && !$simulated) {
    // For real PayHere returns we only have order_id in GET.
    // Session may still have the pending order data saved by payment.php.
    $postData = http_build_query([
        'action'         => 'process',
        'order_id'       => $orderId,
        'customer_name'  => $_SESSION['po_name']    ?? '',
        'customer_email' => $_SESSION['po_email']   ?? '',
        'customer_phone' => $_SESSION['po_phone']   ?? '',
        'address'        => $_SESSION['po_address'] ?? '',
        'city'           => $_SESSION['po_city']    ?? '',
        'amount'         => $_SESSION['po_amount']  ?? 0,
        'user_id'        => $_SESSION['user_id']    ?? 0,
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" .
                         "Cookie: PHPSESSID=" . session_id() . "\r\n",
            'content' => $postData,
            'timeout' => 10,
        ]
    ]);

    $raw = @file_get_contents(
        'http://localhost/dheergayu/public/api/process-order.php',
        false, $ctx
    );
    if ($raw) {
        $processResult = json_decode($raw, true);
    }
}

// Fetch order details for display
$orderRow = null;
if ($orderId) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? LIMIT 1");
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $orderRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Dheergayu</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .success-container {
            background: white; border-radius: 20px; padding: 50px 40px;
            text-align: center; max-width: 520px; width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }
        .success-icon {
            width: 100px; height: 100px; background: #4CAF50; border-radius: 50%;
            margin: 0 auto 28px; display: flex; align-items: center; justify-content: center;
            animation: scaleIn .5s ease;
        }
        .success-icon::before { content:'✓'; font-size:58px; color:white; font-weight:bold; }
        @keyframes scaleIn {
            0%  { transform:scale(0); }
            50% { transform:scale(1.1); }
            100%{ transform:scale(1); }
        }
        h1 { color:#333; font-size:2rem; margin-bottom:12px; }
        .order-box {
            background:#f5f5f5; padding:16px; border-radius:10px;
            margin:22px 0; font-family:monospace; font-size:1rem; color:#555;
        }
        .order-box strong { color:#333; }
        .info-grid { display:grid; gap:14px; margin:22px 0; }
        .info-box {
            background:#e8f5e9; border-left:4px solid #4CAF50;
            padding:14px; border-radius:5px; text-align:left;
        }
        .info-box h3 { color:#2e7d32; margin-bottom:6px; font-size:.95rem; }
        .info-box p  { color:#555; font-size:.87rem; line-height:1.5; }
        .detail-row  { display:flex; justify-content:space-between; padding:6px 0;
                       border-bottom:1px solid #f0f0f0; font-size:.92rem; }
        .detail-row:last-child { border:none; }
        .detail-label { color:#666; }
        .detail-value { font-weight:600; color:#333; }
        .btn {
            display:inline-block; padding:13px 34px;
            background:linear-gradient(135deg,#8B7355,#A0916B);
            color:white; text-decoration:none; border-radius:10px;
            font-weight:600; font-size:.95rem; transition:all .3s; margin:8px;
        }
        .btn:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(139,115,85,.4); }
        .btn-sec { background:linear-gradient(135deg,#667eea,#764ba2); }
        .message { color:#666; font-size:1rem; line-height:1.6; margin-bottom:20px; }
    </style>
</head>
<body>
<div class="success-container">
    <div class="success-icon"></div>
    <h1>Payment Successful!</h1>

    <div class="order-box">
        <strong>Order ID:</strong> <?= $orderId ?: 'N/A' ?>
    </div>

    <?php if ($orderRow): ?>
    <div style="background:#f9f9f9;border-radius:10px;padding:18px;margin:18px 0;text-align:left;">
        <div class="detail-row">
            <span class="detail-label">Amount Paid</span>
            <span class="detail-value">Rs. <?= number_format((float)$orderRow['amount'], 2) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Customer</span>
            <span class="detail-value"><?= htmlspecialchars($orderRow['customer_name'] ?? '—') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">City</span>
            <span class="detail-value"><?= htmlspecialchars($orderRow['delivery_city'] ?? '—') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Items</span>
            <span class="detail-value" style="max-width:250px;text-align:right;">
                <?= htmlspecialchars($orderRow['order_items'] ?? '—') ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value" style="color:#4CAF50;">
                <?= ucfirst($orderRow['status'] ?? 'paid') ?>
            </span>
        </div>
    </div>
    <?php else: ?>
    <p class="message">Your order has been confirmed and will be processed shortly.</p>
    <?php endif; ?>

    <div class="info-grid">
        <div class="info-box">
            <h3>📦 Delivery</h3>
            <p>Your order will be delivered within 3–5 business days.</p>
        </div>
    </div>

    <div style="margin-top:30px;">
        <a href="/dheergayu/app/Views/Patient/home.php"     class="btn">Back to Home</a>
        <a href="/dheergayu/app/Views/Patient/products.php" class="btn btn-sec">Continue Shopping</a>
    </div>
</div>

<script>
    // Ensure cart badge is cleared (belt-and-suspenders)
    fetch('/dheergayu/public/api/cart-api.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'clear' })
    }).catch(() => {});
</script>
</body>
</html>