<?php
// public/api/reschedule-treatment-sessions.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$plan_id = intval($input['plan_id'] ?? 0);
$new_schedule = $input['new_schedule'] ?? [];

if (!$plan_id || empty($new_schedule)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Update each session
    foreach ($new_schedule as $item) {
        $session_id = intval($item['session_id']);
        $new_date = $item['new_date'];
        $new_time = $item['new_time'];
        
        $stmt = $conn->prepare("
            UPDATE treatment_sessions 
            SET session_date = ?, 
                session_time = ?,
                updated_at = NOW()
            WHERE session_id = ? AND plan_id = ?
        ");
        $stmt->bind_param('ssii', $new_date, $new_time, $session_id, $plan_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update session ' . $session_id);
        }
        $stmt->close();
    }
    
    // Update plan status to indicate it needs patient confirmation
    $stmt = $conn->prepare("
        UPDATE treatment_plans 
        SET status = 'Pending',
            change_requested = 0,
            change_reason = NULL,
            updated_at = NOW()
        WHERE plan_id = ?
    ");
    $stmt->bind_param('i', $plan_id);
    $stmt->execute();
    $stmt->close();
    
    // TODO: Send notification to patient (SMS/Email)
    // You can add notification logic here
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Schedule updated successfully',
        'updated_sessions' => count($new_schedule)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Reschedule error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}