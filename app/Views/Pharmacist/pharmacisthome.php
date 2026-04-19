<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../includes/auth_pharmacist.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/ConsultationFormModel.php';

$db = $conn;

$consultations = [];
$dispatchStatuses = [];

if (!$db->connect_error) {
    $consultationModel = new ConsultationFormModel($db);
    $all = $consultationModel->getAllConsultationForms();
    if (is_array($all)) {
        $consultations = array_values(array_filter($all, function($c) {
            $p = $c['personal_products'] ?? '';
            if (empty($p)) return false;
            $d = json_decode($p, true);
            return is_array($d) && count($d) > 0;
        }));
    }
    $dq = $db->query("SELECT consultation_id, status FROM consultation_dispatches");
    if ($dq) {
        while ($row = $dq->fetch_assoc()) $dispatchStatuses[(int)$row['consultation_id']] = $row['status'];
        $dq->free();
    }
}

$pendingOrders = [];
$dispatchedOrders = [];
foreach ($consultations as $c) {
    if (!empty($dispatchStatuses[$c['id']]) && $dispatchStatuses[$c['id']] === 'Dispatched') {
        $dispatchedOrders[] = $c;
    } else {
        $pendingOrders[] = $c;
    }
}

// Inventory alerts
$todayStr = date('Y-m-d');
$soonStr = date('Y-m-d', strtotime('+30 days'));
$lowStockItems = [];
$expiringSoonItems = [];

