<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

$staff = ['name' => '', 'age' => '', 'email' => '', 'contact' => '', 'address' => '', 'gender' => ''];

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
    $staff['name'] = trim($row['first_name'] . ' ' . $row['last_name']);
    $staff['email'] = $row['email'] ?? '';
    $staff['contact'] = $row['phone'] ?? '';
    $staff['age'] = $row['age'] !== null && $row['age'] !== '' ? (int) $row['age'] : '';
    $staff['address'] = $row['address'] ?? '';
    $staff['gender'] = $row['gender'] ?? '';
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/staffprofile.css">
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
    <main class="profile-container">
        <!-- Close button -->
        <a href="stafftreatment.php" class="btn-back">&times;</a>

        <h1 class="profile-title">My Profile</h1>
        
        <!-- Profile Icon -->
        <div class="profile-icon-container">
            <img src="/dheergayu/public/assets/images/Staff/profileicon.jpg" alt="Profile Icon" class="profile-icon">
        </div>
        
        <div class="profile-card">
            <div class="profile-item">
                <span class="label">Name:</span>
                <span class="value"><?php echo htmlspecialchars($staff['name']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Age:</span>
                <span class="value"><?php echo $staff['age'] !== '' ? (int)$staff['age'] : '—'; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Email:</span>
                <span class="value"><?php echo htmlspecialchars($staff['email']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Contact No:</span>
                <span class="value"><?php echo htmlspecialchars($staff['contact']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Address:</span>
                <span class="value"><?php echo htmlspecialchars($staff['address']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Gender:</span>
                <span class="value"><?php echo htmlspecialchars($staff['gender']); ?></span>
            </div>
        </div>

        <div class="edit-btn-container">
            <a href="editstaffprofile.php" class="btn-edit-profile">Edit Profile</a>
        </div>
    </main>
</body>
</html>
