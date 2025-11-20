<?php
// Include database connection and controller
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Controllers/SupplierController.php';

// Debug: Check if $conn is available
if (!isset($conn)) {
    die("Database connection not available. Please check config.php");
}

// Initialize controller
$controller = new SupplierController($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->addSupplier();
    exit; // Controller will redirect, so we don't need to continue
}

// Show the form
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/addsupplier.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="admindashboard.php" class="nav-btn">Home</a>
            <a href="admininventory.php" class="nav-btn">Products</a>
            <a href="admininventoryview.php" class="nav-btn">Inventory</a>
            <a href="adminappointment.php" class="nav-btn">Appointments</a>
            <a href="adminusers.php" class="nav-btn">Users</a>
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminsuppliers.php" class="nav-btn active">Supplier-info</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="add-supplier-form">
            <h2 class="form-title">Add New Supplier</h2>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="addsupplier.php" method="POST" id="addSupplierForm" autocomplete="off">
                <div class="form-group">
                    <label for="supplier-name" class="form-label">Supplier Name</label>
                    <input type="text" id="supplier-name" name="supplier_name" class="form-input" required placeholder="Enter supplier name" autocomplete="off" value="">
                </div>

                <div class="form-group">
                    <label for="contact-person" class="form-label">Contact Person</label>
                    <input type="text" id="contact-person" name="contact_person" class="form-input" required placeholder="Enter contact person" autocomplete="off" value="">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-input" required placeholder="Enter 10-digit phone number" maxlength="10" autocomplete="off" value="">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required placeholder="Enter email address" autocomplete="off" value="">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required placeholder="Enter password for supplier login" autocomplete="new-password" value="">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add Supplier</button>
                    <a href="adminsuppliers.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Phone validation: must be exactly 10 digits
        document.getElementById('addSupplierForm').addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone').value.trim();
            const phonePattern = /^\d{10}$/;
            if (!phonePattern.test(phoneInput)) {
                alert("Phone number must be exactly 10 digits.");
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
