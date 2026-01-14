<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$id = intval($_POST['id'] ?? 0);
$type = $_POST['type'] ?? 'consultation';

if (!$id) {
    echo json_encode(['error' => 'Invalid appointment ID']);
    exit;
}

// Get appointment details before cancellation
$table = ($type === 'consultation') ? 'consultations' : 'consultations';
$stmt = $conn->prepare("SELECT appointment_date, appointment_time FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Cancel the appointment
$updateStmt = $conn->prepare("UPDATE $table SET status = 'Cancelled' WHERE id = ?");
$updateStmt->bind_param("i", $id);

if ($updateStmt->execute()) {
    // Release any locks on this slot when cancelled
    if ($appointment) {
        $deleteStmt = $conn->prepare("DELETE FROM consultations 
                                       WHERE appointment_date = ? 
                                       AND appointment_time = ? 
                                       AND status = 'locked'");
        $deleteStmt->bind_param("ss", $appointment['appointment_date'], $appointment['appointment_time']);
        $deleteStmt->execute();
        $deleteStmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
} else {
    echo json_encode(['error' => 'Failed to cancel appointment']);
}

$updateStmt->close();
$conn->close();
?>