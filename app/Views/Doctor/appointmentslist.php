<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointments List</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctordashboard.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="doctordashboard.php" class="nav-btn active">Appointments</a>
            <a href="patienthistory.php" class="nav-btn">Patient History</a>
            <a href="doctorreport.php" class="nav-btn">Reports</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Doctor</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="doctorprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    <main class="main-content">
        <div class="table-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient No.</th>
                        <th>Patient Name</th>
                        <th>Appointment No.</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)) : ?>
                        <?php foreach ($appointments as $apt) : ?>
                            <tr>
                                <td><?= htmlspecialchars($apt['appointment_id']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_no']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                                <td><?= htmlspecialchars($apt['appointment_no']) ?></td>
                                <td><?= htmlspecialchars($apt['appointment_datetime']) ?></td>
                                <td><?= htmlspecialchars($apt['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6">No appointments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
