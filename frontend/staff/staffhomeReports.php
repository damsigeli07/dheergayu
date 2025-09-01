<?php
// Sample data - In a real application, this would come from a database
$reports = [
    ['patient_no' => '1001', 'patient_name' => 'Arjun Patel', 'report_file' => '1001.pdf'],
    ['patient_no' => '1002', 'patient_name' => 'Priya Sharma', 'report_file' => '1002.pdf'],
    ['patient_no' => '1008', 'patient_name' => 'Ravi Kumar', 'report_file' => '1008.pdf'],
    ['patient_no' => '1010', 'patient_name' => 'Maya Singh', 'report_file' => '1010.pdf'],
    ['patient_no' => '1012', 'patient_name' => 'Suresh Reddy', 'report_file' => '1012.pdf'],
    ['patient_no' => '1030', 'patient_name' => 'Deepa Nair', 'report_file' => '1030.pdf']
];

// Search functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredReports = $reports;

if (!empty($searchQuery)) {
    $filteredReports = array_filter($reports, function($report) use ($searchQuery) {
        return stripos($report['patient_no'], $searchQuery) !== false ||
               stripos($report['patient_name'], $searchQuery) !== false ||
               stripos($report['report_file'], $searchQuery) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Reports - Ayurvedic System</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/staffhomeReports.css?v=1.0">
</head>
<body>
    <!-- Header with ribbon-style design -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="staffhome.php" class="nav-btn">Home</a>
                <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
                <a href="staffappointment.php" class="nav-btn">Appointment</a>
                <a href="staffhomeTreatmentSuggestion.php" class="nav-btn">Treatment Suggestion</a>
                <a href="staffhomeReports.php" class="nav-btn active">Reports</a>
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
            <div class="left-panel" style="grid-column: 1 / -1;">
                <div class="info-section">
                    <h3>Reports</h3>
                    <!-- Search -->
                    <div class="search-section">
                        <form method="GET" class="search-form">
                            <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
                            <button type="submit" class="search-btn">🔍</button>
                        </form>
                    </div>

                    <!-- Reports Table -->
                    <table class="suggestion-table">
                        <thead>
                            <tr>
                                <th>Patient No.</th>
                                <th>Patient Name</th>
                                <th>Report File</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filteredReports)): ?>
                                <tr>
                                    <td colspan="3" class="no-results">No reports found matching your search.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($filteredReports as $report): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['patient_no']); ?></td>
                                        <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                                        <td>
                                            <a href="reports/<?php echo htmlspecialchars($report['report_file']); ?>" class="pdf-link" target="_blank">
                                                <?php echo htmlspecialchars($report['report_file']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Summary -->
                    <div class="summary-section">
                        <p>Total Reports: <?php echo count($filteredReports); ?></p>
                        <?php if (!empty($searchQuery)): ?>
                            <p>Search Results for: "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                            <a href="staffhomeReports.php" class="clear-search">Clear Search</a>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="actions-section">
                        <h3>Report Actions</h3>
                        <div class="action-buttons">
                            <button class="action-btn primary" onclick="window.print()">Print Report List</button>
                            <button class="action-btn secondary" onclick="exportReports()">Export to Excel</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        function exportReports() {
            alert('Export functionality would be implemented here');
        }
    </script>
</body>
</html>
