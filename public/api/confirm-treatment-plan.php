<?php
// public/api/confirm-treatment-plan.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$plan_id = intval($_POST['plan_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$plan_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid plan ID']);
    exit;
}

try {
    if ($action === 'confirm') {
        // Verify the plan belongs to this patient
        $stmt = $conn->prepare("
            SELECT patient_id, status 
            FROM treatment_plans 
            WHERE plan_id = ?
        ");
        $stmt->bind_param('i', $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $plan = $result->fetch_assoc();
        $stmt->close();
        
        if (!$plan) {
            echo json_encode(['success' => false, 'message' => 'Treatment plan not found']);
            exit;
        }
        
        if ($plan['patient_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        // Update plan status to Confirmed
        $stmt = $conn->prepare("
            UPDATE treatment_plans 
            SET status = 'Confirmed', 
                confirmed_at = NOW() 
            WHERE plan_id = ?
        ");
        $stmt->bind_param('i', $plan_id);
        
        if ($stmt->execute()) {
            // Also update all sessions to Confirmed
            $session_stmt = $conn->prepare("
                UPDATE treatment_sessions 
                SET status = 'Confirmed' 
                WHERE plan_id = ? AND status = 'Pending'
            ");
            $session_stmt->bind_param('i', $plan_id);
            $session_stmt->execute();
            $session_stmt->close();
            
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Treatment plan confirmed successfully']);
        } else {
            throw new Exception('Failed to confirm plan: ' . $stmt->error);
        }
        
    } elseif ($action === 'request_change') {
        $reason = trim($_POST['reason'] ?? '');
        
        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for changes']);
            exit;
        }
        
        // Verify ownership
        $stmt = $conn->prepare("SELECT patient_id FROM treatment_plans WHERE plan_id = ?");
        $stmt->bind_param('i', $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $plan = $result->fetch_assoc();
        $stmt->close();
        
        if (!$plan || $plan['patient_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        // Update plan with change request
        $stmt = $conn->prepare("
            UPDATE treatment_plans 
            SET status = 'ChangeRequested',
                change_requested = 1,
                change_reason = ?
            WHERE plan_id = ?
        ");
        $stmt->bind_param('si', $reason, $plan_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // TODO: Send notification to doctor
            // You can add email/SMS notification here
            
            echo json_encode(['success' => true, 'message' => 'Change request sent to doctor']);
        } else {
            throw new Exception('Failed to submit change request: ' . $stmt->error);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Treatment plan confirmation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}