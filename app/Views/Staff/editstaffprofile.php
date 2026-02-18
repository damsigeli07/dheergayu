<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

$staff = ['name' => '', 'first_name' => '', 'last_name' => '', 'age' => '', 'email' => '', 'contact' => '', 'address' => '', 'gender' => ''];

if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) !== 'staff')) {
    header('Location: ../patient/login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.first_name, u.last_name, u.email, u.phone, s.age, s.address, s.gender FROM users u LEFT JOIN staff_info s ON s.user_id = u.id WHERE u.id = ? AND LOWER(u.role) = 'staff' LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    $staff['first_name'] = $row['first_name'] ?? '';
    $staff['last_name'] = $row['last_name'] ?? '';
    $staff['name'] = trim($staff['first_name'] . ' ' . $staff['last_name']);
    $staff['email'] = $row['email'] ?? '';
    $staff['contact'] = $row['phone'] ?? '';
    $staff['age'] = $row['age'] !== null && $row['age'] !== '' ? (int) $row['age'] : '';
    $staff['address'] = $row['address'] ?? '';
    $staff['gender'] = $row['gender'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/editstaffprofile.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
            <a href="staffappointment.php" class="nav-btn">Appointment</a>
            <a href="staffhomeReports.php" class="nav-btn">Reports</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Staff</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="staffprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    <main class="edit-profile-container">
        <!-- Close button -->
        <a href="staffprofile.php" class="btn-back">&times;</a>

        <h1 class="edit-profile-title">Edit Profile</h1>
        
        <form class="edit-profile-form" id="editStaffForm" method="post" action="/dheergayu/app/Controllers/update_staff_profile.php">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo $staff['age'] !== '' ? (int)$staff['age'] : ''; ?>" min="1" max="150" placeholder="Optional">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">Contact No:</label>
                <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($staff['contact']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($staff['address']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender">
                    <option value="">-- Select --</option>
                    <option value="Male" <?php echo ($staff['gender']==='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo ($staff['gender']==='Female')?'selected':''; ?>>Female</option>
                    <option value="Other" <?php echo ($staff['gender']==='Other')?'selected':''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="staffprofile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
