<?php
require_once __DIR__ . '/../../includes/auth_doctor.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../config/config.php';

// Fetch logged-in doctor's basic info from users table
$doctor = [
    'name' => '',
    'age' => '',
    'email' => '',
    'contact' => '',
    'address' => '',
    'gender' => '',
    'specialization' => '',
    'license_number' => '',
    'experience' => '',
    'qualification' => ''
];

if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone FROM users WHERE id = ? AND role = 'doctor'");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        $doctor['name'] = trim($row['first_name'] . ' ' . $row['last_name']);
        $doctor['email'] = $row['email'] ?? '';
        $doctor['contact'] = $row['phone'] ?? '';
        $doctor['license_number'] = 'DOC' . str_pad($row['id'], 6, '0', STR_PAD_LEFT);
    }
}
// Load specialization from doctor_info if exists
$infoStmt = $conn->prepare("SELECT specialization FROM doctor_info WHERE user_id = ? LIMIT 1");
if ($infoStmt) {
    $infoStmt->bind_param('i', $uid);
    $infoStmt->execute();
    $infoRes = $infoStmt->get_result();
    $infoRow = $infoRes->fetch_assoc();
    $infoStmt->close();
    if ($infoRow && !empty($infoRow['specialization'])) {
        $doctor['specialization'] = $infoRow['specialization'];
    }
}
// ensure specialization has a sensible default
if (empty($doctor['specialization'])) {
    $doctor['specialization'] = 'Ayurvedic Medicine';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/editdoctorprofile.css">
    
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
            <span class="user-role">Doctor</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="doctorprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    <main class="edit-profile-container">
        <!-- Close button -->
        <a href="doctorprofile.php" class="btn-back">&times;</a>

        <h1 class="edit-profile-title">Edit Profile</h1>
        
        <form class="edit-profile-form" method="post" action="/dheergayu/app/Controllers/update_doctor_profile.php">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $doctor['name']; ?>" readonly>
            </div>
            
            
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $doctor['email']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">Contact No:</label>
                <input type="text" id="contact" name="contact" value="<?php echo $doctor['contact']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" disabled>
                    <option value="Male" <?php echo ($doctor['gender']=='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo ($doctor['gender']=='Female')?'selected':''; ?>>Female</option>
                    <option value="Other" <?php echo ($doctor['gender']=='Other')?'selected':''; ?>>Other</option>
                </select>
                <input type="hidden" name="gender" value="<?php echo htmlspecialchars($doctor['gender']); ?>">
            </div>
            <div class="form-group">
                <label for="specialization">Specialization:</label>
                <input type="text" id="specialization" name="specialization" value="<?php echo $doctor['specialization']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="license_number">License Number:</label>
                <input type="text" id="license_number" name="license_number" value="<?php echo $doctor['license_number']; ?>" readonly>
            </div>

            <input type="hidden" name="user_id" value="<?php echo isset($_SESSION['user_id'])? (int)$_SESSION['user_id'] : ''; ?>">
            
            
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="doctorprofile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
