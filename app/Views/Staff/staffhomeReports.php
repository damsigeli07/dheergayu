<?php
require_once __DIR__ . '/../../../config/config.php';

// Fetch all treatments and patient counts
$treatmentData = [];
$totalPatients = 0;

$sql = "
    SELECT 
        tl.treatment_name,
        COUNT(DISTINCT CASE 
            WHEN tp.status IN ('Confirmed', 'InProgress', 'Completed') 
            THEN tp.patient_id 
        END) AS patients
    FROM treatment_list tl
    LEFT JOIN treatment_plans tp 
        ON tp.treatment_id = tl.treatment_id
    WHERE tl.status = 'Active'
    GROUP BY tl.treatment_id
    ORDER BY patients DESC
";


$result = $conn->query($sql);

$colors = ['#FFB84D', '#FF8C42', '#E6A85A', '#D4A574'];
$colorIndex = 0;

while ($row = $result->fetch_assoc()) {
    $treatmentData[] = [
        'treatment' => $row['treatment_name'],
        'patients'  => (int)$row['patients'],
        'color'     => $colors[$colorIndex % count($colors)]
    ];
    $totalPatients += $row['patients'];
    $colorIndex++;
}

$totalTreatments = count($treatmentData);


// Search functionality
/*$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredReports = $reports;

if (!empty($searchQuery)) {
    $filteredReports = array_filter($reports, function($report) use ($searchQuery) {
        return stripos($report['patient_ID'], $searchQuery) !== false ||
               stripos($report['patient_name'], $searchQuery) !== false ||
               stripos($report['report_file'], $searchQuery) !== false;
    });
}
*/       

// Get total patients for all treatments

$mostPopular = 'N/A';
$maxPatients = 0;

foreach ($treatmentData as $t) {
    if ($t['patients'] > $maxPatients) {
        $maxPatients = $t['patients'];
        $mostPopular = $t['treatment'];
    }
}

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
            <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
            <a href="staffappointment.php" class="nav-btn">Appointment</a>
            <a href="staffhomeReports.php" class="nav-btn active">Reports</a>
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
                            <span class="summary-value"><?php echo $totalTreatments; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total Patients:</span>
                            <span class="summary-value"><?php echo $totalPatients; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Most Popular:</span>
                            <span class="summary-value"><?php echo htmlspecialchars($mostPopular); ?></span>

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

                    <!-- Actions -->
                    <div class="actions-section">
                       <div class="action-buttons">
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
                        text: 'Scheduled Treatment',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        color: '#2d2d2d'
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
                            color: '#2d2d2d',
                            font: {
                               size: 12
                            },
                            precision: 0,
                            callback: function(value) {
                               return Number.isInteger(value) ? value : null;
                            }
                        },

                        title: {
                            display: true,
                            text: 'Number of Patients',
                            color: '#2d2d2d',
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
                            color: '#2d2d2d',
                            font: {
                                size: 11
                            },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        title: {
                            display: true,
                            text: 'Treatment Types',
                            color: '#2d2d2d',
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
            borderColor: '#E6A85A',
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