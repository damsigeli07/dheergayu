<?php
// public/api/update-appointment.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$id = $_POST['id'] ?? null;
$type = $_POST['type'] ?? null;
$date = $_POST['date'] ?? null;
$time = $_POST['time'] ?? null;

if (!$id || !$type || !$date || !$time) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$table = ($type === 'consultation') ? 'consultations' : 'treatments';

$stmt = $conn->prepare("UPDATE $table SET appointment_date = ?, appointment_time = ? WHERE id = ?");
$stmt->bind_param("ssi", $date, $time, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update appointment']);
}

$stmt->close();
$conn->close();
?>