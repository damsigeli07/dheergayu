<?php
// app/Controllers/ConsultationFormController.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection - use direct mysqli instead of bootloader
function getDbConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'dheergayu_db'; // Make sure this matches your actual database name
    
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
        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        $patient_id = intval($_POST['patient_id'] ?? 0);
        
        if ($appointment_id === 0) {
            throw new Exception("Invalid appointment ID");
        }
        
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $age = intval($_POST['age'] ?? 0);
        $gender = trim($_POST['gender'] ?? '');
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $personal_products = $_POST['personal_products'] ?? '[]';
        $notes = trim($_POST['notes'] ?? '');
        $treatment_plan_choice = $_POST['treatment_plan_choice'] ?? 'no_need';
        $treatment_schedule_data = $_POST['treatment_schedule_data'] ?? '';
        $single_treatment_data = $_POST['single_treatment_data'] ?? '';
        
        // Validate required fields
        if (empty($first_name) || empty($last_name) || $age === 0 || empty($gender)) {
            throw new Exception("Please fill all required patient information fields");
        }
        
        // Check if consultation form exists
        $check_stmt = $db->prepare("SELECT id FROM consultationforms WHERE appointment_id = ? LIMIT 1");
        if (!$check_stmt) {
            throw new Exception("Database prepare error: " . $db->error);
        }
        
        $check_stmt->bind_param('i', $appointment_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($existing) {
            // UPDATE existing form
            $stmt = $db->prepare("
                UPDATE consultationforms SET
                    first_name = ?,
                    last_name = ?,
                    age = ?,
                    gender = ?,
                    diagnosis = ?,
                    personal_products = ?,
                    notes = ?,
                    updated_at = NOW()
                WHERE appointment_id = ?
            ");
            
            if (!$stmt) {
                throw new Exception("Update prepare error: " . $db->error);
            }
            
            $stmt->bind_param(
                'ssissssi',
                $first_name, $last_name, $age, $gender,
                $diagnosis, $personal_products, $notes, $appointment_id
            );
            
        } else {
            // INSERT new form
            $stmt = $db->prepare("
                INSERT INTO consultationforms (
                    appointment_id, first_name, last_name, age, gender, 
                    diagnosis, personal_products, notes, patient_no, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, '', NOW())
            ");
            
            if (!$stmt) {
                throw new Exception("Insert prepare error: " . $db->error);
            }
            
            $stmt->bind_param(
                'isssisss',
                $appointment_id, $first_name, $last_name, $age, $gender,
                $diagnosis, $personal_products, $notes
            );
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save consultation form: " . $stmt->error);
        }
        $stmt->close();
        
        // Handle treatment plans
        if ($treatment_plan_choice === 'multiple_sessions' && !empty($treatment_schedule_data)) {
            
            $scheduleData = json_decode($treatment_schedule_data, true);
            
            if (!$scheduleData) {
                throw new Exception("Invalid treatment schedule data");
            }
            
            // Get treatment_id
            $treatment_name = $scheduleData['treatmentType'];
            $stmt = $db->prepare("SELECT treatment_id FROM treatment_list WHERE treatment_name = ? LIMIT 1");
            
            if (!$stmt) {
                throw new Exception("Treatment lookup error: " . $db->error);
            }
            
            $stmt->bind_param('s', $treatment_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $treatment_row = $result->fetch_assoc();
            $stmt->close();
            
            if (!$treatment_row) {
                throw new Exception("Treatment '" . $treatment_name . "' not found in database");
            }
            
            $treatment_id = $treatment_row['treatment_id'];
            $total_sessions = intval($scheduleData['sessions']);
            $sessions_per_week = intval($scheduleData['sessionsPerWeek']);
            $start_date = $scheduleData['startDate'];
            $total_cost = $total_sessions * 4500;
            $plan_diagnosis = $scheduleData['diagnosis'];
            
            // Insert treatment plan
            $stmt = $db->prepare("
                INSERT INTO treatment_plans (
                    appointment_id, patient_id, treatment_id, diagnosis,
                    total_sessions, sessions_per_week, start_date, total_cost,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
            ");
            
            if (!$stmt) {
                throw new Exception("Treatment plan insert error: " . $db->error);
            }
            
            $stmt->bind_param(
                'iiisiisd',
                $appointment_id,
                $patient_id,
                $treatment_id,
                $plan_diagnosis,
                $total_sessions,
                $sessions_per_week,
                $start_date,
                $total_cost
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create treatment plan: " . $stmt->error);
            }
            
            $plan_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert sessions
            if (isset($scheduleData['schedule']) && is_array($scheduleData['schedule'])) {
                foreach ($scheduleData['schedule'] as $session) {
                    $stmt = $db->prepare("
                        INSERT INTO treatment_sessions (
                            plan_id, session_number, session_date, session_time,
                            status, created_at
                        ) VALUES (?, ?, ?, ?, 'Pending', NOW())
                    ");
                    
                    if (!$stmt) {
                        throw new Exception("Session insert error: " . $db->error);
                    }
                    
                    $session_num = intval($session['sessionNumber']);
                    $session_date = $session['date'];
                    $session_time = $session['time'];
                    
                    $stmt->bind_param('iiss', $plan_id, $session_num, $session_date, $session_time);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to create session: " . $stmt->error);
                    }
                    
                    $stmt->close();
                }
            }
            
        } elseif ($treatment_plan_choice === 'single_session' && !empty($single_treatment_data)) {
            
            $treatmentData = json_decode($single_treatment_data, true);
            
            if ($treatmentData && isset($treatmentData['booking_id'])) {
                $booking_id = intval($treatmentData['booking_id']);
                
                $stmt = $db->prepare("
                    UPDATE consultationforms 
                    SET treatment_booking_id = ? 
                    WHERE appointment_id = ?
                ");
                
                if (!$stmt) {
                    throw new Exception("Booking link error: " . $db->error);
                }
                
                $stmt->bind_param('ii', $booking_id, $appointment_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Mark appointment as completed
        $stmt = $db->prepare("UPDATE consultations SET status = 'Completed' WHERE id = ?");
        
        if (!$stmt) {
            throw new Exception("Appointment update error: " . $db->error);
        }
        
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $stmt->close();
        
        $db->commit();
        $db->close();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Consultation saved successfully'
        ]);
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
            $db->close();
        }
        
        error_log("Consultation save error: " . $e->getMessage());
        
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    
    if ($_GET['action'] === 'get_consultation_form') {
        
        try {
            $appointment_id = intval($_GET['appointment_id'] ?? 0);
            
            if ($appointment_id === 0) {
                throw new Exception('Invalid appointment_id');
            }
            
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT * FROM consultationforms WHERE appointment_id = ? LIMIT 1");
            
            if (!$stmt) {
                throw new Exception("Query error: " . $db->error);
            }
            
            $stmt->bind_param('i', $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $form = $result->fetch_assoc();
            $stmt->close();
            $db->close();
            
            if ($form) {
                echo json_encode($form);
            } else {
                echo json_encode([]);
            }
            
        } catch (Exception $e) {
            error_log("Get consultation error: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}