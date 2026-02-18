<?php
// public/api/update-profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

try {
    // Debug session data
    error_log('Update Profile API - Session data: ' . json_encode($_SESSION));
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }

    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/PatientModel.php';

    $model = new PatientModel($conn);
    $user_id = $_SESSION['user_id'];

    $action = $_POST['action'] ?? '';
    
    // Debug received data
    error_log('Update Profile API - Action: ' . $action);
    error_log('Update Profile API - POST data: ' . json_encode($_POST));

    switch ($action) {
        case 'personal':
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                'gender' => $_POST['gender'] ?? '',
                'nic' => trim($_POST['nic'] ?? ''),
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'emergency_contact' => $_POST['emergency_contact'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];

            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
                exit;
            }

            // If NIC is provided, check it is not already used by another patient (patient_info or patients)
            if ($data['nic'] !== '') {
                $nicCheck = $conn->prepare("SELECT 1 FROM patient_info WHERE nic = ? AND patient_id != ? LIMIT 1");
                if ($nicCheck) {
                    $nicCheck->bind_param('si', $data['nic'], $user_id);
                    $nicCheck->execute();
                    if ($nicCheck->get_result()->fetch_assoc()) {
                        $nicCheck->close();
                        echo json_encode(['success' => false, 'error' => 'This NIC number is already registered to another user. Please use a different NIC or leave it blank.']);
                        exit;
                    }
                    $nicCheck->close();
                }
                $nicCheckPatients = $conn->prepare("SELECT 1 FROM patients WHERE nic = ? AND id != ? LIMIT 1");
                if ($nicCheckPatients) {
                    $nicCheckPatients->bind_param('si', $data['nic'], $user_id);
                    $nicCheckPatients->execute();
                    if ($nicCheckPatients->get_result()->fetch_assoc()) {
                        $nicCheckPatients->close();
                        echo json_encode(['success' => false, 'error' => 'This NIC number is already registered to another user. Please use a different NIC or leave it blank.']);
                        exit;
                    }
                    $nicCheckPatients->close();
                }
            }

            // Debug the data being sent
            error_log('Update Profile - User ID: ' . $user_id);
            error_log('Update Profile - Data: ' . json_encode($data));
            
            // Check if patient_info table exists first
            $table_check = $conn->query("SHOW TABLES LIKE 'patient_info'");
            error_log('Update Profile - Table exists: ' . ($table_check && $table_check->num_rows > 0 ? 'yes' : 'no'));
            
            // Check if profile exists
            $profile_exists = $model->profileExists($user_id);
            error_log('Update Profile - Profile exists: ' . ($profile_exists ? 'yes' : 'no'));
            
            $result = $model->updatePersonalInfo($user_id, $data);
            
            error_log('Update Profile - Result: ' . ($result ? 'true' : 'false'));
            
            if (!$result) {
                error_log('Update Profile - Database error: ' . $conn->error);
                $errMsg = $conn->error ?? '';
                $errNo = $conn->errno ?? 0;
                if ($errNo === 1062 || stripos($errMsg, 'duplicate') !== false && stripos($errMsg, 'nic') !== false) {
                    echo json_encode(['success' => false, 'error' => 'This NIC number is already registered. Please use a different NIC or leave it blank.']);
                    exit;
                }
            }
            
            if ($result) {
                $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
                $_SESSION['user_email'] = $data['email'];
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                // Check if it's a NIC conflict by testing the NIC
                if (!empty($data['nic'])) {
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM patients WHERE nic = ? AND id != ?");
                    $stmt->bind_param("si", $data['nic'], $user_id);
                    $stmt->execute();
                    $check_result = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if ($check_result['count'] > 0) {
                        echo json_encode(['success' => false, 'error' => 'NIC number already exists for another user. Please use a different NIC.']);
                    } else {
                        // Check if patient_info table exists
                        $table_check = $conn->query("SHOW TABLES LIKE 'patient_info'");
                        if (!$table_check || $table_check->num_rows == 0) {
                            echo json_encode(['success' => false, 'error' => 'Database table not found. Please contact support.']);
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Failed to update profile. Please check your data and try again.']);
                        }
                    }
                } else {
                    // Check if patient_info table exists
                    $table_check = $conn->query("SHOW TABLES LIKE 'patient_info'");
                    if (!$table_check || $table_check->num_rows == 0) {
                        echo json_encode(['success' => false, 'error' => 'Database table not found. Please contact support.']);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to update profile. Please check your data and try again.']);
                    }
                }
            }
            break;

        case 'medical':
            $data = [
                'blood_type' => $_POST['blood_type'] ?? '',
                'weight' => $_POST['weight'] ?? 0,
                'allergies' => $_POST['allergies'] ?? '',
                'current_medications' => $_POST['current_medications'] ?? '',
                'chronic_conditions' => $_POST['chronic_conditions'] ?? ''
            ];

            $result = $model->updateMedicalInfo($user_id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Medical information updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update medical information']);
            }
            break;

        case 'preferences':
            $data = [
                'preferred_language' => $_POST['preferred_language'] ?? 'en',
                'preferred_time' => $_POST['preferred_time'] ?? 'morning',
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'sms_notifications' => isset($_POST['sms_notifications']) ? 1 : 0,
                'marketing_communications' => isset($_POST['marketing_communications']) ? 1 : 0,
                'allow_data_improvement' => isset($_POST['allow_data_improvement']) ? 1 : 0,
                'share_research_data' => isset($_POST['share_research_data']) ? 1 : 0
            ];

            $result = $model->updatePreferences($user_id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Preferences saved successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save preferences']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }

    if (isset($conn)) {
        $conn->close();
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>