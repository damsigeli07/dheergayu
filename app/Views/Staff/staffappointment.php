<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if staff is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../patient/login.php');
    exit();
}

// Fetch appointments from database
require_once __DIR__ . '/../../Models/AppointmentModel.php';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');
$appointmentModel = new AppointmentModel($db);

// Get filter from GET parameter
$selectedRoom = isset($_GET['room']) ? $_GET['room'] : 'All';

// Fetch appointments with consultation forms and booking details
if ($selectedRoom === 'All') {
    $appointments = $appointmentModel->getStaffAppointmentsWithConsultationsAndBookings();
} else {
    $appointments = $appointmentModel->getStaffAppointmentsWithConsultationsAndBookings();
    // filter by treatment type if requested
    $appointments = array_filter($appointments, function($a) use ($selectedRoom) {
        return ($a['treatment_room'] ?? '') === $selectedRoom;
    });
    $appointments = array_values($appointments);
}

// Filter to show ONLY appointments with submitted consultation forms
$appointments = array_filter($appointments, function($apt) {
    return $apt['consultation_status'] === 'Consultation Submitted';
});
$appointments = array_values($appointments);

// Get available treatment rooms for filter
$availableRooms = $appointmentModel->getAvailableTreatmentRooms();

// If no appointments found, use empty array
if (!$appointments) {
    $appointments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/staffappointment.css?v=1.1">
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-badge.pending {
            background-color: #FFF4E6;
            color: #E6A85A;
        }
        .status-badge.completed {
            background-color: #FFF4E6;
            color: #D4A574;
        }
        .status-badge.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
    <script>
        function searchTable() {
            const input = document.querySelector('.search-input');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.appointments-table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? '' : 'none';
            }
        }

        function filterByRoom() {
            const roomFilter = document.getElementById('room-filter');
            const selectedRoom = roomFilter.value;
            
            // Redirect with room parameter
            if (selectedRoom === 'All') {
                window.location.href = 'staffappointment.php';
            } else {
                window.location.href = 'staffappointment.php?room=' + encodeURIComponent(selectedRoom);
            }
        }

        function startTreatment(appointmentId) {
            const confirmed = confirm('Are you sure you want to start this treatment? Make sure the patient is present and ready.');
            
            if (!confirmed) return;
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/dheergayu/public/api/update-treatment-status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert('Treatment started successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.message || 'Failed to start treatment'));
                        }
                    } catch (e) {
                        alert('Error processing response: ' + e.message);
                    }
                } else {
                    alert('Server error: ' + xhr.status);
                }
            };
            
            xhr.onerror = function() {
                alert('Network error. Please try again.');
            };
            
            xhr.send('appointment_id=' + appointmentId + '&action=start_treatment');
        }

        function viewConsultationForm(appointmentId) {
            const modal = document.getElementById('consultationModal');
            const content = document.getElementById('consultationContent');
            
            content.innerHTML = '<p style="text-align: center; color: #999;">Loading consultation form...</p>';
            modal.style.display = 'flex';
            
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '/dheergayu/app/Controllers/ConsultationFormController.php?action=get_consultation_form&appointment_id=' + appointmentId, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        
                        if (data && Object.keys(data).length > 0) {
                            let html = '<div style="background: #f9f9f9; padding: 15px; border-radius: 6px;">';
                            
                            // Patient Information
                            html += '<h3 style="color: #E6A85A; margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Patient Information</h3>';
                            html += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">';
                            html += '<p><strong>Name:</strong> ' + (data.first_name ? data.first_name + ' ' + (data.last_name || '') : 'N/A') + '</p>';
                            html += '<p><strong>Age:</strong> ' + (data.age || 'N/A') + '</p>';
                            html += '<p><strong>Gender:</strong> ' + (data.gender || 'N/A') + '</p>';
                            html += '<p><strong>Contact:</strong> ' + (data.contact_info || 'N/A') + '</p>';
                            html += '</div>';
                            
                            // Clinical Information
                            html += '<h3 style="color: #E6A85A; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Clinical Information</h3>';
                            html += '<div style="margin-bottom: 20px;">';
                            html += '<p><strong>Diagnosis:</strong></p>';
                            html += '<p style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #E6A85A;">' + (data.diagnosis || 'N/A') + '</p>';
                            html += '</div>';
                            
                            // Recommended Treatment
                            if (data.recommended_treatment) {
                                html += '<div style="margin-bottom: 20px;">';
                                html += '<p><strong>Recommended Treatment:</strong></p>';
                                html += '<p style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #4CAF50;">' + data.recommended_treatment + '</p>';
                                html += '</div>';
                            }
                            
                            // Special Notes
                            if (data.notes) {
                                html += '<div style="margin-bottom: 20px;">';
                                html += '<p><strong>Special Notes/Instructions:</strong></p>';
                                html += '<p style="background: #fffbea; padding: 10px; border-radius: 4px; border-left: 3px solid #FFC107;">' + data.notes + '</p>';
                                html += '</div>';
                            }
                            
                            // Prescribed Products
                            if (data.personal_products && data.personal_products !== '[]') {
                                try {
                                    const products = JSON.parse(data.personal_products);
                                    if (products.length > 0) {
                                        html += '<div style="margin-bottom: 20px;">';
                                        html += '<p><strong>Prescribed Products:</strong></p>';
                                        html += '<ul style="background: white; padding: 10px 10px 10px 25px; border-radius: 4px; margin: 0;">';
                                        products.forEach(function(product) {
                                            html += '<li>' + product.name + ' (Qty: ' + product.quantity + ')</li>';
                                        });
                                        html += '</ul>';
                                        html += '</div>';
                                    }
                                } catch (e) {
                                    // Invalid JSON, skip
                                }
                            }
                            
                            // Additional Notes
                            html += '<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">';
                            html += '<p style="font-size: 13px; color: #666; margin: 0;"><strong>Previous Visits:</strong> ' + (data.total_visits || '0') + '</p>';
                            html += '</div>';
                            
                            html += '</div>';
                            content.innerHTML = html;
                        } else {
                            content.innerHTML = '<p style="color: #999; text-align: center;">No consultation form data found.</p>';
                        }
                    } catch (e) {
                        content.innerHTML = '<p style="color: #d32f2f; text-align: center;">Error loading consultation form: ' + e.message + '</p>';
                    }
                } else {
                    content.innerHTML = '<p style="color: #d32f2f; text-align: center;">Server error loading consultation form.</p>';
                }
            };
            
            xhr.onerror = function() {
                content.innerHTML = '<p style="color: #d32f2f; text-align: center;">Network error loading consultation form.</p>';
            };
            
            xhr.send();
        }

        function closeConsultationModal() {
            document.getElementById('consultationModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('consultationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConsultationModal();
            }
        });
    </script>
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
            <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
            <button class="nav-btn active">Appointment</button>
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
            <div class="top-section">
                <div class="page-title">
                    <h2>Appointments</h2>
                </div>
                <div class="action-section">
                    <div class="search-section">
                        <input type="text" class="search-input" placeholder="Search" onkeyup="searchTable()">
                        <button class="search-btn" onclick="searchTable()">🔍</button>
                    </div>
                    <div class="filter-section" style="display: flex; gap: 10px; align-items: center;">
                        <label for="room-filter" style="font-weight: 600; color: #333;">Filter by Room:</label>
                        <select id="room-filter" class="room-filter" onchange="filterByRoom()" style="padding: 8px 12px; border-radius: 4px; border: 1px solid #ddd; font-size: 14px;">
                            <option value="All" <?php echo $selectedRoom === 'All' ? 'selected' : ''; ?>>All Rooms</option>
                            <?php foreach ($availableRooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room); ?>" <?php echo $selectedRoom === $room ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>Patient No.</th>
                            <th>Patient Name</th>
                            <th>Date and Time</th>
                            <th>Treatment Room</th>
                            <th>Consultation Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['appointment_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($appointment['patient_no'] ?? ''); ?></td>
                                <td class="patient-name"><?php echo htmlspecialchars($appointment['patient_name'] ?? ''); ?></td>
                                <td class="date-time">
                                    <?php if (!empty($appointment['booking_date']) || !empty($appointment['slot_time'])): ?>
                                        <?php echo htmlspecialchars(($appointment['booking_date'] ?? '') . ' ' . ($appointment['slot_time'] ?? '')); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($appointment['appointment_datetime'] ?? ''); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="room-badge" style="background-color: #E6A85A; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <?php echo htmlspecialchars($appointment['treatment_room'] ?? 'General'); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-view-notes" onclick="viewConsultationForm(<?php echo $appointment['appointment_id']; ?>)" 
                                        style="background: #2196F3; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                        View Notes
                                    </button>
                                </td>
                                <td>
                                    <?php
                                    // Determine if booking is upcoming or completed based on booking_date + slot_time
                                    $now = time(); // Current timestamp
                                    $is_completed = false;
                                    $is_not_completed = false;
                                    $can_start = false;

                                    if (!empty($appointment['booking_date']) && !empty($appointment['slot_time'])) {
                                        // Combine booking_date and slot_time for full datetime comparison
                                        $booking_datetime = $appointment['booking_date'] . ' ' . $appointment['slot_time'];
                                        $booking_ts = strtotime($booking_datetime);
                                        
                                        if ($booking_ts >= $now) {
                                            // Upcoming treatment (date + time is in future) - show Start button
                                            $can_start = true;
                                        } else {
                                            // Past booking (date + time has passed)
                                            $status_lower = strtolower($appointment['status'] ?? '');
                                            if ($status_lower === 'completed') {
                                                // Marked as completed - show Completed label
                                                $is_completed = true;
                                            } else {
                                                // Time slot has passed but not marked as completed - show Not Completed
                                                $is_not_completed = true;
                                            }
                                        }
                                    } else if (!empty($appointment['booking_date'])) {
                                        // Only date available, check against today
                                        $booking_ts = strtotime($appointment['booking_date']);
                                        $today_ts = strtotime(date('Y-m-d'));
                                        
                                        if ($booking_ts >= $today_ts) {
                                            $can_start = true;
                                        } else {
                                            // Past booking
                                            $status_lower = strtolower($appointment['status'] ?? '');
                                            if ($status_lower === 'completed') {
                                                $is_completed = true;
                                            } else {
                                                $is_not_completed = true;
                                            }
                                        }
                                    } else if (!empty($appointment['appointment_datetime'])) {
                                        // Fallback to appointment_datetime if booking_date not set
                                        $appt_ts = strtotime($appointment['appointment_datetime']);
                                        if ($appt_ts >= $now) {
                                            $can_start = true;
                                        } else {
                                            // Past appointment
                                            $status_lower = strtolower($appointment['status'] ?? '');
                                            if ($status_lower === 'completed') {
                                                $is_completed = true;
                                            } else {
                                                $is_not_completed = true;
                                            }
                                        }
                                    }
                                    ?>
                                    <?php if ($is_completed): ?>
                                        <span style="background: #4CAF50; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block;">
                                            ✓ Completed
                                        </span>
                                    <?php elseif ($is_not_completed): ?>
                                        <span style="background: #FF9800; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block;">
                                            ✗ Not Completed
                                        </span>
                                    <?php elseif ($can_start): ?>
                                    <?php
                                        $tpUrl = 'treatment_progress.php?appointment_id=' . urlencode($appointment['appointment_id']);
                                        if (!empty($appointment['booking_id'])) {
                                            $tpUrl .= '&booking_id=' . urlencode($appointment['booking_id']);
                                        }
                                    ?>
                                    <a href="<?php echo $tpUrl; ?>" 
                                    style="background: #4CAF50; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; text-decoration: none; display: inline-block;">
                                        Start
                                    </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">Not upcoming</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">No appointments with submitted consultation forms found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- Consultation Form Modal -->
    <div id="consultationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: white; border-radius: 8px; max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #E6A85A; padding-bottom: 15px;">
                <h2 style="margin: 0; color: #333;">Consultation Form</h2>
                <button onclick="closeConsultationModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">×</button>
            </div>
            
            <div id="consultationContent" style="color: #333; font-size: 14px; line-height: 1.6;">
                <p style="text-align: center; color: #999;">Loading...</p>
            </div>
        </div>
    </div>
</body>
</html>
