<?php
// public/api/get-doctor-report.php
header('Content-Type: application/json');
session_start();

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/DoctorModel.php';

$doctor_id = $_SESSION['user_id'];

try {
    $doctorModel = new DoctorModel($conn);
    $statistics = $doctorModel->getDoctorReportStatistics($doctor_id);
    
    echo json_encode([
        'success' => true,
        'data' => $statistics
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching report data: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
