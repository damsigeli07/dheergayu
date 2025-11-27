<?php
session_start();

// Check if user is logged in and is a supplier
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'supplier') {
    header("Location: ../patient/login.php");
    exit();
}

// Example: Fetch supplied products from database
// Replace with your real DB connection

$products = [
    [
        "name" => "Paspanguwa Pack",
        "mfd" => "2025-02-12",
        "exp" => "2025-02-11",
        "delivered_date" => "2025-04-20",
        "quantity" => "50",
        "amount" => "850 LKR",
        "status" => "Delivered"
    ],

    [
        "name" => "Asamodagam Spirit",
        "mfd" => "2025-06-15",
        "exp" => "2026-06-14",
        "delivered_date" => "2025-08-25",
        "quantity" => "30",
        "amount" => "650 LKR",
        "status" => "Delivered"
    ],

    [
        "name" => "Siddhalepa Balm",
        "mfd" => "2025-06-30",
        "exp" => "2026-06-30",
        "delivered_date" => "2025-09-10",
        "quantity" => "70",
        "amount" => "450 LKR",
        "status" => "Delivered"
    ],

    [
        "name" => "Dashamoolarishta",
        "mfd" => "2025-08-30",
        "exp" => "2026-08-29",
        "delivered_date" => "-",
        "quantity" => "55",
        "amount" => "750 LKR",
        "status" => "Pending"
    ]

];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Supplier/supplierdashboard.css">
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
          <a href="supplierrequest.php" class="nav-btn">Request</a>
      </nav>
      
      <div class="user-section">
          <div class="user-icon" id="user-icon">👤</div>
          <span class="user-role">Supplier</span>
          <!-- Dropdown -->
          <div class="user-dropdown" id="user-dropdown">
              <a href="supplierprofile.php" class="profile-btn">Profile</a>
              <a href="../patient/login.php" class="logout-btn">Logout</a>
          </div>
      </div>
  </header>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Supplied Product Summary</h1>
            <p class="page-subtitle">View all your supplied products and their status</p>
        </div>

        <div class="table-card">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>MFD</th>
                        <th>EXP</th>
                        <th>Delivered Date</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= $p['name']; ?></td>
                        <td><?= $p['mfd']; ?></td>
                        <td><?= $p['exp']; ?></td>
                        <td><?= $p['delivered_date']; ?></td>
                        <td><?= $p['quantity']; ?></td>
                        <td><?= $p['amount']; ?></td>
                        <td>
                            <span class="status <?= strtolower($p['status']); ?>">
                                <?= $p['status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</body>
</html>
