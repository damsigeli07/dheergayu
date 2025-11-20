<?php
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
    <title>Supplier Request - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/DHEERGAYU/public/assets/css/Supplier/supplierrequest.css">
</head>
<body>

<!-- Header with ribbon-style design -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
            <a href="supplierdashboard.php" class="nav-btn">Home</a>    
            <button class="nav-btn active">Request</button>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Supplier</span>
            <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="supplierprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
    </div>
        </div>
    </header>

<div class="container">
    <h2>Requested Product Summary</h2>

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
