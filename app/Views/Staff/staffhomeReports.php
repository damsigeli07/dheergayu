<?php
// Sample data - In a real application, this would come from a database
$reports = [
    ['patient_ID' => 'P12352', 'patient_name' => 'Arjun Patel', 'report_file' => '1001.pdf'],
    ['patient_ID' => 'P12341', 'patient_name' => 'Priya Sharma', 'report_file' => '1002.pdf'],
    ['patient_ID' => 'P12363', 'patient_name' => 'Ravi Kumar', 'report_file' => '1008.pdf'],
    ['patient_ID' => 'P12361', 'patient_name' => 'Maya Singh', 'report_file' => '1010.pdf'],
    ['patient_ID' => 'P12356', 'patient_name' => 'Suresh Reddy', 'report_file' => '1012.pdf'],
    ['patient_ID' => 'P12368', 'patient_name' => 'Deepa Nair', 'report_file' => '1030.pdf']
];

// Treatment data for chart - In real application, this would come from database
$treatmentData = [
    ['treatment' => 'Udwarthana', 'patients' => 45, 'color' => '#8BC34A'],
    ['treatment' => 'Nasya Karma', 'patients' => 38, 'color' => '#7CB342'],
    ['treatment' => 'Shirodhara Therapy', 'patients' => 32, 'color' => '#689F38'],
    ['treatment' => 'Basti', 'patients' => 28, 'color' => '#558B2F'],
    ['treatment' => 'Panchakarma Detox', 'patients' => 76, 'color' => '#33691E'],
    ['treatment' => 'Vashpa Sweda', 'patients' => 24, 'color' => '#8BC34A'],
    ['treatment' => 'Abhyanga Massage', 'patients' => 19, 'color' => '#7CB342'],
    ['treatment' => 'Elakizhi', 'patients' => 15, 'color' => '#689F38']
];

// Search functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredReports = $reports;

if (!empty($searchQuery)) {
    $filteredReports = array_filter($reports, function($report) use ($searchQuery) {
        return stripos($report['patient_ID'], $searchQuery) !== false ||
               stripos($report['patient_name'], $searchQuery) !== false ||
               stripos($report['report_file'], $searchQuery) !== false;
    });
}

// Get total patients for all treatments
$totalPatients = array_sum(array_column($treatmentData, 'patients'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Reports - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/staffhomeReports.css?v=2.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="has-sidebar">
    <!-- Header with ribbon-style design -->
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="staffhome.php" class="nav-btn">Home</a>
            <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
            <a href="staffappointment.php" class="nav-btn">Appointment</a>
            <a href="staffhomeReports.php" class="nav-btn active">Reports</a>
            <a href="staffroomallocation.php" class="nav-btn">Room Allocation</a>
        </nav>
        
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
        <div class="dashboard-content">
            <!-- Treatment Analytics Chart Section -->
            <div class="chart-panel">
                <div class="info-section">
                    <h3>Treatment Analytics</h3>
                    <div class="chart-container">
                        <canvas id="treatmentChart"></canvas>
                    </div>
                    
                    <!-- Chart Summary -->
                    <div class="chart-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total Treatments:</span>
                            <span class="summary-value"><?php echo count($treatmentData); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total Patients:</span>
                            <span class="summary-value"><?php echo $totalPatients; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Most Popular:</span>
                            <span class="summary-value"><?php echo $treatmentData[4]['treatment']; ?></span>
                        </div>
                    </div>

                    <!-- Treatment Details Table -->
                    <div class="treatment-details">
                        <h4>Treatment Breakdown</h4>
                        <table class="treatment-table">
                            <thead>
                                <tr>
                                    <th>Treatment</th>
                                    <th>Patients</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($treatmentData as $treatment): ?>
                                    <tr>
                                        <td>
                                            <div class="treatment-name">
                                                <span class="color-indicator" style="background-color: <?php echo $treatment['color']; ?>"></span>
                                                <?php echo htmlspecialchars($treatment['treatment']); ?>
                                            </div>
                                        </td>
                                        <td class="patient-count"><?php echo $treatment['patients']; ?></td>
                                        <td class="percentage"><?php echo round(($treatment['patients'] / $totalPatients) * 100, 1); ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="reports-panel">
                <div class="info-section">
                    <h3>Patient Reports</h3>
                    <!-- Search -->
                    <div class="search-section">
                        <form method="GET" class="search-form">
                            <input type="text" name="search" placeholder="Search reports..." value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
                            <button type="submit" class="search-btn">🔍</button>
                        </form>
                    </div>

                    <!-- Reports Table -->
                    <table class="suggestion-table">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
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
                                        <td><?php echo htmlspecialchars($report['patient_ID']); ?></td>
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
                            <button class="action-btn tertiary" onclick="downloadChart()">Download Chart</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Treatment data from PHP
        const treatmentData = <?php echo json_encode($treatmentData); ?>;
        
        // Initialize the chart
        const ctx = document.getElementById('treatmentChart').getContext('2d');
        const treatmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: treatmentData.map(item => item.treatment),
                datasets: [{
                    label: 'Number of Patients',
                    data: treatmentData.map(item => item.patients),
                    backgroundColor: treatmentData.map(item => item.color),
                    borderColor: treatmentData.map(item => item.color),
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Patient Bookings by Treatment',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        color: '#8B7355'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e9ecef',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#666',
                            font: {
                                size: 12
                            }
                        },
                        title: {
                            display: true,
                            text: 'Number of Patients',
                            color: '#8B7355',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#666',
                            font: {
                                size: 11
                            },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        title: {
                            display: true,
                            text: 'Treatment Types',
                            color: '#8B7355',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                elements: {
                    bar: {
                        borderRadius: 6
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Add hover effects and tooltips
        treatmentChart.options.plugins.tooltip = {
            backgroundColor: 'rgba(0,0,0,0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: '#8BC34A',
            borderWidth: 2,
            cornerRadius: 8,
            displayColors: false,
            callbacks: {
                title: function(tooltipItems) {
                    return tooltipItems[0].label;
                },
                label: function(context) {
                    const total = treatmentData.reduce((sum, item) => sum + item.patients, 0);
                    const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                    return [`Patients: ${context.parsed.y}`, `Percentage: ${percentage}%`];
                }
            }
        };

        // Update chart with new options
        treatmentChart.update();

        function exportReports() {
            alert('Export functionality would be implemented here');
        }

        function downloadChart() {
            // Create a link element and trigger download
            const link = document.createElement('a');
            link.download = 'treatment-analytics.png';
            link.href = treatmentChart.toBase64Image();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Add click event to chart bars
        treatmentChart.options.onClick = (event, activeElements) => {
            if (activeElements.length > 0) {
                const index = activeElements[0].index;
                const treatment = treatmentData[index];
                alert(`${treatment.treatment}\nPatients: ${treatment.patients}\nClick to view detailed report for this treatment.`);
            }
        };
    </script>
</body>
</html>