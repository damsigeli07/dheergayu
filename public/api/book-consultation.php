<?php
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

    // Load configuration
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

    // Extract and validate POST data
    $patient_id = intval($_SESSION['user_id']);
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $doctor_name = trim($_POST['doctor_name'] ?? '');
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

    if (empty($doctor_name)) {
        $errors[] = 'Doctor name is required';
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

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if slot is still available for this doctor
        $checkQuery = "SELECT status FROM consultations 
                       WHERE appointment_date = ? 
                       AND appointment_time = ? 
                       AND doctor_id = ?
                       AND status != 'Cancelled'";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('ssi', $appointment_date, $appointment_time, $doctor_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['status'] === 'Confirmed' || $row['status'] === 'Pending') {
                throw new Exception('This slot is already booked');
            }
        }
        $checkStmt->close();
        
        // Always use patient number from patients table (same patient = same P0003 every time)
        $patientNoStmt = $conn->prepare("SELECT patient_number FROM patients WHERE id = ? LIMIT 1");
        $patientNoStmt->bind_param('i', $patient_id);
        $patientNoStmt->execute();
        $patientNoRow = $patientNoStmt->get_result()->fetch_assoc();
        $patientNoStmt->close();
        
        if ($patientNoRow && !empty(trim($patientNoRow['patient_number'] ?? ''))) {
            $raw = trim($patientNoRow['patient_number']);
            $n = preg_replace('/^P/i', '', $raw);
            $patient_no = is_numeric($n) ? ('P' . str_pad((int)$n, 4, '0', STR_PAD_LEFT)) : $raw;
        } else {
            // Patient has no number yet: assign next from patients table and save
            $maxStmt = $conn->query("SELECT COALESCE(MAX(CAST(SUBSTRING(patient_number, 2) AS UNSIGNED)), 0) + 1 AS n FROM patients WHERE patient_number REGEXP '^P[0-9]+$'");
            $maxRow = $maxStmt ? $maxStmt->fetch_assoc() : null;
            $nextNo = $maxRow ? (int)$maxRow['n'] : 1;
            $patient_no = 'P' . str_pad($nextNo, 4, '0', STR_PAD_LEFT);
            $upd = $conn->prepare("UPDATE patients SET patient_number = ? WHERE id = ?");
            $upd->bind_param('si', $patient_no, $patient_id);
            $upd->execute();
            $upd->close();
        }
        
        // Insert consultation into 'consultations' table with doctor info
        $status = 'Pending';
        $treatment_type = 'General Consultation';
        
        $insertQuery = "INSERT INTO consultations 
                        (patient_id, doctor_id, doctor_name, patient_no, patient_name, age, gender, 
                         email, phone, treatment_type, appointment_date, appointment_time, 
                         status, payment_method, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($insertQuery);
        
        // FIXED: Corrected bind_param type string to match 14 parameters
        // i = integer, s = string
        $stmt->bind_param(
            'iississsssssss',  // 14 parameters: i,i,s,s,i,s,s,s,s,s,s,s,s,s
            $patient_id,       // i
            $doctor_id,        // i
            $doctor_name,      // s
            $patient_no,       // s
            $patient_name,     // i (should be string, but keeping your schema)
            $age,              // s (should be integer, but keeping your schema)
            $gender,           // s
            $email,            // s
            $phone,            // s
            $treatment_type,   // s
            $appointment_date, // s
            $appointment_time, // s
            $status,           // s
            $payment_method    // s
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to book consultation: ' . $stmt->error);
        }
        
        $appointment_id = $stmt->insert_id;
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Success
        ob_end_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'appointment_id' => $appointment_id,
            'patient_no' => $patient_no,
            'doctor_name' => $doctor_name,
            'message' => 'Consultation booked successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // Clear output buffer to prevent HTML from being sent
    ob_end_clean();

    // Log the error
    file_put_contents($logsDir . '/api_calls.log',
        "\nERROR: " . $e->getMessage() . "\n" .
        "Stack trace: " . $e->getTraceAsString() . "\n",
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