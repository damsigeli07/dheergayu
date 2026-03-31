<?php
require_once __DIR__ . '/../../../config/config.php';

// Fetch real statistics from database
// Orders statistics
$ordersQuery = "SELECT COUNT(*) as total_orders, SUM(amount) as total_revenue FROM orders WHERE DATE(created_at) = CURDATE()";
$ordersResult = $conn->query($ordersQuery);
$ordersData = $ordersResult->fetch_assoc();
$ordersReceivedToday = $ordersData['total_orders'] ?? 0;
$ordersValue = $ordersData['total_revenue'] ?? 0;

// Overall revenue (for progress calculation)
$totalRevenueQuery = "SELECT SUM(amount) as total_revenue FROM orders WHERE status = 'paid'";
$totalRevenueResult = $conn->query($totalRevenueQuery);
$totalRevenueData = $totalRevenueResult->fetch_assoc();
$totalRevenueToday = $totalRevenueData['total_revenue'] ?? 0;

// Other hardcoded values (you can make these dynamic too)
$ongoingAppointments   = 15;
$ongoingTreatments     = 20;
$revenueTarget         = 50000; // Monthly target
$completedAppointments = 35;
$inventoryAlerts       = 2;
$lowStockItems         = ['Herbal Oil', 'Steam Herbs'];
$activeStaff           = 13;
$newUsersRegistered    = 6;

$revenueProgress       = ($totalRevenueToday / $revenueTarget) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
  <script src="/dheergayu/public/assets/js/header.js"></script>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/admindashboard.css?v=2.0" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="has-sidebar">

  <!-- Sidebar -->
  <header class="header">
      <div class="header-top">
          <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo" />
          <h1 class="header-title">Dheergayu</h1>
      </div>
      
      <nav class="navigation">
          <button class="nav-btn active">Home</button>
          <a href="admininventory.php" class="nav-btn">Products</a>
          <a href="admininventoryview.php" class="nav-btn">Inventory</a>
          <a href="adminappointment.php" class="nav-btn">Appointments</a>
          <a href="adminusers.php" class="nav-btn">Users</a>
          <a href="admintreatment.php" class="nav-btn">Treatments</a>
          <a href="adminorders.php" class="nav-btn">Orders</a>
          <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
          <a href="admincontact.php" class="nav-btn">Contact Submissions</a>
      </nav>
      
      <div class="user-section">
          <div class="user-icon" id="user-icon">👤</div>
          <span class="user-role">Admin</span>
          <!-- Dropdown -->
          <div class="user-dropdown" id="user-dropdown">
              <a href="adminprofile.php" class="profile-btn">Profile</a>
              <a href="../patient/login.php" class="logout-btn">Logout</a>
          </div>
      </div>
  </header>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Dashboard Overview</h1>
            <p class="page-subtitle">Welcome back! Here's what's happening today.</p>
        </div>

        <!-- Overview Cards -->
        <div class="overview-cards">
            <div class="overview-card card-primary">
                <div class="card-icon">📅</div>
                <div class="card-content">
                    <div class="card-label">Ongoing Appointments</div>
                    <div class="card-value"><?= $ongoingAppointments ?></div>
                    <div class="card-desc">Active sessions</div>
                </div>
            </div>

            <div class="overview-card card-secondary">
                <div class="card-icon">💆</div>
                <div class="card-content">
                    <div class="card-label">Active Treatments</div>
                    <div class="card-value"><?= $ongoingTreatments ?></div>
                    <div class="card-desc">In progress</div>
                </div>
            </div>

            <div class="overview-card card-accent">
                <div class="card-icon">💰</div>
                <div class="card-content">
                    <div class="card-label">Today's Revenue</div>
                    <div class="card-value">Rs. <?= number_format($totalRevenueToday) ?></div>
                    <div class="card-desc">Target: Rs. <?= number_format($revenueTarget) ?></div>
                </div>
            </div>

            <div class="overview-card card-success">
                <div class="card-icon">✅</div>
                <div class="card-content">
                    <div class="card-label">Completed</div>
                    <div class="card-value"><?= $completedAppointments ?></div>
                    <div class="card-desc">Appointments today</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Revenue Progress</h3>
                    <span class="chart-badge">Today</span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $revenueProgress ?>%"></div>
                    </div>
                    <div class="progress-info">
                        <span>Rs. <?= number_format($totalRevenueToday) ?></span>
                        <span><?= number_format($revenueProgress, 1) ?>% of target</span>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3>Appointments Status</h3>
                    <span class="chart-badge">Today</span>
                </div>
                <canvas id="appointmentsChart"></canvas>
            </div>
        </div>

        <!-- Stats and Activity Section -->
        <div class="stats-section">
            <div class="stats-card">
                <div class="stats-header">
                    <h3>Quick Stats</h3>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $activeStaff ?></div>
                            <div class="stat-label">Active Staff</div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $inventoryAlerts ?></div>
                            <div class="stat-label">Inventory Alerts</div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">🆕</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $newUsersRegistered ?></div>
                            <div class="stat-label">New Users</div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">🛒</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $ordersReceivedToday ?></div>
                            <div class="stat-label">Orders Today</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="activity-card">
                <div class="activity-header">
                    <h3>Recent Activity</h3>
                    <a href="#" class="view-all">View All</a>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">📋</div>
                        <div class="activity-content">
                            <div class="activity-title"><?= $completedAppointments ?> appointments completed</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">⚠️</div>
                        <div class="activity-content">
                            <div class="activity-title">Low stock alert: <?= implode(', ', $lowStockItems) ?></div>
                            <div class="activity-time">5 hours ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">👤</div>
                        <div class="activity-content">
                            <div class="activity-title"><?= $newUsersRegistered ?> new users registered this week</div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">💰</div>
                        <div class="activity-content">
                            <div class="activity-title">Rs. <?= number_format($ordersValue) ?> in orders received</div>
                            <div class="activity-time">2 days ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Appointments Chart
        const ctx = document.getElementById('appointmentsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Ongoing', 'Pending'],
                datasets: [{
                    data: [<?= $completedAppointments ?>, <?= $ongoingAppointments ?>, 5],
                    backgroundColor: [
                        '#FFB84D',
                        '#FF8C42',
                        '#FFEED6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                family: 'Roboto',
                                size: 12
                            },
                            color: '#2d2d2d'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
