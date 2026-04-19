<?php
require_once __DIR__ . '/../../includes/auth_doctor.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../config/config.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header('Location: ../patient/login.php');
    exit;
}

// Get doctor info from users table
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone FROM users WHERE id = ? AND role = 'doctor'");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$stmt->close();

if (!$doctor) {
    header('Location: ../patient/login.php');
    exit;
}

// Prepare doctor data for display
$doctorData = [
    'name' => 'Dr. ' . $doctor['first_name'] . ' ' . $doctor['last_name'],
    'email' => $doctor['email'],
    'contact' => $doctor['phone'] ?? 'N/A',
    'gender' => 'N/A', // Can be added to users table if needed
    'specialization' => 'Ayurvedic Medicine', // Default or from additional table
    'license_number' => 'DOC' . str_pad($doctor['id'], 6, '0', STR_PAD_LEFT),
    'experience' => 'N/A', // Can be added to users table if needed
    'qualification' => 'MBBS, MD' // Can be added to users table if needed
];

// Try to load specialization from doctor_info only if the table exists.
$doctorInfoTableExists = false;
$tableCheck = $conn->query("SHOW TABLES LIKE 'doctor_info'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $doctorInfoTableExists = true;
}

if ($doctorInfoTableExists) {
    $stmt2 = $conn->prepare("SELECT specialization FROM doctor_info WHERE user_id = ? LIMIT 1");
    if ($stmt2) {
        $stmt2->bind_param('i', $doctor['id']);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $dinfo = $res2->fetch_assoc();
        if ($dinfo && !empty($dinfo['specialization'])) {
            $doctorData['specialization'] = $dinfo['specialization'];
        }
        $stmt2->close();
    }
}

// Show success message if redirected after save
$successMsg = '';
if (!empty($_SESSION['doctor_profile_success'])) {
    $successMsg = $_SESSION['doctor_profile_success'];
    unset($_SESSION['doctor_profile_success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile - Dr. <?php echo htmlspecialchars($doctor['last_name']); ?></title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctorprofile.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="doctordashboard.php" class="nav-btn">Appointments</a>
            <a href="doctordashboard.php?view=treatment-plans" class="nav-btn">Treatment Plans</a>
            <a href="patienthistory.php" class="nav-btn">Patient History</a>
            <a href="doctorreport.php" class="nav-btn">Reports</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Dr. <?php echo htmlspecialchars($doctor['last_name']); ?></span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="doctorprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </header>
    
    <main class="profile-container">
        <!-- Close button -->
        <a href="doctordashboard.php" class="btn-back">&times;</a>

        <h1 class="profile-title">My Profile</h1>

        <?php if (!empty($successMsg)): ?>
            <div class="profile-success" style="background:#e6ffed;border:1px solid #b7f0c8;color:#0a6d2a;padding:10px;border-radius:6px;margin-bottom:12px;"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>

        <div class="profile-picture">
            <img src="/dheergayu/public/assets/images/Doctor/doctor-profile.jpg" alt="Doctor Profile">
        </div>
        
        <div class="profile-card">
            <div class="profile-item">
                <span class="label">Name:</span>
                <span class="value"><?php echo htmlspecialchars($doctorData['name']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Email:</span>
                <span class="value"><?php echo htmlspecialchars($doctorData['email']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Contact No:</span>
                <span class="value"><?php echo htmlspecialchars($doctorData['contact']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">License Number:</span>
                <span class="value"><?php echo htmlspecialchars($doctorData['license_number']); ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Specialization:</span>
                <span class="value"><?php echo htmlspecialchars($doctorData['specialization']); ?></span>
            </div>
        </div>

        <div class="edit-btn-container">
            <a href="editdoctorprofile.php" class="btn-edit-profile">Edit Profile</a>
        </div>
    </main>
</body>
</html>