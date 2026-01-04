<?php
header('Content-Type: application/json');
session_start();

// Check if staff is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get the appointment ID and action
$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/AppointmentModel.php';

$appointmentModel = new AppointmentModel($conn);

// Get the appointment details
$appointment = $appointmentModel->getAppointmentById($appointment_id);

if (!$appointment) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    exit;
}

// No room restriction - all staff can start any treatment
// Just verify consultation form exists

// Check if consultation form has been submitted
$sql = "SELECT id FROM consultationforms WHERE appointment_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$consultation_exists = $result->fetch_assoc();
$stmt->close();

if (!$consultation_exists) {
    echo json_encode(['success' => false, 'message' => 'Doctor has not submitted the consultation form yet']);
    exit;
}

// Perform the action
if ($action === 'start_treatment') {
    $status = 'In-Progress';
    $start_time = date('Y-m-d H:i:s');
    
    $success = $appointmentModel->updateTreatmentStatus($appointment_id, $status, $start_time);
    
    if ($success) {
        // Log the treatment start
        $log_sql = "INSERT INTO treatment_logs (appointment_id, action, staff_name, timestamp) 
                   VALUES (?, 'start', ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        if ($log_stmt) {
            $log_stmt->bind_param('iss', $appointment_id, $staff_name, $start_time);
            $log_stmt->execute();
            $log_stmt->close();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Treatment started successfully',
            'appointment_id' => $appointment_id,
            'status' => $status,
            'start_time' => $start_time
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update treatment status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
