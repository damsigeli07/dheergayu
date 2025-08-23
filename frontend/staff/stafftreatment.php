<?php
// Sample data - In a real application, this would come from a database
$treatments = array(
    array(
        'patient_no' => '1001',
        'patient_name' => 'Arjun Patel',
        'treatment_type' => 'Oil Massage',
        'date_time' => 'March 20, 2024 - 10:00 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_no' => '1002',
        'patient_name' => 'Priya Sharma',
        'treatment_type' => 'Steam Therapy',
        'date_time' => 'March 20, 2024 - 11:30 AM',
        'status' => 'Completed'
    ),
    array(
        'patient_no' => '1008',
        'patient_name' => 'Ravi Kumar',
        'treatment_type' => 'Shirodhara',
        'date_time' => 'March 20, 2024 - 2:00 PM',
        'status' => 'Pending'
    ),
    array(
        'patient_no' => '1010',
        'patient_name' => 'Maya Singh',
        'treatment_type' => 'Oil Massage',
        'date_time' => 'March 20, 2024 - 3:30 PM',
        'status' => 'In Progress'
    ),
    array(
        'patient_no' => '1012',
        'patient_name' => 'Suresh Reddy',
        'treatment_type' => 'Steam Therapy',
        'date_time' => 'March 20, 2024 - 4:00 PM',
        'status' => 'Pending'
    ),
    array(
        'patient_no' => '1030',
        'patient_name' => 'Deepa Nair',
        'treatment_type' => 'Shirodhara',
        'date_time' => 'March 20, 2024 - 5:00 PM',
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
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/stafftreatment.css?v=1.1">
</head>
<body>
    <!-- Header with ribbon style -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="staffhome.php" class="nav-btn">Home</a>
                <button class="nav-btn active">Treatment Schedule</button>
                <a href="staffappointment.php" class="nav-btn">Appointment</a>
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
                            <th>Patient No</th>
                            <th>Patient Name</th>
                            <th>Treatment Type</th>
                            <th>Date and Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($treatments as $treatment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($treatment['patient_no']); ?></td>
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
