<?php
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\BatchModel;

$model = new BatchModel();

// Get inventory overview from database
$overview = $model->getInventoryOverview();

// Get expiring items (within 30 days)
$expiringItems = [];
$today = new DateTime();
$thirtyDaysFromNow = clone $today;
$thirtyDaysFromNow->modify('+30 days');

foreach ($overview as $row) {
    $productId = $row['product_id'];
    $productName = $row['product'];
    $batches = $model->getBatchesByProductId($productId);
    
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

// Prepare chart data
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

            <!-- Main Chart -->
            <div class="report-card full-width">
                <h3>Stock Levels by Product</h3>
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>

            <!-- Generate Report Button -->
            <div class="generate-report-section">
                <button class="btn-generate-report" onclick="generateReport()">📊 Generate Detailed Report</button>
            </div>
        </div>
    </main>

    <script>
        // Stock Levels Chart - Using actual data from database
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
                            text: 'Products'
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

        // Generate Report Function
        function generateReport() {
            // Show loading state
            const button = document.querySelector('.btn-generate-report');
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating Report...';
            button.disabled = true;

            // Simulate report generation (replace with actual backend call)
            setTimeout(() => {
                // Here you would typically make an AJAX call to your backend
                // For now, we'll just show a success message
                button.innerHTML = '✅ Report Generated!';
                button.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    button.style.background = 'linear-gradient(135deg, #7a9b57, #6B8E23)';
                }, 3000);
            }, 2000);
        }
    </script>
</body>
</html>
