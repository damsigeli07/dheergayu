<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';

if (empty($date) || empty($time)) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Check if slot is already locked or booked in 'consultations' table
$checkQuery = "SELECT id FROM consultations 
               WHERE appointment_date = ? 
               AND appointment_time = ?
               AND status != 'Cancelled'";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param('ss', $date, $time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Slot already taken']);
    $stmt->close();
    exit;
}

$stmt->close();
echo json_encode(['success' => true]);
$conn->close();
?>