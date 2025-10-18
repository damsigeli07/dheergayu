<?php
// public/api/book-treatment.php

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

$patient_id = $_SESSION['user_id'];
$treatment_type = $_POST['treatment_type'] ?? '';
$appointment_date = $_POST['appointment_date'] ?? '';
$appointment_time = $_POST['appointment_time'] ?? '';
$patient_name = $_POST['patient_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$age = intval($_POST['age'] ?? 0);
$gender = $_POST['gender'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'onsite';

if (!$treatment_type || !$appointment_date || !$appointment_time || !$patient_name) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$appointment_id = $model->bookTreatment(
    $patient_id, $treatment_type, $appointment_date, $appointment_time,
    $patient_name, $email, $phone, $age, $gender, $payment_method
);

if ($appointment_id) {
    echo json_encode(['success' => true, 'appointment_id' => $appointment_id]);
} else {
    echo json_encode(['error' => 'Failed to book treatment']);
}
?>