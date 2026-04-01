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
        
        $therapist_name = trim($_POST['therapist_name'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        if (empty($notes)) {
            throw new Exception("Notes are required");
        }
        
        // Check if form already exists
        $check_stmt = $db->prepare("SELECT id FROM staff_treatment_forms WHERE plan_id = ? AND staff_id = ? LIMIT 1");
        $check_stmt->bind_param('ii', $plan_id, $staff_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($existing) {
            // Update existing form - in view mode, only update notes
            if ($view_mode) {
                $update_stmt = $db->prepare("
                    UPDATE staff_treatment_forms 
                    SET notes = ?, updated_at = NOW()
                    WHERE plan_id = ? AND staff_id = ?
                ");
                $update_stmt->bind_param('sii', $notes, $plan_id, $staff_id);
            } else {
                if (empty($therapist_name)) {
                    throw new Exception("Therapist name is required");
                }
                $update_stmt = $db->prepare("
                    UPDATE staff_treatment_forms 
                    SET therapist_name = ?, notes = ?, updated_at = NOW()
                    WHERE plan_id = ? AND staff_id = ?
                ");
                $update_stmt->bind_param('ssii', $therapist_name, $notes, $plan_id, $staff_id);
            }
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update treatment form: " . $update_stmt->error);
            }
            
            $update_stmt->close();
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
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $insert_stmt->bind_param('iiss', $plan_id, $staff_id, $therapist_name, $notes);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to save treatment form: " . $insert_stmt->error);
            }
            
            $insert_stmt->close();
            
            // Create a treatment session record to update progress
            // Get the treatment plan to find total sessions and get next session number
            $plan_stmt = $db->prepare("
                SELECT total_sessions, start_date, sessions_per_week 
                FROM treatment_plans 
                WHERE plan_id = ?
            ");
            $plan_stmt->bind_param('i', $plan_id);
            $plan_stmt->execute();
            $plan_result = $plan_stmt->get_result();
            $plan_data = $plan_result->fetch_assoc();
            $plan_stmt->close();
            
            if ($plan_data) {
                // Get the highest session number for this plan
                $max_session_stmt = $db->prepare("
                    SELECT COALESCE(MAX(session_number), 0) as max_session 
                    FROM treatment_sessions 
                    WHERE plan_id = ?
                ");
                $max_session_stmt->bind_param('i', $plan_id);
                $max_session_stmt->execute();
                $max_session_result = $max_session_stmt->get_result();
                $max_session_data = $max_session_result->fetch_assoc();
                $next_session_number = ($max_session_data['max_session'] ?? 0) + 1;
                $max_session_stmt->close();
                
                // Use treatment plan start_date if available, otherwise use current date
                $session_date = !empty($plan_data['start_date']) ? $plan_data['start_date'] : date('Y-m-d');
                
                // Get session time from existing sessions or use default
                $time_stmt = $db->prepare("
                    SELECT session_time 
                    FROM treatment_sessions 
                    WHERE plan_id = ? 
                    ORDER BY session_number ASC 
                    LIMIT 1
                ");
                $time_stmt->bind_param('i', $plan_id);
                $time_stmt->execute();
                $time_result = $time_stmt->get_result();
                $time_data = $time_result->fetch_assoc();
                $session_time = $time_data['session_time'] ?? '09:00:00';
                $time_stmt->close();
                
                // Insert treatment session with Completed status
                $session_stmt = $db->prepare("
                    INSERT INTO treatment_sessions (
                        plan_id, session_number, session_date, session_time,
                        status, created_at
                    ) VALUES (?, ?, ?, ?, 'Completed', NOW())
                ");
                
                if ($session_stmt) {
                    $session_stmt->bind_param('iiss', $plan_id, $next_session_number, $session_date, $session_time);
                    
                    if (!$session_stmt->execute()) {
                        error_log("Failed to create treatment session: " . $session_stmt->error);
                        // Don't throw error, just log it - form is saved successfully
                    } else {
                        error_log("Treatment session created: plan_id=$plan_id, session_number=$next_session_number, status=Completed");
                    }
                    $session_stmt->close();
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
