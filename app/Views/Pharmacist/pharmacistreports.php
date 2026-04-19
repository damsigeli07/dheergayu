<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../includes/auth_pharmacist.php';
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\BatchModel;

$model = new BatchModel();

// Get inventory overview for all product types
$overview        = $model->getInventoryOverview();
$patientOverview = $model->getPatientProductsOverview();
$treatmentOverview = $model->getTreatmentProductsOverview();

$expiringItems = [];
$today = new DateTime();
$thirtyDaysFromNow = clone $today;
$thirtyDaysFromNow->modify('+30 days');

$allOverviews = [
    ['rows' => $overview,         'source' => 'admin'],
    ['rows' => $patientOverview,  'source' => 'patient'],
    ['rows' => $treatmentOverview,'source' => 'treatment'],
];

foreach ($allOverviews as $group) {
    foreach ($group['rows'] as $row) {
        $batches = $model->getBatchesByProductId($row['product_id'], $group['source']);
        foreach ($batches as $batch) {
            if ($batch['exp']) {
                $expDate = new DateTime($batch['exp']);
                if ($expDate <= $thirtyDaysFromNow && $expDate >= $today) {
                    $expMonth = $expDate->format('M Y');
                    $key = $row['product'] . '_' . $expMonth;
                    if (!isset($expiringItems[$key])) {
                        $expiringItems[$key] = ['product' => $row['product'], 'expiry' => $expMonth, 'count' => 0];
                    }
                    $expiringItems[$key]['count'] += (int)$batch['quantity'];
                }
            }
        }
    }
}

usort($expiringItems, function($a, $b) {
    return strtotime($a['expiry']) - strtotime($b['expiry']);
});

function buildChartData(array $rows): array {
    $labels = []; $data = [];
    foreach ($rows as $row) {
        $qty = (int)$row['total_quantity'];
        $labels[] = $row['product'];
        $data[]   = $qty;
    }
    return [$labels, $data];
}

[$chartLabels, $chartData] = buildChartData($overview);
[$treatmentChartLabels, $treatmentChartData] = buildChartData($treatmentOverview);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacistreports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="pharmacisthome.php" class="nav-btn">Home</a>
            <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
            <a href="pharmacistorders.php" class="nav-btn">Orders</a>
            <button class="nav-btn active">Reports</button>
            <a href="pharmacistshoporders.php" class="nav-btn">Shop Orders</a>
            <a href="pharmacistrequest.php" class="nav-btn">Request</a>
            <a href="pharmacisttreatmentprep.php" class="nav-btn">Treatment Prep</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Pharmacist</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="reports-container">
            <!-- Reports Header -->
            <div class="reports-header">
                <h2 class="reports-title">Analytics & Reports</h2>
                <p class="reports-subtitle">Monitor your inventory performance and stock levels</p>
            </div>


            <!-- Expiring Stock Alerts -->
            <div class="expiring-alerts">
                <h3>⚠️ Expiring Stock Alerts</h3>
                <?php if (!empty($expiringItems)): ?>
                    <?php foreach ($expiringItems as $item): ?>
                        <div class="alert-item">
                            <span class="alert-product"><?= htmlspecialchars($item['product']) ?></span>
                            <span class="alert-date">Expires: <?= htmlspecialchars($item['expiry']) ?> (<?= $item['count'] ?> item<?= $item['count'] > 1 ? 's' : '' ?>)</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert-item">
                        <span class="alert-product">No items expiring soon</span>
                        <span class="alert-date">All items are within safe expiry range</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Medicines Chart -->
            <div class="report-card full-width">
                <h3>Medicines - Stock Levels</h3>
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>

            <!-- Treatment Oils Chart -->
            <div class="report-card full-width" style="margin-top: 2rem;">
                <h3>Treatment Oils - Stock Levels</h3>
                <div class="chart-container">
                    <canvas id="treatmentStockChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        const unifiedBarColor = '#F5B24C';

        // Medicines Stock Levels Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        const chartLabels = <?= json_encode($chartLabels) ?>;
        const chartData = <?= json_encode($chartData) ?>;
        
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Current Stock',
                    data: chartData,
                    backgroundColor: unifiedBarColor,
                    borderColor: '#333',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Medicines'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    }
                }
            }
        });

        // Treatment Oils Stock Levels Chart
        const treatmentStockCtx = document.getElementById('treatmentStockChart').getContext('2d');
        const treatmentChartLabels = <?= json_encode($treatmentChartLabels) ?>;
        const treatmentChartData = <?= json_encode($treatmentChartData) ?>;

        new Chart(treatmentStockCtx, {
            type: 'bar',
            data: {
                labels: treatmentChartLabels,
                datasets: [{
                    label: 'Current Stock',
                    data: treatmentChartData,
                    backgroundColor: unifiedBarColor,
                    borderColor: '#333',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { title: { display: true, text: 'Treatment Oils' }, ticks: { maxRotation: 45, minRotation: 45 } },
                    y: { beginAtZero: true, title: { display: true, text: 'Quantity (Bottles)' } }
                }
            }
        });
    </script>
</body>
</html>
