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
        // Verify the plan belongs to this patient and get treatment_id
        $stmt = $conn->prepare("
            SELECT patient_id, status, treatment_id 
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
            $stmt->close();
            // Also update all sessions to Confirmed
            $session_stmt = $conn->prepare("
                UPDATE treatment_sessions 
                SET status = 'Confirmed' 
                WHERE plan_id = ? AND status = 'Pending'
            ");
            $session_stmt->bind_param('i', $plan_id);
            $session_stmt->execute();
            $session_stmt->close();
            
            // Offer this plan to the staff assigned to this treatment (treatment_staff)
            $treatment_id = (int)($plan['treatment_id'] ?? 0);
            if ($treatment_id > 0) {
                $conn->query("CREATE TABLE IF NOT EXISTS treatment_plan_staff_offer (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    plan_id INT NOT NULL,
                    treatment_id INT NOT NULL,
                    primary_staff1_id INT NOT NULL,
                    primary_staff2_id INT NOT NULL,
                    backup_staff_id INT NOT NULL,
                    assigned_staff_id INT NULL,
                    primary1_declined TINYINT(1) NOT NULL DEFAULT 0,
                    primary2_declined TINYINT(1) NOT NULL DEFAULT 0,
                    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    confirmed_at TIMESTAMP NULL,
                    UNIQUE KEY one_offer_per_plan (plan_id),
                    INDEX idx_offer_staff (primary_staff1_id, primary_staff2_id, backup_staff_id),
                    INDEX idx_offer_status (status)
                )");
                $ts = $conn->prepare("SELECT primary_staff1_id, primary_staff2_id, backup_staff_id FROM treatment_staff WHERE treatment_id = ? LIMIT 1");
                $ts->bind_param('i', $treatment_id);
                $ts->execute();
                $ts_row = $ts->get_result()->fetch_assoc();
                $ts->close();
                if ($ts_row) {
                    $ins = $conn->prepare("INSERT INTO treatment_plan_staff_offer (plan_id, treatment_id, primary_staff1_id, primary_staff2_id, backup_staff_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE plan_id = plan_id");
                    $ins->bind_param('iiiii', $plan_id, $treatment_id, $ts_row['primary_staff1_id'], $ts_row['primary_staff2_id'], $ts_row['backup_staff_id']);
                    $ins->execute();
                    $ins->close();
                }
            }
            
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