<?php
// Sample data - In a real application, this would come from a database
$totalPatients = 15;
$treatments = [
    'Udwarthana' => 3,
    'Nasya Karma' => 2,
    'Shirodhara' => 2,
    'Basti' => 1,
    'Panchakarma Detox' => 4,
    'Vashpa Sweda' => 1,
    'Abhyanga Massage' => 1,
    'Elakizhi' => 1,
];

$todaySchedule = [
    ['time' => '9:00 AM - 11:00 AM', 'type' => 'Morning Session'],
    ['time' => '2:00 PM - 5:00 PM', 'type' => 'Afternoon Session'],
    ['time' => '', 'type' => 'Staff on Duty:'],
    ['time' => '', 'type' => 'Therapist A - Udwarthana'],
    ['time' => '', 'type' => 'Therapist B - Nasya Karma'],
    ['time' => '', 'type' => 'Therapist C - Shirodhara'],
    ['time' => '', 'type' => 'Therapist D - Basti'],
    ['time' => '', 'type' => 'Therapist E - Panchakarma Detox'],
    ['time' => '', 'type' => 'Therapist F - Vashpa Sweda'],
    ['time' => '', 'type' => 'Therapist G - Abhyanga Massage'],
    ['time' => '', 'type' => 'Therapist H - Elakizhi'],
    ['time' => '', 'type' => 'Therapist I - Receptionist'],
    ['time' => '', 'type' => 'Total Staff: 9']
];

$requiredProducts = [
    'Herbal oil' => '2 bottles',
    'Steam Towels' => '6'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Ayurvedic System</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/staffhome.css">
</head>
<body>
    <!-- Header with ribbon-style design -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <button class="nav-btn active">Home</button>
                <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
                <a href="staffappointment.php" class="nav-btn">Appointment</a>
                <a href="staffhomeTreatmentSuggestion.php" class="nav-btn">Treatment Suggestion</a>
                <a href="staffhomeReports.php" class="nav-btn">Reports</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Staff</span>
            <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="staffprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
    </div>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-content">
            <!-- Left Panel -->
            <div class="left-panel">
                <div class="info-section">
                    <h3>Total Patients Scheduled for Treatments: <?php echo $totalPatients; ?></h3>
                </div>

                <div class="info-section">
                    <h3>Treatments to be performed:</h3>
                    <ul class="treatment-list">
                        <?php foreach ($treatments as $treatment => $count): ?>
                            <li><?php echo $treatment; ?> - <?php echo $count; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="right-panel">
                <div class="schedule-section">
                    <h3>Today's Schedule</h3>
                    <div class="schedule-content">
                        <?php foreach ($todaySchedule as $item): ?>
                            <div class="schedule-item">
                                <?php if (!empty($item['time'])): ?>
                                    <span class="time"><?php echo $item['time']; ?></span>
                                <?php endif; ?>
                                <span class="description"><?php echo $item['type']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
