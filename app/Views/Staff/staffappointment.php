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

// Fetch appointments from appointments table (same as doctor dashboard)
$appointments = $appointmentModel->getAllDoctorAppointments();

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
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            display: inline-block;
            border: 1px solid transparent;
        }
        .status-badge.upcoming {
            background: #e8f5e9;
            color: #7a5a2b;
            border-color: #f3e6d5;
        }
        .status-badge.completed {
            background: #e3f2fd;
            color: #1976d2;
            border-color: #bbdefb;
        }
        .status-badge.cancelled {
            background: #ffebee !important;
            color: #c62828 !important;
            border-color: #ffcdd2 !important;
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

        function showCancelReason(btn, appointmentNo) {
            const confirmBox = document.createElement('div');
            confirmBox.style.position = 'fixed';
            confirmBox.style.top = '0';
            confirmBox.style.left = '0';
            confirmBox.style.width = '100vw';
            confirmBox.style.height = '100vh';
            confirmBox.style.background = 'rgba(0,0,0,0.2)';
            confirmBox.style.display = 'flex';
            confirmBox.style.alignItems = 'center';
            confirmBox.style.justifyContent = 'center';
            confirmBox.style.zIndex = '9999';
            confirmBox.innerHTML = `<div style="background:#f7fafc;padding:30px 40px;border-radius:10px;box-shadow:0 2px 10px #b2bec3;text-align:center;max-width:350px;">
                <p style='font-size:16px;margin-bottom:10px;'>Please provide a reason for cancellation:</p>
                <textarea id='cancel-reason' style='width:90%;height:60px;border-radius:6px;border:1px solid #b2bec3;margin-bottom:18px;'></textarea><br>
                <button id='cancel-no-btn' style='background:#b2dfdb;color:#333;padding:8px 18px;border:none;border-radius:6px;font-size:14px;margin-right:10px;cursor:pointer;'>No</button>
                <button id='cancel-next-btn' style='background:#e57373;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;cursor:pointer;'>Next</button>
            </div>`;
            document.body.appendChild(confirmBox);
            
            document.getElementById('cancel-no-btn').addEventListener('click', function() {
                document.body.removeChild(confirmBox);
            });
            
            document.getElementById('cancel-next-btn').addEventListener('click', function() {
                showFinalCancelConfirm(appointmentNo, confirmBox);
            });
        }

        function showFinalCancelConfirm(appointmentNo, previousDialog) {
            const reason = document.getElementById('cancel-reason').value.trim();
            if (!reason) {
                alert('Please enter a reason.');
                return;
            }
            
            document.body.removeChild(previousDialog);
            
            const confirmBox = document.createElement('div');
            confirmBox.style.position = 'fixed';
            confirmBox.style.top = '0';
            confirmBox.style.left = '0';
            confirmBox.style.width = '100vw';
            confirmBox.style.height = '100vh';
            confirmBox.style.background = 'rgba(0,0,0,0.2)';
            confirmBox.style.display = 'flex';
            confirmBox.style.alignItems = 'center';
            confirmBox.style.justifyContent = 'center';
            confirmBox.style.zIndex = '9999';
            confirmBox.innerHTML = `<div style="background:#f7fafc;padding:30px 40px;border-radius:10px;box-shadow:0 2px 10px #b2bec3;text-align:center;max-width:350px;">
                <p style='font-size:16px;margin-bottom:20px;'>Are you sure you want to cancel this appointment?</p>
                <button id='final-cancel-no-btn' style='background:#b2dfdb;color:#333;padding:8px 18px;border:none;border-radius:6px;font-size:14px;margin-right:10px;cursor:pointer;'>No</button>
                <button id='final-cancel-yes-btn' style='background:#e57373;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;cursor:pointer;'>Yes</button>
            </div>`;
            document.body.appendChild(confirmBox);
            
            document.getElementById('final-cancel-no-btn').addEventListener('click', function() {
                document.body.removeChild(confirmBox);
            });
            
            document.getElementById('final-cancel-yes-btn').addEventListener('click', function() {
                submitCancelReason(appointmentNo, reason, confirmBox);
            });
        }

        function submitCancelReason(appointmentNo, reason, dialogElement) {
            document.body.removeChild(dialogElement);
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/dheergayu/app/Controllers/AppointmentController.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert('Appointment cancelled!');
                    setTimeout(function(){ location.reload(); }, 1200);
                }
            };
            xhr.send('action=cancel&appointment_id=' + encodeURIComponent(appointmentNo) + '&reason=' + encodeURIComponent(reason));
        }

        function showCancelDetails(reason) {
            alert('Cancellation Reason:\n' + reason);
        }

        function showConsultationModal(appointmentId) {
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
                            
                            html += '<h3 style="color: #E6A85A; margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Patient Information</h3>';
                            html += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">';
                            html += '<p><strong>Name:</strong> ' + (data.first_name ? data.first_name + ' ' + (data.last_name || '') : 'N/A') + '</p>';
                            html += '<p><strong>Age:</strong> ' + (data.age || 'N/A') + '</p>';
                            html += '<p><strong>Gender:</strong> ' + (data.gender || 'N/A') + '</p>';
                            html += '<p><strong>Contact:</strong> ' + (data.contact_info || 'N/A') + '</p>';
                            html += '</div>';
                            
                            html += '<h3 style="color: #E6A85A; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Clinical Information</h3>';
                            html += '<div style="margin-bottom: 20px;">';
                            html += '<p><strong>Diagnosis:</strong></p>';
                            html += '<p style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #E6A85A;">' + (data.diagnosis || 'N/A') + '</p>';
                            html += '</div>';
                            
                            if (data.recommended_treatment) {
                                html += '<div style="margin-bottom: 20px;">';
                                html += '<p><strong>Recommended Treatment:</strong></p>';
                                html += '<p style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #4CAF50;">' + data.recommended_treatment + '</p>';
                                html += '</div>';
                            }
                            
                            if (data.notes) {
                                html += '<div style="margin-bottom: 20px;">';
                                html += '<p><strong>Special Notes/Instructions:</strong></p>';
                                html += '<p style="background: #fffbea; padding: 10px; border-radius: 4px; border-left: 3px solid #FFC107;">' + data.notes + '</p>';
                                html += '</div>';
                            }
                            
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
                </div>
            </div>

            <div class="table-container">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>Patient No.</th>
                            <th>Patient Name</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $apt): ?>
                                <?php
                                    $statusUpper = strtoupper(isset($apt['status']) ? trim($apt['status']) : '');
                                    if ($statusUpper === 'PENDING' || $statusUpper === 'CONFIRMED') {
                                        $status = 'Upcoming';
                                    } elseif ($statusUpper === 'COMPLETED') {
                                        $status = 'Completed';
                                    } elseif ($statusUpper === 'CANCELLED') {
                                        $status = 'Cancelled';
                                    } else {
                                        $status = $apt['status'];
                                    }
                                ?>
                                <tr class="appointment-row <?= strtolower($status) ?>" data-status="<?= strtolower($status) ?>">
                                    <td><?= htmlspecialchars($apt['appointment_id']) ?></td>
                                    <td><?= htmlspecialchars($apt['patient_no']) ?></td>
                                    <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                                    <td><?= htmlspecialchars($apt['appointment_datetime']) ?></td>
                                    <td>
                                        <?php if ($status === 'Upcoming') : ?>
                                            <span class="status-badge upcoming">Upcoming</span>
                                        <?php elseif ($status === 'Completed') : ?>
                                            <span class="status-badge completed">Completed</span>
                                        <?php elseif ($status === 'Cancelled') : ?>
                                            <span class="status-badge cancelled">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <?php if ($status === 'Upcoming') : ?>
                                            <button class="btn-start" onclick="window.open('doctorconsultform.php?appointment_id=<?= htmlspecialchars($apt['appointment_id']) ?>', '_blank')">Start Consultation</button>
                                            <button class="btn-cancel" onclick="showCancelReason(this, '<?= htmlspecialchars($apt['appointment_id']) ?>')">Cancel</button>
                                        <?php elseif ($status === 'Completed') : ?>
                                            <button class="btn-view" onclick="showConsultationModal(<?= htmlspecialchars($apt['appointment_id']) ?>)">View</button>
                                        <?php elseif ($status === 'Cancelled') : ?>
                                            <button class="btn-view" onclick="showCancelDetails('<?= htmlspecialchars($apt['reason'] ?? '') ?>')">View</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No appointments found.</td></tr>
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
