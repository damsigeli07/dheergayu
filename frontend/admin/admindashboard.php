<?php
$ongoingAppointments   = 15;
$ongoingTreatments     = 20;
$totalRevenueToday     = 15750;
$revenueTarget         = 18000;
$completedAppointments = 35;
$inventoryAlerts       = 2;
$lowStockItems         = ['Herbal Oil', 'Steam Herbs'];
$activeStaff           = 13;
$newUsersRegistered    = 6;
$ordersReceivedToday   = 10;
$ordersValue           = 8450;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../css_common/header.css">
  <script src="../js_common/header.js"></script>
  <link rel="stylesheet" href="css/admindashboard.css?v=1.2" />
</head>
<body>

  <header class="header">
      <nav class="navigation">
          <button class="nav-btn active">Home</button>
          <a href="admininventory.php" class="nav-btn">Inventory</a>
          <a href="adminappointment.php" class="nav-btn">Appointments</a>
          <a href="adminusers.php" class="nav-btn">Users</a>
          <a href="admintreatment.php" class="nav-btn">Treatment Schedule</a>
          <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
      </nav>
      <div class="header-right">
         <img src="../staff/images/dheergayu.png" class="logo" alt="Logo" />
         <h1 class="header-title">Dheergayu</h1>
         <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="adminprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
            </div> 
      </div>
  </header>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-grid">
            <div class="card green">
                <div class="title">Ongoing Appointments: <?= $ongoingAppointments ?></div>
                <div class="value"><?= $ongoingAppointments ?></div>
                <div>Active Sessions<br><span style="font-size:0.95em;">Last updated: 2 mins ago</span></div>
            </div>
            <div class="card green">
                <div class="title">Ongoing: <?= $ongoingTreatments ?> Treatments</div>
                <div class="value"><?= $ongoingTreatments ?></div>
                <div>In Progress<br><span style="font-size:0.95em;">Oil Massage, Steam Therapy</span></div>
            </div>
            <div class="card green">
                <div class="title">Total Revenue Today</div>
                <div class="value">Rs. <?= number_format($totalRevenueToday) ?></div>
                <div>From <?= $completedAppointments ?> appointments<br>Target: Rs. <?= number_format($revenueTarget) ?></div>
            </div>

            <div class="card yellow">
                <div class="title">Today Completed Appointments: <?= $completedAppointments ?></div>
                <div class="value"><?= $completedAppointments ?></div>
                <div>Successfully finished</div>
            </div>
            <div class="card yellow">
                <div class="title">Inventory: <?= $inventoryAlerts ?> Alerts</div>
                <div class="value"><span style="font-size:1.5em;">&#x26A0;</span> <?= $inventoryAlerts ?></div>
                <div>Low Stock Items<br><?= implode(', ', $lowStockItems) ?></div>
            </div>
            <div class="card yellow">
                <div class="title">Active Staff: <?= $activeStaff ?></div>
                <div class="value"><?= $activeStaff ?></div>
                <div>8 Therapists, 3 Admin, 2 Pharmacy<br><span style="font-size:0.95em;">All systems operational</span></div>
            </div>

            <div class="card blue">
                <div class="title">New Users Registered: <?= $newUsersRegistered ?></div>
                <div class="value"><?= $newUsersRegistered ?></div>
                <div>This week<br>3 patients, 2 staff, 1 admin</div>
            </div>
            <div class="card purple">
                <div class="title">Orders Received Today: <?= $ordersReceivedToday ?></div>
                <div class="value"><?= $ordersReceivedToday ?></div>
                <div>Total value: Rs. <?= number_format($ordersValue) ?></div>
            </div>
            <div class="card purple">
                <div class="title">System Status</div>
                <div class="system-status">
                    <span class="ok">&#9679; All systems operational</span>
                    <span class="warn">&#9679; <?= $inventoryAlerts ?> inventory alerts</span>
                </div>
                <div style="font-size: 0.97em; color: #666;">Last check: 5 mins ago</div>
            </div>
        </div>
    </div>
</body>
</html>
