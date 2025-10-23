<?php
// public/api/cancel-appointment.php

ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/AppointmentModel.php';

$model = new AppointmentModel($conn);

// Get appointment details before cancellation
$table = ($type === 'consultation') ? 'consultations' : 'treatments';
$stmt = $conn->prepare("SELECT appointment_date, appointment_time FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();

$id = intval($_POST['id'] ?? 0);
$type = $_POST['type'] ?? 'consultation';

if (!$id) {
    echo json_encode(['error' => 'Invalid appointment ID']);
    exit;
}

if ($model->cancelAppointment($id, $type)) {
    // Release any locks on this slot when cancelled
    if ($appointment) {
        $model->releaseSlot($appointment['appointment_date'], $appointment['appointment_time'], $_SESSION['user_id']);
    }
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
} else {
    echo json_encode(['error' => 'Failed to cancel appointment']);
}
?>