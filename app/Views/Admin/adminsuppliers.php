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

// Handle different actions
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'deactivate':
        $controller->deactivateSupplier();
        break;
    case 'activate':
        $controller->activateSupplier();
        break;
    default:
        $suppliers = $controller->getModel()->getAllSuppliers();
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Info - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/adminsuppliers.css">
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
            <button class="nav-btn active">Supplier Info</button>
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
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Supplier Information</h1>
            <p class="page-subtitle">Manage supplier accounts and information</p>
        </div>

        <!-- Add Supplier Button -->
        <div class="add-supplier-container">
            <a href="addsupplier.php" class="btn-add-supplier">+ Add New Supplier</a>
        </div>

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

        <!-- Supplier Table -->
        <div class="supplier-table-container">
            <table class="supplier-table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Reg Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($suppliers)): ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <?php 
                                $status = isset($supplier['status']) ? strtolower($supplier['status']) : 'active';
                                $isActive = ($status === 'active');
                                $statusText = $isActive ? 'Active' : 'Inactive';
                                $actionText = $isActive ? 'Deactivate' : 'Activate';
                                $actionClass = $isActive ? 'btn-deactivate' : 'btn-activate';
                                $actionUrl = $isActive ? 'deactivate' : 'activate';
                                $regDate = isset($supplier['created_at']) ? $supplier['created_at'] : '-';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                <td>
                                    <span class="status <?php echo $isActive ? 'active' : 'inactive'; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($regDate); ?></td>
                                <td>
                                    <a href="editsupplier.php?id=<?php echo $supplier['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="?action=<?php echo $actionUrl; ?>&id=<?php echo $supplier['id']; ?>" 
                                       class="<?php echo $actionClass; ?>" 
                                       onclick="return confirm('Are you sure you want to <?php echo strtolower($actionText); ?> this supplier?')">
                                        <?php echo $actionText; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                No suppliers found. <a href="addsupplier.php">Add your first supplier</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
