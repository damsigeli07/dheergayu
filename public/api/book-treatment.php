<?php
// Disable ALL errors before JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Set headers FIRST
header('Content-Type: application/json');

// Start output buffering
ob_start();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }

    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/AppointmentModel.php';

    $model = new AppointmentModel($conn);

    // Get POST data
    $patient_id = $_SESSION['user_id'];
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $patient_name = trim($_POST['patient_name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'onsite');

    // Validate required fields
    if (!$doctor_id || !$appointment_date || !$appointment_time || !$patient_name) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    // Book consultation using model
    $appointment_id = $model->bookConsultation(
        $patient_id,
        $doctor_id,
        $appointment_date,
        $appointment_time,
        $patient_name,
        $email,
        $phone,
        $age,
        $gender,
        $payment_method
    );

    if ($appointment_id) {
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'appointment_id' => $appointment_id,
            'message' => 'Consultation booked successfully'
        ]);
    } else {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Failed to book consultation']);
    }

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

exit;
?>