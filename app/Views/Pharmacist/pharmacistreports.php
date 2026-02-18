<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\BatchModel;

$model = new BatchModel();

// Get inventory overview from database for admin products
$overview = $model->getInventoryOverview();

// Get inventory overview for patient products
$patientOverview = $model->getPatientProductsOverview();

// Get expiring items (within 30 days) for both admin and patient products
$expiringItems = [];
$today = new DateTime();
$thirtyDaysFromNow = clone $today;
$thirtyDaysFromNow->modify('+30 days');

// Check admin products
foreach ($overview as $row) {
    $productId = $row['product_id'];
    $productName = $row['product'];
    $batches = $model->getBatchesByProductId($productId, 'admin');
    
    foreach ($batches as $batch) {
        if ($batch['exp']) {
            $expDate = new DateTime($batch['exp']);
            if ($expDate <= $thirtyDaysFromNow && $expDate >= $today) {
                // Group by product and expiry month
                $expMonth = $expDate->format('M Y');
                $key = $productName . '_' . $expMonth;
                
                if (!isset($expiringItems[$key])) {
                    $expiringItems[$key] = [
                        'product' => $productName,
                        'expiry' => $expMonth,
                        'count' => 0
                    ];
                }
                $expiringItems[$key]['count'] += (int)$batch['quantity'];
            }
        }
    }
}

// Check patient products
foreach ($patientOverview as $row) {
    $productId = $row['product_id'];
    $productName = $row['product'];
    $batches = $model->getBatchesByProductId($productId, 'patient');
    
    foreach ($batches as $batch) {
        if ($batch['exp']) {
            $expDate = new DateTime($batch['exp']);
            if ($expDate <= $thirtyDaysFromNow && $expDate >= $today) {
                // Group by product and expiry month
                $expMonth = $expDate->format('M Y');
                $key = $productName . '_' . $expMonth;
                
                if (!isset($expiringItems[$key])) {
                    $expiringItems[$key] = [
                        'product' => $productName,
                        'expiry' => $expMonth,
                        'count' => 0
                    ];
                }
                $expiringItems[$key]['count'] += (int)$batch['quantity'];
            }
        }
    }
}

// Sort expiring items by expiry date
usort($expiringItems, function($a, $b) {
    return strtotime($a['expiry']) - strtotime($b['expiry']);
});

// Prepare chart data for admin products
$chartLabels = [];
$chartData = [];
$chartColors = [];

foreach ($overview as $row) {
    $qty = (int)$row['total_quantity'];
    $chartLabels[] = $row['product'];
    $chartData[] = $qty;
    
    // Color coding based on stock level
    if ($qty <= 5) {
        $chartColors[] = '#FF6B6B'; // Critical - Red
    } elseif ($qty <= 15) {
        $chartColors[] = '#FF8C42'; // Low - Orange
    } else {
        $chartColors[] = '#FFB84D'; // Normal - Yellow
    }
}

// Prepare chart data for patient products
$patientChartLabels = [];
$patientChartData = [];
$patientChartColors = [];

foreach ($patientOverview as $row) {
    $qty = (int)$row['total_quantity'];
    $patientChartLabels[] = $row['product'];
    $patientChartData[] = $qty;
    
    // Color coding based on stock level
    if ($qty <= 5) {
        $patientChartColors[] = '#FF6B6B'; // Critical - Red
    } elseif ($qty <= 15) {
        $patientChartColors[] = '#FF8C42'; // Low - Orange
    } else {
        $patientChartColors[] = '#FFB84D'; // Normal - Yellow
    }
}
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
            <a href="pharmacistrequest.php" class="nav-btn">Request</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Pharmacist</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
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

            <!-- Admin Products Chart -->
            <div class="report-card full-width">
                <h3>Admin Products - Stock Levels</h3>
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>

            <!-- Patient Products Chart -->
            <div class="report-card full-width" style="margin-top: 2rem;">
                <h3>Patient Products - Stock Levels</h3>
                <div class="chart-container">
                    <canvas id="patientStockChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Admin Products Stock Levels Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        const chartLabels = <?= json_encode($chartLabels) ?>;
        const chartData = <?= json_encode($chartData) ?>;
        const chartColors = <?= json_encode($chartColors) ?>;
        
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Current Stock',
                    data: chartData,
                    backgroundColor: chartColors,
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
                            text: 'Admin Products'
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

        // Patient Products Stock Levels Chart
        const patientStockCtx = document.getElementById('patientStockChart').getContext('2d');
        const patientChartLabels = <?= json_encode($patientChartLabels) ?>;
        const patientChartData = <?= json_encode($patientChartData) ?>;
        const patientChartColors = <?= json_encode($patientChartColors) ?>;
        
        new Chart(patientStockCtx, {
            type: 'bar',
            data: {
                labels: patientChartLabels,
                datasets: [{
                    label: 'Current Stock',
                    data: patientChartData,
                    backgroundColor: patientChartColors,
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
                            text: 'Patient Products'
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
    </script>
</body>
</html>
