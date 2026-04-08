<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SUPPLIER_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'supplier') {
    header("Location: ../patient/login.php");
    exit();
}

require_once(dirname(__DIR__, 2) . '/config/config.php');

$supplierId = $_SESSION['user_id'];
$successMsg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name=?, address=?, contact_person=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssssi", $_POST['name'], $_POST['address'], $_POST['contactperson'], $_POST['email'], $_POST['contact'], $supplierId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['user_name'] = $_POST['name'];
    $successMsg = 'Profile updated successfully!';
}

$stmt = $conn->prepare("SELECT supplier_name, contact_person, phone, email, address, created_at FROM suppliers WHERE id = ?");
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

$supplier = [
    'name' => $row['supplier_name'] ?? '',
    'address' => $row['address'] ?? '',
    'contactperson' => $row['contact_person'] ?? '',
    'email' => $row['email'] ?? '',
    'contact' => $row['phone'] ?? '',
    'regdate' => isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : 'N/A',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Supplier/suppliereditprofile.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="supplierrequest.php" class="nav-btn">Request</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Supplier</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="supplierprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    <main class="edit-profile-container">
        <!-- Close button -->
        <a href="supplierprofile.php" class="btn-back">&times;</a>

        <h1 class="edit-profile-title">Edit Profile</h1>
        
        <?php if (!empty($successMsg)): ?>
            <div style="background:#e6ffe6;color:green;text-align:center;padding:10px;border-radius:5px;margin-bottom:15px;">
                <?= htmlspecialchars($successMsg) ?>
            </div>
        <?php endif; ?>
        <form class="edit-profile-form" method="POST" action="suppliereditprofile.php">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $supplier['name']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" required><?php echo $supplier['address']; ?></textarea>
            </div>

            <div class="form-group">
                <label for="contactperson">Contact Person:</label>
                <input type="text" id="contactperson" name="contactperson" value="<?php echo $supplier['contactperson']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $supplier['email']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">Contact No:</label>
                <input type="text" id="contact" name="contact" value="<?php echo $supplier['contact']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="regdate">Reg Date:</label>
                <input type="date" id="regdate" name="regdate" value="<?php echo $supplier['regdate']; ?>" required>
            </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="supplierprofile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
