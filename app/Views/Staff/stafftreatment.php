<?php
// Sample data - In a real application, this would come from a database
$treatments = array(
    array(
        'patient_ID' => 'P12349',
        'patient_name' => 'Melissa Fernando',
        'treatment_type' => 'Vashpa Sweda',
        'date_time' => 'March 20, 2026 - 10:00 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12352',
        'patient_name' => 'Arjun Patel',
        'treatment_type' => 'Udwarthana',
        'date_time' => 'March 20, 2026 - 10:15 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12345',
        'patient_name' => 'Amashi Vithanage',
        'treatment_type' => 'Panchakarma Detox',
        'date_time' => 'March 20, 2026 - 10:30 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12358',
        'patient_name' => 'Noora Fathi',
        'treatment_type' => 'Elakizhi',
        'date_time' => 'March 20, 2026 - 10:30 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12375',
        'patient_name' => 'Kingsley Tissera',
        'treatment_type' => 'Shirodhara',
        'date_time' => 'March 20, 2026 - 11:00 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12341',
        'patient_name' => 'Priya Sharma',
        'treatment_type' => 'Nasya Karma',
        'date_time' => 'March 20, 2026 - 11:30 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12388',
        'patient_name' => 'John Alexa',
        'treatment_type' => 'Udwarthana',
        'date_time' => 'March 20, 2026 - 12:00 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12366',
        'patient_name' => 'Andrea Devidpulle',
        'treatment_type' => 'Panchakarma Detox',
        'date_time' => 'March 20, 2026 - 12:30 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_ID' => 'P12363',
        'patient_name' => 'Ravi Kumar',
        'treatment_type' => 'Shirodhara',
        'date_time' => 'March 20, 2026 - 02:00 PM',
        'status' => 'In Progress'
    ),
    array(
        'patient_ID' => 'P12347',
        'patient_name' => 'Riyaz Veero',
        'treatment_type' => 'Abhyanga Massage',
        'date_time' => 'March 20, 2026 - 02:00 PM',
        'status' => 'In Progress'
    ),
    array(
        'patient_ID' => 'P12380',
        'patient_name' => 'Elani Fernando',
        'treatment_type' => 'Panchakarma Detox',
        'date_time' => 'March 20, 2026 - 02:30 PM',
        'status' => 'Pending'
    ),
    array(
        'patient_ID' => 'P12361',
        'patient_name' => 'Maya Singh',
        'treatment_type' => 'Udwarthana',
        'date_time' => 'March 20, 2026 - 03:30 PM',
        'status' => 'Pending'
    ),
    array(
        'patient_ID' => 'P12356',
        'patient_name' => 'Suresh Reddy',
        'treatment_type' => 'Nasya Karma',
        'date_time' => 'March 20, 2026 - 04:00 PM',
        'status' => 'Pending'
    ),
    array(
        'patient_ID' => 'P12384',
        'patient_name' => 'Nicola Muller',
        'treatment_type' => 'Panchakarma Detox',
        'date_time' => 'March 20, 2026 - 04:30 PM',
        'status' => 'Pending'
    ),
    array(
        'patient_ID' => 'P12368',
        'patient_name' => 'Deepa Nair',
        'treatment_type' => 'Basti',
        'date_time' => 'March 20, 2026 - 05:00 PM',
        'status' => 'Pending'
    )
);

function getStatusClass($status) {
    switch(strtolower($status)) {
        case 'completed':
            return 'status-completed';
        case 'in progress':
            return 'status-in-progress';
        case 'pending':
            return 'status-pending';
        default:
            return 'status-pending';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Schedule - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/stafftreatment.css?v=1.1">
</head>
<body>
    <!-- Header with ribbon style -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="staffhome.php" class="nav-btn">Home</a>
                <button class="nav-btn active">Treatment Schedule</button>
                <a href="staffappointment.php" class="nav-btn">Appointment</a>
                <a href="staffhomeTreatmentSuggestion.php" class="nav-btn">Treatment Suggestion</a>
                <a href="staffhomeReports.php" class="nav-btn">Reports</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
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
    </header>

    <main class="main-content">
        <div class="content">
            <div class="search-section">
                <input type="text" class="search-input" placeholder="Search">
                <button class="search-btn">🔍</button>
            </div>

            <div class="table-container">
                <table class="treatment-table">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Treatment Type</th>
                            <th>Date and Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($treatments as $treatment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($treatment['patient_ID']); ?></td>
                            <td><?php echo htmlspecialchars($treatment['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($treatment['treatment_type']); ?></td>
                            <td><?php echo htmlspecialchars($treatment['date_time']); ?></td>
                            <td>
                                <span class="status-badge <?php echo getStatusClass($treatment['status']); ?>">
                                    <?php echo htmlspecialchars($treatment['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
