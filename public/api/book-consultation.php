<?php
// public/api/book-consultation.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$logsDir = __DIR__ . '/../../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}
ini_set('error_log', $logsDir . '/php_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

file_put_contents($logsDir . '/api_calls.log',
    "\n========== " . date('Y-m-d H:i:s') . " ==========\n" .
    "Endpoint: book-consultation.php\n" .
    "Session User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n" .
    "POST Data: " . print_r($_POST, true),
    FILE_APPEND
);

ob_start();

try {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Not logged in', 'code' => 'AUTH_FAILED']);
        exit;
    }

    $configPath = __DIR__ . '/../../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Config file not found');
    }
    require_once $configPath;

    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection error: ' . ($conn->connect_error ?? 'unknown'));
    }

    // ── Extract & validate POST data ──────────────────────────────────────────
    $patient_id     = intval($_SESSION['user_id']);
    $doctor_id      = intval($_POST['doctor_id']      ?? 0);
    $doctor_name    = trim($_POST['doctor_name']      ?? '');
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $patient_name   = trim($_POST['patient_name']     ?? '');
    $email          = trim($_POST['email']            ?? '');
    $phone          = trim($_POST['phone']            ?? '');
    $age            = intval($_POST['age']            ?? 0);
    $gender         = trim($_POST['gender']           ?? '');
    $payment_method = trim($_POST['payment_method']   ?? 'onsite');

    $errors = [];
    if (!$doctor_id)              $errors[] = 'Doctor not selected';
    if (empty($doctor_name))      $errors[] = 'Doctor name is required';
    if (empty($appointment_date)) $errors[] = 'Appointment date is required';
    if (empty($appointment_time)) $errors[] = 'Time slot not selected';
    if (empty($patient_name))     $errors[] = 'Patient name is required';
    if (empty($email))            $errors[] = 'Email is required';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($phone))            $errors[] = 'Phone number is required';
    if ($age < 1 || $age > 150)  $errors[] = 'Please enter a valid age (1-150)';
    if (empty($gender) || $gender === 'Select') $errors[] = 'Please select a valid gender';

    if (!empty($errors)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => implode(', ', $errors), 'errors' => $errors]);
        exit;
    }

    // ── Generate patient_no from patient id (no extra column needed) ──────────
    // Format: P0003 based on the patient's id in the patients table.
    // This is consistent: same patient always gets the same number.
    $patient_no = 'P' . str_pad($patient_id, 4, '0', STR_PAD_LEFT);

    // ── Begin transaction ──────────────────────────────────────────────────────
    $conn->begin_transaction();

    try {
        // Day-level block guard (new table + legacy fallback).
        $dayBlocked = false;
        $dayBlockStmt = $conn->prepare("SELECT id FROM doctor_unavailable_days WHERE doctor_id = ? AND unavailable_date = ? LIMIT 1");
        if ($dayBlockStmt) {
            $dayBlockStmt->bind_param('is', $doctor_id, $appointment_date);
            $dayBlockStmt->execute();
            $dayBlocked = (bool)$dayBlockStmt->get_result()->fetch_assoc();
            $dayBlockStmt->close();
        }

        if (!$dayBlocked) {
            $dayBlockPattern = 'Doctor Cancelled: Doctor unavailable on ' . $appointment_date . '%';
            $dayBlockStmt = $conn->prepare("SELECT id FROM consultations WHERE doctor_id = ? AND appointment_date = ? AND status = 'Cancelled' AND notes LIKE ? LIMIT 1");
            if (!$dayBlockStmt) throw new Exception('DB prepare error: ' . $conn->error);
            $dayBlockStmt->bind_param('iss', $doctor_id, $appointment_date, $dayBlockPattern);
            $dayBlockStmt->execute();
            $dayBlocked = (bool)$dayBlockStmt->get_result()->fetch_assoc();
            $dayBlockStmt->close();
        }

        if ($dayBlocked) {
            throw new Exception('Doctor is unavailable on this date');
        }

        // Check slot availability for this doctor
        $checkQuery = "SELECT status FROM consultations
                       WHERE appointment_date = ?
                         AND appointment_time = ?
                         AND doctor_id        = ?
                         AND status NOT IN ('Cancelled')";
        $checkStmt = $conn->prepare($checkQuery);
        if (!$checkStmt) throw new Exception('DB prepare error: ' . $conn->error);

        $checkStmt->bind_param('ssi', $appointment_date, $appointment_time, $doctor_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (in_array($row['status'], ['Pending', 'Confirmed', 'locked'])) {
                throw new Exception('This slot is not available');
            }
        }
        $checkStmt->close();

        // Insert consultation
        $status         = 'Pending';
        $treatment_type = 'General Consultation';

        $insertQuery = "INSERT INTO consultations
                        (patient_id, doctor_id, doctor_name, patient_no, patient_name, age, gender,
                         email, phone, treatment_type, appointment_date, appointment_time,
                         status, payment_method, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($insertQuery);
        if (!$stmt) throw new Exception('DB prepare error: ' . $conn->error);

        // Correct types: i=int, s=string
        // patient_id(i), doctor_id(i), doctor_name(s), patient_no(s),
        // patient_name(s), age(i), gender(s), email(s), phone(s),
        // treatment_type(s), appointment_date(s), appointment_time(s),
        // status(s), payment_method(s)  → 14 params: iissssisssssss
        $stmt->bind_param(
            'iisssississsss',
            $patient_id,
            $doctor_id,
            $doctor_name,
            $patient_no,
            $patient_name,
            $age,
            $gender,
            $email,
            $phone,
            $treatment_type,
            $appointment_date,
            $appointment_time,
            $status,
            $payment_method
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to book consultation: ' . $stmt->error);
        }

        $appointment_id = $stmt->insert_id;
        $stmt->close();

        $conn->commit();

        ob_end_clean();
        echo json_encode([
            'success'        => true,
            'appointment_id' => $appointment_id,
            'patient_no'     => $patient_no,
            'doctor_name'    => $doctor_name,
            'message'        => 'Consultation booked successfully',
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    ob_end_clean();

    file_put_contents($logsDir . '/api_calls.log',
        "\nERROR: " . $e->getMessage() . "\n",
        FILE_APPEND
    );

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'type' => 'exception']);
}

exit;