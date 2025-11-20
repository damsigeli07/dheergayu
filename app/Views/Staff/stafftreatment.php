<?php
// Fetch treatments from database
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

// Fetch treatments excluding "General Consultation"
$query = "SELECT * FROM treatments WHERE treatment_type != 'General Consultation' ORDER BY appointment_date DESC, appointment_time DESC";
$result = $db->query($query);

$treatments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $treatments[] = [
            'id' => $row['id'],
            'patient_id' => $row['patient_id'],
            'patient_name' => $row['patient_name'],
            'treatment_type' => $row['treatment_type'],
            'appointment_date' => $row['appointment_date'],
            'appointment_time' => $row['appointment_time'],
            'status' => $row['status'],
            'treatment_fee' => $row['treatment_fee'],
            'duration' => $row['duration']
        ];
    }
}

$db->close();

function getStatusClass($status) {
    switch(strtolower($status)) {
        case 'completed':
            return 'status-completed';
        case 'in progress':
            return 'status-in-progress';
        case 'pending':
            return 'status-pending';
        case 'cancelled':
            return 'status-cancelled';
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
<body class="has-sidebar">
    <!-- Header with ribbon style -->
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="staffhome.php" class="nav-btn">Home</a>
            <button class="nav-btn active">Treatment Schedule</button>
            <a href="staffappointment.php" class="nav-btn">Appointment</a>
            <a href="staffhomeReports.php" class="nav-btn">Reports</a>
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
                            <th>Duration</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($treatments)): ?>
                            <?php foreach ($treatments as $treatment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($treatment['patient_id']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['treatment_type']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['appointment_date'] . ' - ' . $treatment['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['duration'] . ' min'); ?></td>
                                <td>Rs. <?php echo number_format($treatment['treatment_fee'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo getStatusClass($treatment['status']); ?>">
                                        <?php echo htmlspecialchars($treatment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (strtolower($treatment['status']) === 'pending'): ?>
                                        <button class="action-btn complete-btn" onclick="markAsCompleted(<?php echo $treatment['id']; ?>)">
                                            Mark Complete
                                        </button>
                                    <?php elseif (strtolower($treatment['status']) === 'completed'): ?>
                                        <span class="completed-text">✓ Completed</span>
                                    <?php else: ?>
                                        <span class="status-text"><?php echo htmlspecialchars($treatment['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px;">No treatments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function markAsCompleted(treatmentId) {
            if (confirm('Are you sure you want to mark this treatment as completed?')) {
                // Create form data
                const formData = new FormData();
                formData.append('treatment_id', treatmentId);
                formData.append('status', 'Completed');

                // Send AJAX request
                fetch('/dheergayu/app/Controllers/TreatmentController.php?action=update_status', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Treatment marked as completed successfully!');
                        location.reload(); // Refresh the page to show updated status
                    } else {
                        alert('Failed to update treatment status: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the treatment status.');
                });
            }
        }

        // Search functionality
        document.querySelector('.search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.treatment-table tbody tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let found = false;
                
                for (let i = 0; i < cells.length - 1; i++) { // Exclude actions column
                    if (cells[i].textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            });
        });
    </script>

    <style>
        .action-btn {
            background: #5d9b57;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
        }
        
        .action-btn:hover {
            background: #4a7c47;
        }
        
        .complete-btn {
            background: #28a745;
        }
        
        .complete-btn:hover {
            background: #218838;
        }
        
        .completed-text {
            color: #28a745;
            font-weight: 500;
            font-size: 12px;
        }
        
        .status-text {
            color: #6c757d;
            font-size: 12px;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</body>
</html>
