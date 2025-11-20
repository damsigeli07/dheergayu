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
    case 'delete':
        $controller->deleteSupplier();
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
    <style>
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #8B7355;
            margin-bottom: 1rem;
            text-align: center;
        }

        .add-supplier-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .btn-add-supplier {
            background-color: #8B7355;
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-add-supplier:hover {
            background-color: #6F5B42;
            transform: translateY(-2px);
        }

        .supplier-table-container {
            overflow-x: auto;
        }

        .supplier-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .supplier-table th, .supplier-table td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .supplier-table th {
            background-color: #f8f8f8;
            font-weight: 600;
            color: #555;
        }

        .supplier-table tr:hover {
            background-color: #f0f0f0;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit, .btn-delete {
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit {
            background-color: #7a9b57;
        }

        .btn-edit:hover {
            background-color: #6B8E23;
        }

        .btn-delete {
            background-color: #DC143C;
        }

        .btn-delete:hover {
            background-color: #B91C3C;
        }
    </style>
</head>
<body class="has-sidebar">
    <!-- Header -->
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
        <h2 class="section-title">Supplier Information</h2>

        <!-- Add Supplier Button -->
        <div class="add-supplier-container">
            <a href="addsupplier.php" class="btn-add-supplier">+ Add New Supplier</a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <script>
                console.log('Success message:', '<?php echo $_SESSION['success']; ?>');
                alert('<?php echo addslashes($_SESSION['success']); ?>');
            </script>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                console.log('Error message:', '<?php echo $_SESSION['error']; ?>');
                alert('<?php echo addslashes($_SESSION['error']); ?>');
            </script>
            <div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Debug: Show all session data -->
        <script>
            console.log('All session data:', <?php echo json_encode($_SESSION); ?>);
            alert('Page loaded - checking for session messages...');
        </script>

        <!-- Supplier Table -->
        <div class="supplier-table-container">
            <table class="supplier-table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($suppliers)): ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                <td class="action-buttons">
                                    <a href="editsupplier.php?id=<?php echo $supplier['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="?action=delete&id=<?php echo $supplier['id']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this supplier? This action cannot be undone.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #666;">
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
