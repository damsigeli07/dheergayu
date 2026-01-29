<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';

if (empty($date) || empty($time)) {
    echo json_encode(['success' => false]);
    exit;
}

// Release locked slot in 'consultations' table (if status is 'locked')
$query = "DELETE FROM consultations 
          WHERE appointment_date = ? 
          AND appointment_time = ? 
          AND status = 'locked'";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $date, $time);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
$conn->close();
?>