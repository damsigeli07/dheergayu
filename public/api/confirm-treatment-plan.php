<?php
// public/api/confirm-treatment-plan.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

function ensureTreatmentPlanStaffOffer(mysqli $conn, int $plan_id, int $treatment_id): void {
    if ($plan_id <= 0 || $treatment_id <= 0) {
        return;
    }

    $conn->query("CREATE TABLE IF NOT EXISTS treatment_plan_staff_offer (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        plan_id INT NOT NULL,
        treatment_id INT NOT NULL,
        primary_staff1_id INT NOT NULL,
        primary_staff2_id INT NOT NULL,
        assigned_staff_id INT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        confirmed_at TIMESTAMP NULL,
        UNIQUE KEY one_offer_per_plan (plan_id),
        INDEX idx_offer_staff (primary_staff1_id, primary_staff2_id),
        INDEX idx_offer_status (status)
    )");

    $ts = $conn->prepare("SELECT primary_staff1_id, primary_staff2_id FROM treatment_staff WHERE treatment_id = ? LIMIT 1");
    if (!$ts) {
        return;
    }

    $ts->bind_param('i', $treatment_id);
    $ts->execute();
    $ts_row = $ts->get_result()->fetch_assoc();
    $ts->close();

    if (!$ts_row) {
        return;
    }

    $ins = $conn->prepare("INSERT INTO treatment_plan_staff_offer (plan_id, treatment_id, primary_staff1_id, primary_staff2_id) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE treatment_id = VALUES(treatment_id), primary_staff1_id = VALUES(primary_staff1_id), primary_staff2_id = VALUES(primary_staff2_id)");
    if (!$ins) {
        return;
    }

    $ins->bind_param('iiii', $plan_id, $treatment_id, $ts_row['primary_staff1_id'], $ts_row['primary_staff2_id']);
    $ins->execute();
    $ins->close();
}

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
            
            // Ensure this plan is linked to current treatment staff mapping.
            ensureTreatmentPlanStaffOffer($conn, $plan_id, (int)($plan['treatment_id'] ?? 0));
            
            echo json_encode(['success' => true, 'message' => 'Treatment plan confirmed successfully']);
        } else {
            throw new Exception('Failed to confirm plan: ' . $stmt->error);
        }
        
    } elseif ($action === 'update_schedule') {
        $scheduleJson = trim((string)($_POST['schedule'] ?? ''));
        $note = trim((string)($_POST['note'] ?? ''));

        if ($scheduleJson === '') {
            echo json_encode(['success' => false, 'message' => 'Schedule data is required']);
            exit;
        }

        $schedule = json_decode($scheduleJson, true);
        if (!is_array($schedule) || empty($schedule)) {
            echo json_encode(['success' => false, 'message' => 'Invalid schedule format']);
            exit;
        }

        $stmt = $conn->prepare("SELECT patient_id, treatment_id FROM treatment_plans WHERE plan_id = ?");
        $stmt->bind_param('i', $plan_id);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$plan || (int)$plan['patient_id'] !== (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }

        $treatment_id = (int)($plan['treatment_id'] ?? 0);
        if ($treatment_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid treatment plan']);
            exit;
        }

        $slotStmt = $conn->prepare("SELECT slot_id FROM treatment_slots WHERE treatment_id = ? AND slot_time = ? LIMIT 1");
        $bookingCheckStmt = $conn->prepare("SELECT booking_id FROM treatment_bookings WHERE treatment_id = ? AND booking_date = ? AND slot_id = ? AND status NOT IN ('Cancelled') LIMIT 1");
        $updateSessionStmt = $conn->prepare("UPDATE treatment_sessions SET session_date = ?, session_time = ? WHERE plan_id = ? AND session_number = ?");

        if (!$slotStmt || !$bookingCheckStmt || !$updateSessionStmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare schedule update']);
            exit;
        }

        $conn->begin_transaction();
        try {
            foreach ($schedule as $row) {
                $sessionNo = (int)($row['session_number'] ?? 0);
                $sessionDate = trim((string)($row['session_date'] ?? ''));
                $sessionTime = trim((string)($row['session_time'] ?? ''));

                if ($sessionNo <= 0 || $sessionDate === '' || $sessionTime === '') {
                    throw new Exception('Each session requires number, date, and time');
                }

                $slotStmt->bind_param('is', $treatment_id, $sessionTime);
                $slotStmt->execute();
                $slot = $slotStmt->get_result()->fetch_assoc();
                if (!$slot) {
                    throw new Exception('Invalid slot selected for Session ' . $sessionNo);
                }

                $slotId = (int)$slot['slot_id'];
                $bookingCheckStmt->bind_param('isi', $treatment_id, $sessionDate, $slotId);
                $bookingCheckStmt->execute();
                $conflict = $bookingCheckStmt->get_result()->fetch_assoc();
                if ($conflict) {
                    throw new Exception('Selected slot is already booked for Session ' . $sessionNo);
                }

                $updateSessionStmt->bind_param('ssii', $sessionDate, $sessionTime, $plan_id, $sessionNo);
                $updateSessionStmt->execute();
            }

            if ($note !== '') {
                $reason = 'Updated by patient before payment. ' . $note;
                $reasonStmt = $conn->prepare("UPDATE treatment_plans SET change_requested = 1, change_reason = ? WHERE plan_id = ?");
                if ($reasonStmt) {
                    $reasonStmt->bind_param('si', $reason, $plan_id);
                    $reasonStmt->execute();
                    $reasonStmt->close();
                }
            }

            // Update start_date to the earliest rescheduled session date
            $newStartDates = array_column($schedule, 'session_date');
            sort($newStartDates);
            $newStartDate = $newStartDates[0] ?? null;
            if ($newStartDate) {
                $conn->query("UPDATE treatment_plans SET start_date = '" . $conn->real_escape_string($newStartDate) . "' WHERE plan_id = " . $plan_id);
            }

            ensureTreatmentPlanStaffOffer($conn, $plan_id, $treatment_id);
            $conn->commit();

            $slotStmt->close();
            $bookingCheckStmt->close();
            $updateSessionStmt->close();

            echo json_encode(['success' => true, 'message' => 'Schedule updated successfully']);
        } catch (Exception $ex) {
            $conn->rollback();
            $slotStmt->close();
            $bookingCheckStmt->close();
            $updateSessionStmt->close();
            throw $ex;
        }

    } elseif ($action === 'request_change') {
        $reason = trim($_POST['reason'] ?? '');
        
        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for changes']);
            exit;
        }
        
        // Verify ownership
        $stmt = $conn->prepare("SELECT patient_id, treatment_id FROM treatment_plans WHERE plan_id = ?");
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
            
            // Keep offer synced so that once payment is completed staff can see this plan.
            ensureTreatmentPlanStaffOffer($conn, $plan_id, (int)($plan['treatment_id'] ?? 0));
            
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