if (!$db->connect_error) {
    $sq = $db->query("
        SELECT p.name,
               COALESCE(SUM(CASE WHEN b.exp IS NULL OR b.exp >= '$todayStr' THEN b.quantity ELSE 0 END), 0) AS avail_qty,
               MIN(CASE WHEN b.exp IS NOT NULL AND b.exp >= '$todayStr' THEN b.exp ELSE NULL END) AS earliest_exp
        FROM products p
        LEFT JOIN batches b ON b.product_id = p.product_id
        GROUP BY p.product_id, p.name
        ORDER BY avail_qty ASC, earliest_exp ASC
    ");
    if ($sq) {
        while ($row = $sq->fetch_assoc()) {
            $qty = (int)$row['avail_qty'];
            if ($qty <= 15) $lowStockItems[] = ['name' => $row['name'], 'qty' => $qty];
            if (!empty($row['earliest_exp']) && $row['earliest_exp'] <= $soonStr) {
                $daysLeft = (int)round((strtotime($row['earliest_exp']) - time()) / 86400);
                $expiringSoonItems[] = ['name' => $row['name'], 'days' => max(0, $daysLeft)];
            }
        }
        $sq->free();
    }
    $db->close();
}

$pendingCount = count($pendingOrders);
$dispatchedCount = count($dispatchedOrders);
$lowStockCount = count($lowStockItems);
$expiringSoonCount = count($expiringSoonItems);

$recentPending = array_slice($pendingOrders, 0, 3);
$recentDispatched = array_slice($dispatchedOrders, 0, 2);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacisthome.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>

        <nav class="navigation">
            <button class="nav-btn active">Home</button>
            <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
            <a href="pharmacistorders.php" class="nav-btn">Orders</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-container">
            <!-- Welcome Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Pharmacist Dashboard</h1>
                <p class="dashboard-subtitle">Welcome back! Here's your overview for today.</p>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card pending-card">
                    <div class="card-icon">📋</div>
                    <div class="card-content">
                        <h3 class="card-label">Pending Orders</h3>
                        <p class="card-value"><?= $pendingCount ?></p>
                        <span class="card-change">Awaiting dispatch</span>
                    </div>
                </div>

                <div class="summary-card dispatched-card">
                    <div class="card-icon">✅</div>
                    <div class="card-content">
                        <h3 class="card-label">Dispatched Orders</h3>
                        <p class="card-value"><?= $dispatchedCount ?></p>
                        <span class="card-change">Ready for pickup</span>
                    </div>
                </div>

                <div class="summary-card low-stock-card">
                    <div class="card-icon">⚠️</div>
                    <div class="card-content">
                        <h3 class="card-label">Low Stock Items</h3>
                        <p class="card-value"><?= $lowStockCount ?></p>
                        <span class="card-change">Requires attention</span>
                    </div>
                </div>

                <div class="summary-card expiring-card">
                    <div class="card-icon">⏰</div>
                    <div class="card-content">
                        <h3 class="card-label">Expiring Soon</h3>
                        <p class="card-value"><?= $expiringSoonCount ?></p>
                        <span class="card-change">Within 30 days</span>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Orders Section -->
                <div class="orders-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Orders</h2>
                        <a href="pharmacistorders.php" class="view-all-link">View All →</a>
                    </div>

                    <!-- Pending Orders -->
                    <div class="order-group">
                        <h3 class="order-group-title">
                            <span class="status-badge pending-badge">Pending</span>
                            <span class="order-count"><?= $pendingCount ?> order<?= $pendingCount != 1 ? 's' : '' ?></span>
                        </h3>

                        <div class="order-list">
                            <?php if (empty($recentPending)): ?>
                                <p style="color:#666;padding:1rem 0;">No pending orders.</p>
                            <?php else: ?>
                                <?php foreach ($recentPending as $order): ?>
                                    <?php
                                    $medicines = json_decode($order['personal_products'] ?? '[]', true);
                                    if (!is_array($medicines)) $medicines = [];
                                    $patientName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
                                    if (!$patientName) $patientName = 'Patient #' . $order['id'];
                                    ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <span class="order-id">Consultation #<?= $order['id'] ?></span>
                                            <span class="order-date"><?= htmlspecialchars($order['created_at'] ?? '') ?></span>
                                        </div>
                                        <div class="order-patient">
                                            <strong>Patient:</strong> <?= htmlspecialchars($patientName) ?>
                                        </div>
                                        <div class="order-medicines">
                                            <?php foreach ($medicines as $med): ?>
                                                <?php $mName = is_array($med) ? ($med['product'] ?? '') : $med; $mQty = is_array($med) ? ($med['qty'] ?? 1) : 1; if (!$mName) continue; ?>
                                                <span class="medicine-tag"><?= htmlspecialchars($mName) ?> x<?= (int)$mQty ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="order-footer">
                                            <span class="consultation-ref">Consultation #<?= $order['id'] ?></span>
                                            <a href="pharmacistorders.php" class="action-button dispatch-btn">View Orders</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dispatched Orders -->
                    <div class="order-group">
                        <h3 class="order-group-title">
                            <span class="status-badge dispatched-badge">Dispatched</span>
                            <span class="order-count"><?= $dispatchedCount ?> order<?= $dispatchedCount != 1 ? 's' : '' ?></span>
                        </h3>

                        <div class="order-list">
                            <?php if (empty($recentDispatched)): ?>
                                <p style="color:#666;padding:1rem 0;">No dispatched orders.</p>
                            <?php else: ?>
                                <?php foreach ($recentDispatched as $order): ?>
                                    <?php
                                    $medicines = json_decode($order['personal_products'] ?? '[]', true);
                                    if (!is_array($medicines)) $medicines = [];
                                    $patientName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
                                    if (!$patientName) $patientName = 'Patient #' . $order['id'];
                                    ?>
                                    <div class="order-card dispatched">
                                        <div class="order-header">
                                            <span class="order-id">Consultation #<?= $order['id'] ?></span>
                                            <span class="order-date"><?= htmlspecialchars($order['created_at'] ?? '') ?></span>
                                        </div>
                                        <div class="order-patient">
                                            <strong>Patient:</strong> <?= htmlspecialchars($patientName) ?>
                                        </div>
                                        <div class="order-medicines">
                                            <?php foreach ($medicines as $med): ?>
                                                <?php $mName = is_array($med) ? ($med['product'] ?? '') : $med; $mQty = is_array($med) ? ($med['qty'] ?? 1) : 1; if (!$mName) continue; ?>
                                                <span class="medicine-tag"><?= htmlspecialchars($mName) ?> x<?= (int)$mQty ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="order-footer">
                                            <span class="consultation-ref">Consultation #<?= $order['id'] ?></span>
                                            <span class="dispatched-label">✓ Dispatched</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Alerts Section -->
                <div class="alerts-section">
                    <!-- Low Stock Alert -->
                    <div class="alert-card low-stock-alert">
                        <div class="alert-header">
                            <div class="alert-icon-wrapper">
                                <span class="alert-icon">⚠️</span>
                            </div>
                            <h3 class="alert-title">Low Stock Items</h3>
                        </div>
                        <div class="alert-content">
                            <?php if (empty($lowStockItems)): ?>
                                <p style="color:#666;padding:0.5rem 0;">All stock levels are adequate.</p>
                            <?php else: ?>
                                <?php foreach (array_slice($lowStockItems, 0, 7) as $item): ?>
                                    <div class="alert-item">
                                        <div class="alert-item-info">
                                            <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                            <span class="item-detail"><?= $item['qty'] ?> bottle<?= $item['qty'] != 1 ? 's' : '' ?> remaining</span>
                                        </div>
                                        <span class="item-status <?= $item['qty'] <= 5 ? 'critical' : 'warning' ?>"><?= $item['qty'] <= 5 ? 'Critical' : 'Low' ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="pharmacistinventory.php" class="alert-action-link">View Inventory →</a>
                    </div>

                    <!-- Expiring Items Alert -->
                    <div class="alert-card expiring-alert">
                        <div class="alert-header">
                            <div class="alert-icon-wrapper">
                                <span class="alert-icon">⏰</span>
                            </div>
                            <h3 class="alert-title">Expiring Soon</h3>
                        </div>
                        <div class="alert-content">
                            <?php if (empty($expiringSoonItems)): ?>
                                <p style="color:#666;padding:0.5rem 0;">No items expiring within 30 days.</p>
                            <?php else: ?>
                                <?php foreach (array_slice($expiringSoonItems, 0, 7) as $item): ?>
                                    <div class="alert-item">
                                        <div class="alert-item-info">
                                            <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                            <span class="item-detail">Expires in <?= $item['days'] ?> day<?= $item['days'] != 1 ? 's' : '' ?></span>
                                        </div>
                                        <span class="item-status <?= $item['days'] <= 7 ? 'urgent' : 'warning' ?>"><?= $item['days'] <= 7 ? 'Urgent' : 'Soon' ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="pharmacistinventory.php" class="alert-action-link">View Inventory →</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
