<?php
// Sample data - In a real application, this would come from a database
$patients = [
    ['patient_no' => '1001', 'patient_name' => 'Arjun Patel', 'treatment' => 'Udwarthana', 'suggestion' => 'Sensitive skin. Medium Level massage- For 20 minutes'],
    ['patient_no' => '1002', 'patient_name' => 'Priya Sharma', 'treatment' => 'Nasya Karma', 'suggestion' => 'For 15 minutes'],
    ['patient_no' => '1008', 'patient_name' => 'Ravi Kumar', 'treatment' => 'Shirodhara', 'suggestion' => 'No'],
    ['patient_no' => '1010', 'patient_name' => 'Maya Singh', 'treatment' => 'Udwarthana', 'suggestion' => 'High Level massage- For 30 minutes'],
    ['patient_no' => '1012', 'patient_name' => 'Suresh Reddy', 'treatment' => 'Nasya Karma', 'suggestion' => 'For 30 minutes'],
    ['patient_no' => '1030', 'patient_name' => 'Deepa Nair', 'treatment' => 'Shirodhara', 'suggestion' => 'No']
];

// Search functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredPatients = $patients;

if (!empty($searchQuery)) {
    $filteredPatients = array_filter($patients, function($patient) use ($searchQuery) {
        return stripos($patient['patient_no'], $searchQuery) !== false ||
               stripos($patient['patient_name'], $searchQuery) !== false ||
               stripos($patient['treatment'], $searchQuery) !== false ||
               stripos($patient['suggestion'], $searchQuery) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Suggestion - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/staffhomeTreatmentSuggestion.css?v=1.0">
</head>
<body>
    <!-- Header with ribbon-style design -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="staffhome.php" class="nav-btn">Home</a>
                <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
                <a href="staffappointment.php" class="nav-btn">Appointment</a>
                <a href="staffhomeTreatmentSuggestion.php" class="nav-btn active">Treatment Suggestion</a>
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
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-content">
            <!-- Full width Treatment Suggestion table -->
            <div class="left-panel" style="grid-column: 1 / -1;">
                <div class="info-section">
                    <h3>Treatment Suggestion</h3>
                    <div class="search-section">
                        <form method="GET" class="search-form">
                            <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
                            <button type="submit" class="search-btn">🔍</button>
                        </form>
                    </div>
                    <table class="suggestion-table">
                        <thead>
                            <tr>
                                <th>Patient No.</th>
                                <th>Patient Name</th>
                                <th>Treatment</th>
                                <th>Suggestion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filteredPatients)): ?>
                                <tr>
                                    <td colspan="4" class="no-results">No patients found matching your search.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($filteredPatients as $patient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($patient['patient_no']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['treatment']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['suggestion']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="summary-section">
                        <p>Total Patients: <?php echo count($filteredPatients); ?></p>
                        <?php if (!empty($searchQuery)): ?>
                            <p>Search Results for: "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                            <a href="staffhomeTreatmentSuggestion.php" class="clear-search">Clear Search</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
