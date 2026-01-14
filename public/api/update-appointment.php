<?php
// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set proper headers
header('Content-Type: application/json');

session_start();

// Debug session data
error_log('Session data: ' . json_encode($_SESSION));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'patient') {
    echo json_encode(['success' => false, 'error' => 'Access denied - Patient role required']);
    exit;
}

try {
    require_once __DIR__ . '/../../config/config.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

try {
    $id = $_POST['id'] ?? null;
    $type = $_POST['type'] ?? null;
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;

    if (!$id || !$type || !$date || !$time) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $table = ($type === 'consultation') ? 'consultations' : 'consultations';

    // Get current appointment details
    $stmt = $conn->prepare("SELECT appointment_date, appointment_time, doctor_id FROM $table WHERE id = ? AND patient_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$current) {
        echo json_encode(['success' => false, 'error' => 'Appointment not found']);
        exit;
    }

    // If date/time hasn't changed, just allow it
    if ($current['appointment_date'] === $date && $current['appointment_time'] === $time) {
        echo json_encode(['success' => true]);
        exit;
    }

    // Check if new slot is available for the same doctor
    $checkQuery = "SELECT id FROM consultations 
                   WHERE appointment_date = ? 
                   AND appointment_time = ? 
                   AND doctor_id = ?
                   AND status != 'Cancelled'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('ssi', $date, $time, $current['doctor_id']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Selected slot is no longer available']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Update the appointment
    $stmt = $conn->prepare("UPDATE $table SET appointment_date = ?, appointment_time = ? WHERE id = ? AND patient_id = ?");
    $stmt->bind_param("ssii", $date, $time, $id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update appointment']);
    }

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>