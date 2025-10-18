<?php
// app/Controllers/PatientController.php

require_once __DIR__ . '/../Models/PatientModel.php';

class PatientController {
    private $conn;
    private $model;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new PatientModel($conn);
    }

    // Show patient profile page
    public function showProfile() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        // Check if profile exists, if not create one
        if (!$this->model->profileExists($user_id)) {
            $this->model->createProfile(
                $user_id,
                $_SESSION['user_email'] ?? '',
                $_SESSION['user_name'] ?? ''
            );
        }

        // Get profile data
        $profile = $this->model->getProfileByUserId($user_id);
        $stats = $this->model->getAppointmentStats($user_id);
        $medical_history = $this->model->getRecentMedicalHistory($user_id);

        // Load view with data
        include __DIR__ . '/../Views/Patient/patient_profile.php';
    }

    // Get profile data (AJAX)
    public function getProfile() {
        session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $profile = $this->model->getProfileByUserId($user_id);
        $stats = $this->model->getAppointmentStats($user_id);
        
        if ($profile) {
            echo json_encode([
                'success' => true,
                'profile' => $profile,
                'stats' => $stats
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Profile not found']);
        }
    }

    // Update personal information
    public function updatePersonalInfo() {
        session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender' => $_POST['gender'] ?? '',
            'nic' => $_POST['nic'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'emergency_contact' => $_POST['emergency_contact'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
            exit;
        }

        $result = $this->model->updatePersonalInfo($user_id, $data);
        
        if ($result) {
            // Update session data
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['user_email'] = $data['email'];
            
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
        }
    }

    // Update medical information
    public function updateMedicalInfo() {
        session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        $data = [
            'blood_type' => $_POST['blood_type'] ?? '',
            'weight' => $_POST['weight'] ?? 0,
            'allergies' => $_POST['allergies'] ?? '',
            'current_medications' => $_POST['current_medications'] ?? '',
            'chronic_conditions' => $_POST['chronic_conditions'] ?? ''
        ];

        $result = $this->model->updateMedicalInfo($user_id, $data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Medical information updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update medical information']);
        }
    }

    // Update preferences
    public function updatePreferences() {
        session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        $data = [
            'preferred_language' => $_POST['preferred_language'] ?? 'en',
            'preferred_time' => $_POST['preferred_time'] ?? 'morning',
            'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
            'sms_notifications' => isset($_POST['sms_notifications']) ? 1 : 0,
            'marketing_communications' => isset($_POST['marketing_communications']) ? 1 : 0,
            'allow_data_improvement' => isset($_POST['allow_data_improvement']) ? 1 : 0,
            'share_research_data' => isset($_POST['share_research_data']) ? 1 : 0
        ];

        $result = $this->model->updatePreferences($user_id, $data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Preferences saved successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save preferences']);
        }
    }

    // Get medical history
    public function getMedicalHistory() {
        session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $history = $this->model->getRecentMedicalHistory($user_id);
        
        echo json_encode(['success' => true, 'history' => $history]);
    }

    // Delete account
    public function deleteAccount() {
        session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $result = $this->model->deleteAccount($user_id);
        
        if ($result) {
            // Destroy session
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete account']);
        }
    }
}
?>