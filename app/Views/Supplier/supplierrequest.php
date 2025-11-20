<?php
session_start();

// Check if user is logged in and is a supplier
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'supplier') {
    header("Location: ../patient/login.php");
    exit();
}

// request.php
// Sample data (replace with your database results)
$requests = [
    ["product" => "Paspanguwa Pack", "quantity" => 40, "date" => "2025-08-30"],
    ["product" => "Asamodagam Spirit", "quantity" => 20, "date" => "2025-08-31"],
    ["product" => "Siddhalepa Balm", "quantity" => 50, "date" => "2025-09-20"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Request - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Supplier/supplierrequest.css">
</head>
<body class="has-sidebar">

  <!-- Sidebar -->
  <header class="header">
      <div class="header-top">
          <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo" />
          <h1 class="header-title">Dheergayu</h1>
      </div>
      
      <nav class="navigation">
          <a href="supplierdashboard.php" class="nav-btn">Home</a>
          <button class="nav-btn active">Request</button>
          <a href="supplierprofile.php" class="nav-btn">Profile</a>
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
            <h1 class="page-title">Requested Product Summary</h1>
            <p class="page-subtitle">Review and manage product requests</p>
        </div>

        <div class="table-box">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Request Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($requests as $row): ?>
                        <tr>
                            <td><?= $row["product"] ?></td>
                            <td><?= $row["quantity"] ?></td>
                            <td><?= $row["date"] ?></td>
                            <td>
                                <button class="btn accept">Accept</button>
                                <button class="btn reject">Reject</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
