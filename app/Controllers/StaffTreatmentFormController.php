<?php
// app/Controllers/StaffTreatmentFormController.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Check if user is staff
$user_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');
if ($user_role !== 'staff') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

// Database connection
function getDbConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'dheergayu_db';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Database connection failed");
    }
    
    return $conn;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDbConnection();
        $db->begin_transaction();
        
        // Get and validate form data
        $plan_id = intval($_POST['plan_id'] ?? 0);
        $staff_id = intval($_SESSION['user_id'] ?? 0);
        $view_mode = isset($_POST['view_mode']) && $_POST['view_mode'] == '1';
        
        if ($plan_id === 0 || $staff_id === 0) {
            throw new Exception("Invalid plan ID or staff ID");
        }

        $gate = $db->prepare("SELECT payment_status, status, change_requested, assigned_staff_id FROM treatment_plans WHERE plan_id = ? LIMIT 1");
        $gate->bind_param('i', $plan_id);
        $gate->execute();
        $gRow = $gate->get_result()->fetch_assoc();
        $gate->close();
        if (!$gRow) {
            throw new Exception("Treatment plan not found");
        }

        $gAssignedId = (int)($gRow['assigned_staff_id'] ?? 0);
        if ($gAssignedId === 0 || $gAssignedId !== $staff_id) {
            throw new Exception("You are not assigned to this treatment plan.");
        }
        $gPay = ($gRow['payment_status'] ?? '') === 'Completed';
        $gSt = $gRow['status'] ?? '';
        $gChg = !empty($gRow['change_requested']);
        $gOk = $gPay && !$gChg && in_array($gSt, ['Confirmed', 'InProgress'], true);
        if (!$gOk) {
            throw new Exception("Treatment cannot be saved until the patient has paid and confirmed the plan.");
        }
        
        $therapist_name          = trim($_POST['therapist_name'] ?? '');
        $session_notes_input     = $_POST['session_notes'] ?? [];
        $session_completed_input = $_POST['session_completed'] ?? [];

        // Ensure per-session notes table exists
        $db->query("CREATE TABLE IF NOT EXISTS staff_treatment_session_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plan_id INT NOT NULL,
            staff_id INT NOT NULL,
            session_number INT NOT NULL,
            session_note LONGTEXT,
            is_completed TINYINT(1) DEFAULT 0,
            completed_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_plan_staff_session (plan_id, staff_id, session_number)
        )");
        
        // Check if form already exists
        $check_stmt = $db->prepare("SELECT id FROM staff_treatment_forms WHERE plan_id = ? AND staff_id = ? LIMIT 1");
        $check_stmt->bind_param('ii', $plan_id, $staff_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($existing) {
            // Update existing form - in view mode, only update notes
            // Always allow updating therapist name and session notes regardless of view mode
$update_stmt = $db->prepare("
    UPDATE staff_treatment_forms 
    SET therapist_name = ?, updated_at = NOW()
    WHERE plan_id = ? AND staff_id = ?
");
$update_stmt->bind_param('sii', $therapist_name, $plan_id, $staff_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update treatment form: " . $update_stmt->error);
            }

            $update_stmt->close();

            // ── Save/update session notes on update too ─────────────────
            foreach ($session_notes_input as $session_num => $note) {
                $session_num  = intval($session_num);
                $note         = trim($note);
                $is_completed = isset($session_completed_input[$session_num]) ? 1 : 0;
                $completed_at = $is_completed ? date('Y-m-d H:i:s') : null;

                $sn_stmt = $db->prepare("
                    INSERT INTO staff_treatment_session_notes 
                        (plan_id, staff_id, session_number, session_note, is_completed, completed_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        session_note = VALUES(session_note),
                        is_completed = VALUES(is_completed),
                        completed_at = VALUES(completed_at),
                        updated_at   = NOW()
                ");
                $sn_stmt->bind_param('iiisis', $plan_id, $staff_id, $session_num, $note, $is_completed, $completed_at);
                $sn_stmt->execute();
                $sn_stmt->close();

                // Update existing planned session only — never overwrite AwaitingPayment
                $session_status = $is_completed ? 'Completed' : 'Pending';
                $ts_stmt = $db->prepare("
                    UPDATE treatment_sessions
                    SET status = ?
                    WHERE plan_id = ? AND session_number = ?
                    AND status != 'AwaitingPayment'
                    LIMIT 1
                ");
                if ($ts_stmt) {
                    $ts_stmt->bind_param('sii', $session_status, $plan_id, $session_num);
                    $ts_stmt->execute();
                    $ts_stmt->close();
                }
            }
            // ── End session notes ───────────────────────────────────────

            // If all sessions are completed, mark plan as Completed.
            $cnt = $db->prepare("SELECT COUNT(*) AS done FROM treatment_sessions WHERE plan_id = ? AND status = 'Completed'");
            $cnt->bind_param('i', $plan_id);
            $cnt->execute();
            $doneRow = $cnt->get_result()->fetch_assoc();
            $cnt->close();
            $done = (int)($doneRow['done'] ?? 0);
            if ($done > 0) {
                $tot = $db->prepare("SELECT COUNT(*) AS total FROM treatment_sessions WHERE plan_id = ?");
                $tot->bind_param('i', $plan_id);
                $tot->execute();
                $totRow = $tot->get_result()->fetch_assoc();
                $tot->close();
                $totalSessions = (int)($totRow['total'] ?? 0);
                if ($totalSessions > 0 && $done >= $totalSessions) {
                    $db->query("UPDATE treatment_plans SET status = 'Completed' WHERE plan_id = " . (int)$plan_id);
                } elseif ($done > 0) {
                    // Ensure at least InProgress once staff starts completing sessions
                    $db->query("UPDATE treatment_plans SET status = 'InProgress' WHERE plan_id = " . (int)$plan_id . " AND status != 'Completed'");
                }
            }

            $db->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Treatment form updated successfully'
            ]);

        } else {
            // Insert new form
            if (empty($therapist_name)) {
                throw new Exception("Therapist name is required");
            }
            $insert_stmt = $db->prepare("
               INSERT INTO staff_treatment_forms (plan_id, staff_id, therapist_name, notes, created_at, updated_at)
               VALUES (?, ?, ?, '', NOW(), NOW())
            ");
            $insert_stmt->bind_param('iis', $plan_id, $staff_id, $therapist_name);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to save treatment form: " . $insert_stmt->error);
            }
            
            $insert_stmt->close();

foreach ($session_notes_input as $session_num => $note) {
    $session_num   = intval($session_num);
    $note          = trim($note);
    $is_completed  = isset($session_completed_input[$session_num]) ? 1 : 0;
    $completed_at  = $is_completed ? date('Y-m-d H:i:s') : null;

    $sn_stmt = $db->prepare("
        INSERT INTO staff_treatment_session_notes 
            (plan_id, staff_id, session_number, session_note, is_completed, completed_at)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            session_note = VALUES(session_note),
            is_completed = VALUES(is_completed),
            completed_at = VALUES(completed_at),
            updated_at   = NOW()
    ");
    $sn_stmt->bind_param('iiisis', $plan_id, $staff_id, $session_num, $note, $is_completed, $completed_at);
    $sn_stmt->execute();
    $sn_stmt->close();
    
    // Update existing planned session only — never overwrite AwaitingPayment
    $session_status = $is_completed ? 'Completed' : 'Pending';
    $ts_stmt = $db->prepare("
        UPDATE treatment_sessions
        SET status = ?
        WHERE plan_id = ? AND session_number = ?
        AND status != 'AwaitingPayment'
        LIMIT 1
    ");
    if ($ts_stmt) {
        $ts_stmt->bind_param('sii', $session_status, $plan_id, $session_num);
        $ts_stmt->execute();
        $ts_stmt->close();
    }
}

            // If all sessions are completed, mark plan as Completed.
            $cnt = $db->prepare("SELECT COUNT(*) AS done FROM treatment_sessions WHERE plan_id = ? AND status = 'Completed'");
            $cnt->bind_param('i', $plan_id);
            $cnt->execute();
            $doneRow = $cnt->get_result()->fetch_assoc();
            $cnt->close();
            $done = (int)($doneRow['done'] ?? 0);
            if ($done > 0) {
                $tot = $db->prepare("SELECT COUNT(*) AS total FROM treatment_sessions WHERE plan_id = ?");
                $tot->bind_param('i', $plan_id);
                $tot->execute();
                $totRow = $tot->get_result()->fetch_assoc();
                $tot->close();
                $totalSessions = (int)($totRow['total'] ?? 0);
                if ($totalSessions > 0 && $done >= $totalSessions) {
                    $db->query("UPDATE treatment_plans SET status = 'Completed' WHERE plan_id = " . (int)$plan_id);
                } elseif ($done > 0) {
                    $db->query("UPDATE treatment_plans SET status = 'InProgress' WHERE plan_id = " . (int)$plan_id . " AND status != 'Completed'");
                }
            }

            $db->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Treatment form saved successfully'
            ]);
        }
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        error_log("StaffTreatmentFormController Error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
