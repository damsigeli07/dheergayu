<?php
// public/api/send-treatment-plan.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    $schedule_data = json_decode($_POST['schedule_data'], true);
    $appointment_id = intval($_POST['appointment_id']);
    $patient_id = intval($_POST['patient_id']);
    
    if (!$schedule_data || !$appointment_id || !$patient_id) {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
        exit;
    }
    
    // Get treatment ID from treatment_list
    $treatment_name = $schedule_data['treatmentType'];
    $stmt = $conn->prepare("SELECT treatment_id, price FROM treatment_list WHERE treatment_name = ?");
    $stmt->bind_param('s', $treatment_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $treatment = $result->fetch_assoc();
    $stmt->close();
    
    if (!$treatment) {
        echo json_encode(['success' => false, 'error' => 'Treatment not found']);
        exit;
    }
    
    $treatment_id = $treatment['treatment_id'];
    $session_cost = $treatment['price'];
    $total_sessions = intval($schedule_data['sessions']);
    $total_cost = $session_cost * $total_sessions;
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Insert treatment plan (matching your existing table structure)
    $stmt = $conn->prepare("
        INSERT INTO treatment_plans 
        (patient_id, appointment_id, treatment_id, diagnosis, 
         total_sessions, sessions_per_week, start_date, total_cost, status, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending')
    ");
    
    $stmt->bind_param(
        'iiisisd',
        $patient_id,
        $appointment_id,
        $treatment_id,
        $schedule_data['diagnosis'],
        $total_sessions,
        $schedule_data['sessionsPerWeek'],
        $schedule_data['startDate'],
        $total_cost
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create treatment plan: ' . $stmt->error);
    }
    
    $plan_id = $conn->insert_id;
    $stmt->close();
    
    // Insert treatment sessions
    $session_stmt = $conn->prepare("
        INSERT INTO treatment_sessions 
        (plan_id, session_number, session_date, session_time, status)
        VALUES (?, ?, ?, ?, 'Pending')
    ");
    
    foreach ($schedule_data['schedule'] as $index => $session) {
        $session_number = $index + 1;
        $session_stmt->bind_param(
            'iiss',
            $plan_id,
            $session_number,
            $session['date'],
            $session['time']
        );
        
        if (!$session_stmt->execute()) {
            throw new Exception('Failed to create session ' . $session_number . ': ' . $session_stmt->error);
        }
    }
    $session_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Treatment plan sent to patient successfully',
        'plan_id' => $plan_id
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log("Send treatment plan error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>