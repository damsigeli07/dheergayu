<?php
require_once __DIR__ . '/../../../core/bootloader.php';

// Check if staff is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../patient/login.php');
    exit();
}

// Connect to DB via Core Database
$db = \Core\Database::connect();
require_once __DIR__ . '/../../../app/Models/TreatmentProgressModel.php';
require_once __DIR__ . '/../../../app/Models/ConsultationFormModel.php';

$progressModel = new TreatmentProgressModel($db);
$cfModel = new ConsultationFormModel($db);

// Get staff_id from session
$staff_id = $_SESSION['user_id'] ?? null;

// Get appointment_id and booking_id from URL
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CURRENT appointment status BEFORE we do anything
    $check_stmt = $db->prepare("SELECT status FROM appointments WHERE appointment_id = ? LIMIT 1");
    if ($check_stmt) {
        $check_stmt->bind_param('i', $appointment_id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        if ($check_row = $check_res->fetch_assoc()) {
            error_log("FORM SUBMIT START: appointment_id=$appointment_id, current status=" . $check_row['status']);
        }
        $check_stmt->close();
    }
    
    // Collect observations checkboxes
    $observations = [];
    if (isset($_POST['observations']) && is_array($_POST['observations'])) {
        $observations = $_POST['observations'];
    }

    $data = [
        'appointment_id' => $appointment_id,
        'booking_id' => $booking_id ?: null,
        'progress_notes' => $_POST['progress'] ?? '',
        'materials_used' => $_POST['materials'] ?? '',
        'patient_response' => $_POST['response'] ?? 'good',
        'observations' => $observations,
        'additional_notes' => $_POST['additional'] ?? '',
        'duration_minutes' => !empty($_POST['duration']) ? intval($_POST['duration']) : null
    ];

    if ($progressModel->saveProgress($data)) {
        error_log("Treatment progress saved for appointment_id: " . $appointment_id);
        
        // Mark appointment as completed (updated cache bust)
        $update_stmt = $db->prepare("UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?");
        $rows_affected = 0;
        $update_error = '';
        $current_status = '';
        
        if ($update_stmt) {
            $update_stmt->bind_param('i', $appointment_id);
            $success = $update_stmt->execute();
            $rows_affected = $update_stmt->affected_rows;
            error_log("Update query executed. Rows affected: " . $rows_affected);
            
            if ($success && $rows_affected > 0) {
                error_log("Successfully updated appointment " . $appointment_id . " status to Completed. Rows: " . $rows_affected);
            } else {
                $update_error = $update_stmt->error;
                error_log("Failed to update appointment status. Success: " . ($success ? 'true' : 'false') . ", Error: " . $update_error . ", Rows affected: " . $rows_affected);
            }
            $update_stmt->close();
            
            // Verify the update
            $verify_stmt = $db->prepare("SELECT status FROM appointments WHERE appointment_id = ? LIMIT 1");
            if ($verify_stmt) {
                $verify_stmt->bind_param('i', $appointment_id);
                $verify_stmt->execute();
                $verify_res = $verify_stmt->get_result();
                if ($verify_row = $verify_res->fetch_assoc()) {
                    $current_status = $verify_row['status'];
                    error_log("Appointment " . $appointment_id . " current status: " . $current_status);
                }
                $verify_stmt->close();
            }
        } else {
            $update_error = $db->error;
            error_log("Failed to prepare update statement: " . $update_error);
        }
        
        // Give a moment to ensure DB is updated
        usleep(500000);
        
        $success_message = "Treatment progress saved successfully! Status update: Rows affected=$rows_affected, Current status=$current_status" . ($update_error ? ", Error: $update_error" : "");
        // Redirect back to appointments after 5 seconds with cache bust
        header('Refresh: 5; url=staffappointment.php?t=' . time());
    } else {
        error_log("Failed to save treatment progress for appointment_id: " . $appointment_id);
        $error_message = 'Failed to save treatment progress. Please try again.';
    }
}

// Prepare default placeholder data
$dummy_data = [
    'patient_name' => 'Unknown Patient',
    'patient_initials' => 'NA',
    'patient_no' => '',
    'room_name' => 'General',
    'treatment_type' => 'General',
    'treatment_name' => 'General Treatment',
    'current_session' => 1,
    'total_sessions' => 1,
    'duration' => 'N/A',
    'booking_date' => '',
    'session_time' => '',
    'diagnosis' => '',
    'age' => '',
    'gender' => ''
];

