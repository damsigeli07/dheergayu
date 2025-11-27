<?php
session_start();

// Check if user is logged in and is a supplier
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'supplier') {
    header("Location: ../patient/login.php");
    exit();
}

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
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody id="supplierRequestsBody">
    <tr>
        <td colspan="4" style="text-align:center; padding:20px;">Loading...</td>
    </tr>
</tbody>


            </table>
        </div>
    </div>

<script>
const supplierId = <?= json_encode($_SESSION['user_id']) ?>;

function loadSupplierRequests() {
    fetch('/dheergayu/public/api/get-supplier-requests.php?supplier_id=' + supplierId)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('supplierRequestsBody');
            if (data.success && data.requests.length > 0) {
                tbody.innerHTML = data.requests.map(req => `
                    <tr>
                        <td>${req.product_name}</td>
                        <td>${req.quantity}</td>
                        <td>${req.request_date}</td>
                        <td><span class="status-badge status-${req.status}">${req.status}</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr><td colspan="4" style="text-align:center; padding:15px;">No requests found.</td></tr>
                `;
            }
        })
        .catch(err => console.error(err));
}

loadSupplierRequests();
</script>

</body>
</html>
