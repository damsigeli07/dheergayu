<?php
require_once __DIR__ . '/../../includes/auth_admin.php';
require_once __DIR__ . '/../../../config/config.php';

$today = date('Y-m-d');

// ── Revenue today from paid orders ────────────────────────────────────────────
$r = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM   orders
    WHERE  DATE(created_at) = '$today' AND status = 'paid'
");
$totalRevenueToday = $r ? (float)$r->fetch_assoc()['total'] : 0;
$revenueTarget     = 50000; // configurable daily target

// ── Ongoing appointments (Pending/Confirmed) for today ───────────────────────
$r = $conn->query("
    SELECT COUNT(*) AS cnt FROM consultations
    WHERE  appointment_date = '$today'
    AND    status IN ('Pending','Confirmed')
");
$ongoingAppointments = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

// ── Active treatment plans ────────────────────────────────────────────────────
$r = $conn->query("
    SELECT COUNT(*) AS cnt FROM treatment_plans
    WHERE  status IN ('Pending','Confirmed','InProgress')
");
$ongoingTreatments = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

// ── Completed appointments today ──────────────────────────────────────────────
$r = $conn->query("
    SELECT COUNT(*) AS cnt FROM consultations
    WHERE  appointment_date = '$today' AND status = 'Completed'
");
$completedAppointments = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

// ── Inventory alerts: batches with quantity < 5 ───────────────────────────────
$r = $conn->query("
    SELECT COUNT(DISTINCT product_id) AS cnt
    FROM   batches
    WHERE  quantity < 5 AND quantity >= 0
");
$inventoryAlerts = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

$lowStockItems = [];
$r = $conn->query("
    SELECT p.name
    FROM   products p
    JOIN   batches  b ON p.product_id = b.product_id
    WHERE  b.quantity < 5 AND b.quantity >= 0
    GROUP  BY p.product_id
    ORDER  BY SUM(b.quantity) ASC
    LIMIT  3
");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $lowStockItems[] = $row['name'];
    }
}
if (empty($lowStockItems)) $lowStockItems = ['No low-stock items'];

// ── Active staff (non-admin users) ────────────────────────────────────────────
$r = $conn->query("
    SELECT COUNT(*) AS cnt FROM users
    WHERE  status = 'Active' AND role != 'admin'
");
$activeStaff = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

// ── New patients registered this week ────────────────────────────────────────
$r = $conn->query("
    SELECT COUNT(*) AS cnt FROM patients
    WHERE  created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$newUsersRegistered = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

// ── Orders received today ─────────────────────────────────────────────────────
$r = $conn->query("
    SELECT COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS total
    FROM   orders
    WHERE  DATE(created_at) = '$today'
");
$ordersRow          = $r ? $r->fetch_assoc() : null;
$ordersReceivedToday = (int)($ordersRow['cnt']   ?? 0);
$ordersValue         = (float)($ordersRow['total'] ?? 0);

$revenueProgress = $revenueTarget > 0
    ? min(100, ($totalRevenueToday / $revenueTarget) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
  <script src="/dheergayu/public/assets/js/header.js"></script>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/admindashboard.css?v=3.0" />
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
          <a href="admininventory.php"     class="nav-btn">Products</a>
          <a href="admininventoryview.php" class="nav-btn">Inventory</a>
          <a href="adminappointment.php"   class="nav-btn">Appointments</a>
          <a href="adminusers.php"         class="nav-btn">Users</a>
          <a href="adminpatients.php"      class="nav-btn">Patients</a>
          <a href="admintreatment.php"     class="nav-btn">Treatments</a>
          <a href="adminpayments.php"        class="nav-btn">Payments</a>
          <a href="adminsuppliers.php"     class="nav-btn">Supplier-info</a>
          <a href="admincontact.php"       class="nav-btn">Contact Submissions</a>
              <a href="adminreports.php" class="nav-btn">Reports</a>
        </nav>
      <div class="user-section">
          <div class="user-icon" id="user-icon">👤</div>
          <span class="user-role">Admin</span>
          <div class="user-dropdown" id="user-dropdown">
              <a href="adminprofile.php"        class="profile-btn">Profile</a>
              <a href="/dheergayu/app/Views/logout.php"    class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
          </div>
      </div>
  </header>

  <!-- Main Content -->
  <div class="main-content">
      <div class="page-header">
          <h1 class="page-title">Dashboard Overview</h1>
          <p class="page-subtitle">
              Live data for <?= date('l, d F Y') ?> &mdash; refreshes on page reload.
          </p>
      </div>

      <!-- Overview Cards -->
      <div class="overview-cards">
          <div class="overview-card card-primary">
              <div class="card-icon">📅</div>
              <div class="card-content">
                  <div class="card-label">Ongoing Appointments (Today)</div>
                  <div class="card-value"><?= $ongoingAppointments ?></div>
                  <div class="card-desc">Pending &amp; Confirmed</div>
              </div>
          </div>
          <div class="overview-card card-secondary">
              <div class="card-icon">💆</div>
              <div class="card-content">
                  <div class="card-label">Active Treatment Plans</div>
                  <div class="card-value"><?= $ongoingTreatments ?></div>
                  <div class="card-desc">Pending / Confirmed / In-progress</div>
              </div>
          </div>
          <div class="overview-card card-accent">
              <div class="card-icon">💰</div>
              <div class="card-content">
                  <div class="card-label">Today's Revenue (Paid Orders)</div>
                  <div class="card-value">Rs.&nbsp;<?= number_format($totalRevenueToday) ?></div>
                  <div class="card-desc">Target: Rs.&nbsp;<?= number_format($revenueTarget) ?></div>
              </div>
          </div>
          <div class="overview-card card-success">
              <div class="card-icon">✅</div>
              <div class="card-content">
                  <div class="card-label">Completed Today</div>
                  <div class="card-value"><?= $completedAppointments ?></div>
                  <div class="card-desc">Consultations completed</div>
              </div>
          </div>
      </div>

      <!-- Charts -->
      <div class="charts-section">
          <div class="chart-card">
              <div class="chart-header">
                  <h3>Revenue Progress</h3>
                  <span class="chart-badge">Today</span>
              </div>
              <div class="progress-container">
                  <div class="progress-bar">
                      <div class="progress-fill"
                           style="width:<?= number_format($revenueProgress, 1) ?>%"></div>
                  </div>
                  <div class="progress-info">
                      <span>Rs.&nbsp;<?= number_format($totalRevenueToday) ?></span>
                      <span><?= number_format($revenueProgress, 1) ?>% of target</span>
                  </div>
              </div>
              <?php if ($ordersReceivedToday > 0): ?>
              <p style="margin-top:12px;font-size:.85rem;color:#888;">
                  <?= $ordersReceivedToday ?> order<?= $ordersReceivedToday > 1 ? 's' : '' ?>
                  totalling Rs.&nbsp;<?= number_format($ordersValue) ?> received today.
              </p>
              <?php endif; ?>
          </div>

          <div class="chart-card">
              <div class="chart-header">
                  <h3>Appointments Status (Today)</h3>
                  <span class="chart-badge">Today</span>
              </div>
              <canvas id="appointmentsChart"></canvas>
          </div>
      </div>

      <!-- Stats & Activity -->
      <div class="stats-section">
          <div class="stats-card">
              <div class="stats-header"><h3>Quick Stats</h3></div>
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
                          <div class="stat-label">Low-Stock Products</div>
                      </div>
                  </div>
                  <div class="stat-item">
                      <div class="stat-icon">🆕</div>
                      <div class="stat-info">
                          <div class="stat-value"><?= $newUsersRegistered ?></div>
                          <div class="stat-label">New Patients (7d)</div>
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
              </div>
              <div class="activity-list">
                  <div class="activity-item">
                      <div class="activity-icon">📋</div>
                      <div class="activity-content">
                          <div class="activity-title">
                              <?= $completedAppointments ?> appointment<?= $completedAppointments !== 1 ? 's' : '' ?> completed today
                          </div>
                          <div class="activity-time">As of <?= date('g:i A') ?></div>
                      </div>
                  </div>
                  <div class="activity-item">
                      <div class="activity-icon">⚠️</div>
                      <div class="activity-content">
                          <div class="activity-title">
                              <?php if ($inventoryAlerts > 0): ?>
                                  Low stock: <?= implode(', ', $lowStockItems) ?>
                              <?php else: ?>
                                  All product stock levels are healthy
                              <?php endif; ?>
                          </div>
                          <div class="activity-time">Inventory check</div>
                      </div>
                  </div>
                  <div class="activity-item">
                      <div class="activity-icon">👤</div>
                      <div class="activity-content">
                          <div class="activity-title">
                              <?= $newUsersRegistered ?> new patient<?= $newUsersRegistered !== 1 ? 's' : '' ?> registered this week
                          </div>
                          <div class="activity-time">Last 7 days</div>
                      </div>
                  </div>
                  <div class="activity-item">
                      <div class="activity-icon">💰</div>
                      <div class="activity-content">
                          <div class="activity-title">
                              Rs.&nbsp;<?= number_format($ordersValue) ?> in orders today
                          </div>
                          <div class="activity-time"><?= $ordersReceivedToday ?> paid order<?= $ordersReceivedToday !== 1 ? 's' : '' ?></div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>

<script>
const ctx = document.getElementById('appointmentsChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Ongoing', 'Cancelled'],
        datasets: [{
            data: [
                <?= $completedAppointments ?>,
                <?= $ongoingAppointments ?>,
                <?php
                    $can = $conn->query("SELECT COUNT(*) AS c FROM consultations WHERE appointment_date='$today' AND status='Cancelled'");
                    echo $can ? (int)$can->fetch_assoc()['c'] : 0;
                ?>
            ],
            backgroundColor: ['#FFB84D','#FF8C42','#FFEED6'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding:15, font:{ family:'Roboto', size:12 }, color:'#2d2d2d' }
            }
        }
    }
});
</script>
</body>
</html>