// Try to load real data when appointment_id is provided
if ($appointment_id > 0) {
    $appt = $cfModel->getAppointmentDetails($appointment_id);
    $consult = $cfModel->getConsultationFormByAppointmentId($appointment_id);

    if ($appt) {
        $dummy_data['patient_name'] = $appt['patient_name'] ?? $dummy_data['patient_name'];
        $dummy_data['patient_no'] = $appt['patient_no'] ?? $dummy_data['patient_no'];
        $dummy_data['age'] = $appt['age'] ?? $dummy_data['age'];
        $dummy_data['gender'] = $appt['gender'] ?? $dummy_data['gender'];
        // Extract initials
        $names = explode(' ', $dummy_data['patient_name']);
        $dummy_data['patient_initials'] = substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : '');
    }
    if ($consult) {
        $dummy_data['diagnosis'] = $consult['diagnosis'] ?? $dummy_data['diagnosis'];
        $dummy_data['recommended_treatment'] = $consult['recommended_treatment'] ?? '';
    }

    // If booking_id provided, fetch booking details
    if ($booking_id > 0) {
        $bstmt = $db->prepare("SELECT tb.booking_id, tb.booking_date, tb.slot_id, ts.slot_time, tl.treatment_name
            FROM treatment_bookings tb
            LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id
            LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id
            WHERE tb.booking_id = ? LIMIT 1");
        if ($bstmt) {
            $bstmt->bind_param('i', $booking_id);
            $bstmt->execute();
            $bres = $bstmt->get_result();
            if ($brow = $bres->fetch_assoc()) {
                $dummy_data['booking_date'] = $brow['booking_date'];
                $dummy_data['session_time'] = $brow['slot_time'];
                $dummy_data['treatment_name'] = $brow['treatment_name'] ?? $dummy_data['treatment_name'];
                // Determine room based on treatment name
                if (stripos($brow['treatment_name'], 'udwarthana') !== false) {
                    $dummy_data['room_name'] = 'Udwarthana';
                } elseif (stripos($brow['treatment_name'], 'nasya') !== false) {
                    $dummy_data['room_name'] = 'Nasya Karma';
                } elseif (stripos($brow['treatment_name'], 'shirodhara') !== false) {
                    $dummy_data['room_name'] = 'Shirodhara';
                } elseif (stripos($brow['treatment_name'], 'basti') !== false) {
                    $dummy_data['room_name'] = 'Basti';
                } else {
                    $dummy_data['room_name'] = 'General';
                }
                $dummy_data['treatment_type'] = strtoupper(substr($brow['treatment_name'], 0, 15));
            }
            $bstmt->close();
        }
    }
}

$current_session = $dummy_data['current_session'];
$total_sessions = $dummy_data['total_sessions'];
$progress_percentage = ($current_session / max(1, $total_sessions)) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Treatment Progress</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/treatment_progress.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Record Treatment Progress</h1>
                <span class="room-badge"><?php echo $dummy_data['room_name'] . ' - ' . $dummy_data['treatment_type']; ?></span>
            </div>
            
            <div class="patient-section">
                <div class="patient-header">
                    <div class="patient-avatar"><?php echo $dummy_data['patient_initials']; ?></div>
                    <div class="patient-info">
                        <h2><?php echo $dummy_data['patient_name']; ?></h2>
                        <p>Today's Session | <?php echo $dummy_data['session_time']; ?></p>
                    </div>
                </div>
                
                <div class="treatment-details">
                    <div class="detail-item">
                        <strong>TREATMENT</strong>
                        <span><?php echo $dummy_data['treatment_name']; ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>CURRENT SESSION</strong>
                        <span>Session <?php echo $current_session; ?> of <?php echo $total_sessions; ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>DURATION</strong>
                        <span><?php echo $dummy_data['duration']; ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>BOOKING DATE</strong>
                        <span><?php echo $dummy_data['booking_date']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="progress-indicator">
                <div class="progress-text">
                    <span>Treatment Progress</span>
                    <span><strong><?php echo $current_session; ?> of <?php echo $total_sessions; ?></strong> sessions completed</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;"></div>
                </div>
            </div>
            
            <div class="info-box doctor-instructions">
                <div class="info-icon">📋</div>
                <div class="info-content">
                    <h3>Doctor's Instructions</h3>
                    <p><?php echo $dummy_data['diagnosis']; ?></p>
                </div>
            </div>
            
            <div class="info-box reminder">
                <div class="info-icon">💡</div>
                <div class="info-content">
                    <p>Record today's treatment details and schedule the next appointment below</p>
                </div>
            </div>

            <?php if ($success_message): ?>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 16px;">
                <strong>✓ Success:</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 16px;">
                <strong>✗ Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-section">
                    <div class="section-icon">📝</div>
                    <h3>Treatment Notes</h3>
                    
                    <div class="form-group">
                        <label>Treatment Progress & Observations *</label>
                        <textarea 
                            name="progress" 
                            placeholder="Example: Session completed successfully. Used triphala powder mixture. Patient tolerated treatment well. No adverse reactions observed. Focused on upper body as instructed..." 
                            required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Materials Used</label>
                        <input 
                            type="text" 
                            name="materials" 
                            placeholder="Example: Triphala powder, Kolakulathadi choornam">
                    </div>

                    <div class="form-group">
                        <label>Session Duration (minutes)</label>
                        <input 
                            type="number" 
                            name="duration" 
                            min="0"
                            max="999"
                            placeholder="Example: 45">
                    </div>
                    
                    <div class="form-group">
                        <label>Patient Response</label>
                        <select name="response">
                            <option value="excellent">Excellent - Very comfortable</option>
                            <option value="good" selected>Good - Comfortable</option>
                            <option value="moderate">Moderate - Some discomfort</option>
                            <option value="poor">Poor - Uncomfortable</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Observations</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="obs1" name="observations[]" value="no_allergies">
                                <label for="obs1">No allergic reactions noted</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="obs2" name="observations[]" value="normal_skin">
                                <label for="obs2">Skin appeared normal after treatment</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="obs3" name="observations[]" value="relaxed">
                                <label for="obs3">Patient felt relaxed after session</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="obs4" name="observations[]" value="follow_protocol">
                                <label for="obs4">Treatment protocol followed as prescribed</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Additional Notes (Optional)</label>
                        <textarea 
                            name="additional" 
                            rows="3"
                            placeholder="Any special notes, recommendations, or things to remember for next session..."></textarea>
                    </div>
                </div>
                
                <div class="actions">
                    <a href="staffappointment.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Progress & Complete</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>