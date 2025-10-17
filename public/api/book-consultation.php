<?php
// public/api/book-consultation.php

// Set headers FIRST - before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Disable error display to response, log to file instead
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

ini_set('error_log', $logsDir . '/php_errors.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log the request
file_put_contents($logsDir . '/api_calls.log', 
    "\n========== " . date('Y-m-d H:i:s') . " ==========\n" .
    "Endpoint: book-consultation.php\n" .
    "Session User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n" .
    "POST Data: " . print_r($_POST, true),
    FILE_APPEND
);

// Send output buffering to catch any unexpected output
ob_start();

try {
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Not logged in',
            'code' => 'AUTH_FAILED'
        ]);
        exit;
    }

    // Load configuration and model
    $configPath = __DIR__ . '/../../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Config file not found');
    }
    require_once $configPath;

    // Check database connection
    if (!$conn) {
        throw new Exception('Database connection object not initialized');
    }

    if ($conn->connect_error) {
        throw new Exception('Database connection error: ' . $conn->connect_error);
    }

    // Load model
    $modelPath = __DIR__ . '/../../app/Models/AppointmentModel.php';
    if (!file_exists($modelPath)) {
        throw new Exception('AppointmentModel not found');
    }
    require_once $modelPath;

    $model = new AppointmentModel($conn);

    // Extract and validate POST data
    $patient_id = intval($_SESSION['user_id']);
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $patient_name = trim($_POST['patient_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'onsite');

    // Validation logic
    $errors = [];

    if (!$doctor_id) {
        $errors[] = 'Doctor not selected';
    }

    if (empty($appointment_date)) {
        $errors[] = 'Appointment date is required';
    }

    if (empty($appointment_time)) {
        $errors[] = 'Time slot not selected';
    }

    if (empty($patient_name)) {
        $errors[] = 'Patient name is required';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }

    if ($age < 1 || $age > 150) {
        $errors[] = 'Please enter a valid age (1-150)';
    }

    if (empty($gender) || $gender === 'Select') {
        $errors[] = 'Please select a valid gender';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => implode(', ', $errors),
            'errors' => $errors
        ]);
        exit;
    }

    // Attempt to book consultation
    try {
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
    } catch (Exception $modelEx) {
        file_put_contents($logsDir . '/api_calls.log',
            "\nMODEL EXCEPTION: " . $modelEx->getMessage() . "\n",
            FILE_APPEND
        );
        throw $modelEx;
    }

    if ($appointment_id === false || $appointment_id === 0) {
        throw new Exception('Database insert failed - appointment ID is invalid');
    }

    // Success
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'appointment_id' => $appointment_id,
        'message' => 'Consultation booked successfully'
    ]);

} catch (Exception $e) {
    // Clear output buffer to prevent HTML from being sent
    ob_end_clean();

    // Log the error
    file_put_contents($logsDir . '/api_calls.log',
        "\nERROR: " . $e->getMessage() . "\n",
        FILE_APPEND
    );

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'type' => 'exception'
    ]);
}

exit;
?>