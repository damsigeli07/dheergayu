<?php
// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set proper headers
header('Content-Type: application/json');

session_start();

try {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/AppointmentModel.php';
    
    $date = $_GET['date'] ?? '';

    if (!$date) {
        echo json_encode(['error' => 'Date required']);
        exit;
    }

    $model = new AppointmentModel($conn);
    $slots = $model->getAvailableSlots($date);

    echo json_encode(['slots' => $slots]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>