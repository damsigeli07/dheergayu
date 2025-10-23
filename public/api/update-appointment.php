<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/AppointmentModel.php';

$id = $_POST['id'] ?? null;
$type = $_POST['type'] ?? null;
$date = $_POST['date'] ?? null;
$time = $_POST['time'] ?? null;

if (!$id || !$type || !$date || !$time) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$model = new AppointmentModel($conn);
$table = ($type === 'consultation') ? 'consultations' : 'treatments';

// Get current appointment details
$stmt = $conn->prepare("SELECT appointment_date, appointment_time FROM $table WHERE id = ? AND patient_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current) {
    echo json_encode(['success' => false, 'error' => 'Appointment not found']);
    exit;
}

// If date/time hasn't changed, just allow it
if ($current['appointment_date'] === $date && $current['appointment_time'] === $time) {
    echo json_encode(['success' => true]);
    exit;
}

// Check if new slot is available
if (!$model->isSlotAvailable($date, $time)) {
    echo json_encode(['success' => false, 'error' => 'Selected slot is no longer available']);
    exit;
}

// Try to lock the new slot
if (!$model->lockSlot($date, $time, $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unable to secure the selected slot']);
    exit;
}

// Update the appointment
$stmt = $conn->prepare("UPDATE $table SET appointment_date = ?, appointment_time = ? WHERE id = ? AND patient_id = ?");
$stmt->bind_param("ssii", $date, $time, $id, $_SESSION['user_id']);

if ($stmt->execute()) {
    // Release the lock after successful update
    $model->releaseSlot($date, $time, $_SESSION['user_id']);
    echo json_encode(['success' => true]);
} else {
    // Release lock if update failed
    $model->releaseSlot($date, $time, $_SESSION['user_id']);
    echo json_encode(['success' => false, 'error' => 'Failed to update appointment']);
}

$stmt->close();
$conn->close();
?>