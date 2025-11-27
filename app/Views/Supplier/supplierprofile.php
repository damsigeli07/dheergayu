<?php
session_start();

// Check if user is logged in and is a supplier
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'supplier') {
    header("Location: ../patient/login.php");
    exit();
}

// Example data – in real case, fetch from database
$supplier = [
    'name' => 'S. Fernando',
    'age' => 35,
    'email' => 'supplier1@gmail.com',
    'contact' => '+94 77 987 6543',
    'address' => '456 Supplier Street, Kandy, Sri Lanka',
    'gender' => 'Male'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Profile - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Supplier/supplierprofile.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="supplierdashboard.php" class="nav-btn">Home</a>
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
    <main class="profile-container">
        <!-- Close button -->
        <a href="supplierdashboard.php" class="btn-back">&times;</a>

        <h1 class="profile-title">My Profile</h1>
        
        <!-- Profile Icon -->
        <div class="profile-icon-container">
            <img src="/dheergayu/public/assets/images/Supplier/profileicon.jpg" alt="Profile Icon" class="profile-icon">
        </div>
        
        <div class="profile-card">
            <div class="profile-item">
                <span class="label">Name:</span>
                <span class="value"><?php echo $supplier['name']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Age:</span>
                <span class="value"><?php echo $supplier['age']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Email:</span>
                <span class="value"><?php echo $supplier['email']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Contact No:</span>
                <span class="value"><?php echo $supplier['contact']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Address:</span>
                <span class="value"><?php echo $supplier['address']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Gender:</span>
                <span class="value"><?php echo $supplier['gender']; ?></span>
            </div>
        </div>

        <div class="edit-btn-container">
            <a href="suppliereditprofile.php" class="btn-edit-profile">Edit Profile</a>
        </div>
    </main>
</body>
</html>

