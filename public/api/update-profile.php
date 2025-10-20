<?php
// public/api/update-profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/PatientModel.php';

    $model = new PatientModel($conn);
    $user_id = $_SESSION['user_id'];

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'personal':
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                'gender' => $_POST['gender'] ?? '',
                'nic' => $_POST['nic'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'emergency_contact' => $_POST['emergency_contact'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];

            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
                exit;
            }

            $result = $model->updatePersonalInfo($user_id, $data);
            
            if ($result) {
                $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
                $_SESSION['user_email'] = $data['email'];
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
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