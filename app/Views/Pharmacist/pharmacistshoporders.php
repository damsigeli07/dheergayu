<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../includes/auth_pharmacist.php';
require_once __DIR__ . '/../../../config/config.php';

// Fetch paid shop orders (exclude dispensed/appointment/treatment rows)
$shopOrders = [];
$res = $conn->query("
    SELECT o.order_id,
           COALESCE(NULLIF(o.customer_name,''), CONCAT(p.first_name, ' ', p.last_name)) AS customer_name,
           COALESCE(NULLIF(o.customer_phone,''), '') AS customer_phone,
           COALESCE(NULLIF(o.customer_email,''), p.email) AS customer_email,
           o.delivery_address, o.delivery_city, o.amount, o.order_items,
           o.created_at, o.dispatch_status
    FROM orders o
    LEFT JOIN patients p ON p.id = o.user_id
    WHERE o.status = 'paid'
      AND o.order_items NOT LIKE 'Dispensed:%'
      AND o.order_items NOT LIKE 'Consultation #%'
      AND o.order_items NOT LIKE 'Treatment #%'
      AND o.order_items NOT LIKE 'Treatment Plan #%'
      AND o.order_items NOT LIKE 'Session #%'
    ORDER BY o.created_at DESC
");
if ($res) {
    while ($row = $res->fetch_assoc()) $shopOrders[] = $row;
    $res->free();
}
$conn->close();

$pendingOrders    = array_values(array_filter($shopOrders, fn($o) => $o['dispatch_status'] === 'pending'));
$dispatchedOrders = array_values(array_filter($shopOrders, fn($o) => $o['dispatch_status'] === 'dispatched'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Orders - Pharmacist</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacistshoporders.css">
</head>
<body class="has-sidebar">
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        <nav class="navigation">
            <a href="pharmacisthome.php" class="nav-btn">Home</a>
            <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
            <a href="pharmacistorders.php" class="nav-btn">Orders</a>
            <button class="nav-btn active">Shop Orders</button>
            <a href="pharmacistreports.php" class="nav-btn">Reports</a>
            <a href="pharmacistrequest.php" class="nav-btn">Request</a>
            <a href="pharmacisttreatmentprep.php" class="nav-btn">Treatment Prep</a>
        </nav>
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Pharmacist</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <h2 class="section-title">Shop Orders</h2>
        <p class="section-desc">Customer purchases to be handed to the delivery partner.</p>

        <div class="tabs">
            <button class="tab active" data-tab="pending">Pending (<?= count($pendingOrders) ?>)</button>
            <button class="tab" data-tab="dispatched">Dispatched (<?= count($dispatchedOrders) ?>)</button>
        </div>

        <!-- Pending -->
        <div id="tab-pending" class="tab-section">
            <?php if (!empty($pendingOrders)): ?>
                <?php foreach ($pendingOrders as $order): ?>
                <div class="order-card pending" data-order-id="<?= htmlspecialchars($order['order_id']) ?>" data-items="<?= htmlspecialchars($order['order_items']) ?>">
                    <div class="card-header">
                        <div>
                            <span class="order-id">#<?= htmlspecialchars($order['order_id']) ?></span>
                            <span class="badge badge-pending">Pending Dispatch</span>
                        </div>
                        <span class="order-date"><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="detail-row"><span class="label">Customer</span><span><?= htmlspecialchars($order['customer_name']) ?> — <?= htmlspecialchars($order['customer_phone']) ?></span></div>
                        <div class="detail-row"><span class="label">Deliver to</span><span><?= htmlspecialchars($order['delivery_address']) ?>, <?= htmlspecialchars($order['delivery_city']) ?></span></div>
                        <div class="detail-row"><span class="label">Items</span><span><?= htmlspecialchars($order['order_items']) ?></span></div>
                        <div class="detail-row"><span class="label">Total</span><span class="amount">Rs. <?= number_format((float)$order['amount'], 2) ?> (LKR)</span></div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-dispatch" onclick="dispatchOrder('<?= htmlspecialchars($order['order_id']) ?>', this)">
                            Dispatch to Delivery
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📦</div><div>No pending shop orders.</div></div>
            <?php endif; ?>
        </div>

        <!-- Dispatched -->
        <div id="tab-dispatched" class="tab-section" style="display:none;">
            <?php if (!empty($dispatchedOrders)): ?>
                <?php foreach ($dispatchedOrders as $order): ?>
                <div class="order-card dispatched" data-order-id="<?= htmlspecialchars($order['order_id']) ?>">
                    <div class="card-header">
                        <div>
                            <span class="order-id">#<?= htmlspecialchars($order['order_id']) ?></span>
                            <span class="badge badge-dispatched">Dispatched</span>
                        </div>
                        <span class="order-date"><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="detail-row"><span class="label">Customer</span><span><?= htmlspecialchars($order['customer_name']) ?> — <?= htmlspecialchars($order['customer_phone']) ?></span></div>
                        <div class="detail-row"><span class="label">Deliver to</span><span><?= htmlspecialchars($order['delivery_address']) ?>, <?= htmlspecialchars($order['delivery_city']) ?></span></div>
                        <div class="detail-row"><span class="label">Items</span><span><?= htmlspecialchars($order['order_items']) ?></span></div>
                        <div class="detail-row"><span class="label">Total</span><span class="amount">Rs. <?= number_format((float)$order['amount'], 2) ?> (LKR)</span></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">✅</div><div>No dispatched orders yet.</div></div>
            <?php endif; ?>
        </div>
    </main>

    <div id="toast" class="toast" style="display:none;"></div>

    <script>
        document.querySelectorAll('.tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-section').forEach(s => s.style.display = 'none');
                this.classList.add('active');
                document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
            });
        });

        async function dispatchOrder(orderId, btn) {
            // Check for expired stock first
            const card = document.querySelector('.order-card[data-order-id="' + orderId + '"]');
            const orderItems = card ? card.getAttribute('data-items') : '';
            if (orderItems) {
                const chk = new FormData();
                chk.append('order_items', orderItems);
                const chkRes = await fetch('/dheergayu/public/api/check-expired-stock.php', { method: 'POST', body: chk });
                const chkData = await chkRes.json();
                if (chkData.has_expired) {
                    if (!confirm('⚠️ Warning: Some items in this order have EXPIRED batches in stock.\n\nExpired stock found for: ' + chkData.products.join(', ') + '\n\nPlease remove expired batches from inventory first.\n\nProceed anyway?')) return;
                } else {
                    if (!confirm('Dispatch order #' + orderId + '?\n\nThis will reduce stock from inventory (FEFO).')) return;
                }
            } else {
                if (!confirm('Dispatch order #' + orderId + '?\n\nThis will reduce stock from inventory (FEFO).')) return;
            }
            btn.disabled = true;
            btn.textContent = 'Dispatching...';
            try {
                const fd = new FormData();
                fd.append('order_id', orderId);
                const res = await fetch('/dheergayu/public/api/dispatch-shop-order.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    const card = document.querySelector('.order-card[data-order-id="' + orderId + '"]');
                    card.remove();
                    showToast('Order dispatched successfully.');
                    // Update pending count
                    const pendingTab = document.querySelector('.tab[data-tab="pending"]');
                    const pendingCount = document.querySelectorAll('#tab-pending .order-card').length;
                    pendingTab.textContent = 'Pending (' + pendingCount + ')';
                    if (pendingCount === 0) {
                        document.getElementById('tab-pending').innerHTML = '<div class="empty-state"><div class="empty-icon">📦</div><div>No pending shop orders.</div></div>';
                    }
                } else {
                    alert(data.error || 'Failed to dispatch order.');
                    btn.disabled = false;
                    btn.textContent = 'Dispatch to Delivery';
                }
            } catch (e) {
                alert('Server error. Please try again.');
                btn.disabled = false;
                btn.textContent = 'Dispatch to Delivery';
            }
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 3000);
        }
    </script>
</body>
</html>
