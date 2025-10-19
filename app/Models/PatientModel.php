<?php
// app/Models/PatientModel.php

class PatientModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get patient profile by user_id
    public function getProfileByUserId($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM patient_info WHERE patient_id = ?");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // Create initial patient profile
    public function createProfile($patient_id, $email, $first_name = '', $last_name = '') {
        $stmt = $this->conn->prepare("
            INSERT INTO patient_info (patient_id, email, first_name, last_name) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $patient_id, $email, $first_name, $last_name);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Update personal information
    public function updatePersonalInfo($user_id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE patient_info SET 
                first_name = ?,
                last_name = ?,
                date_of_birth = ?,
                gender = ?,
                nic = ?,
                email = ?,
                phone = ?,
                emergency_contact = ?,
                address = ?
            WHERE patient_id = ?
        ");
        
        $stmt->bind_param(
            "sssssssssi",
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'],
            $data['gender'],
            $data['nic'],
            $data['email'],
            $data['phone'],
            $data['emergency_contact'],
            $data['address'],
            $user_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Update medical information
    public function updateMedicalInfo($user_id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE patient_info SET 
                blood_type = ?,
                weight = ?,
                allergies = ?,
                current_medications = ?,
                chronic_conditions = ?
            WHERE patient_id = ?
        ");
        
        $stmt->bind_param(
            "sdsssi",
            $data['blood_type'],
            $data['weight'],
            $data['allergies'],
            $data['current_medications'],
            $data['chronic_conditions'],
            $user_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Update preferences
    public function updatePreferences($user_id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE patient_info SET 
                preferred_language = ?,
                preferred_time = ?,
                email_notifications = ?,
                sms_notifications = ?,
                marketing_communications = ?,
                allow_data_improvement = ?,
                share_research_data = ?
            WHERE patient_id = ?
        ");
        
        $stmt->bind_param(
            "ssiiiii",
            $data['preferred_language'],
            $data['preferred_time'],
            $data['email_notifications'],
            $data['sms_notifications'],
            $data['marketing_communications'],
            $data['allow_data_improvement'],
            $data['share_research_data'],
            $user_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get appointment statistics
    public function getAppointmentStats($user_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(CASE WHEN status NOT IN ('Cancelled', 'Completed') THEN 1 END) as upcoming_count,
                MAX(CASE WHEN status = 'Completed' THEN appointment_date END) as last_visit,
                MIN(CASE WHEN status NOT IN ('Cancelled', 'Completed') THEN appointment_date END) as next_appointment,
                (SELECT created_at FROM patient_info WHERE patient_id = ?) as member_since
            FROM (
                SELECT appointment_date, status FROM consultations WHERE patient_id = ?
                UNION ALL
                SELECT appointment_date, status FROM treatments WHERE patient_id = ?
            ) as all_appointments
        ");
        
        $stmt->bind_param("iii", $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // Get recent medical history
    public function getRecentMedicalHistory($user_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM (
                SELECT 
                    'Consultation' as type,
                    appointment_date,
                    doctor_name,
                    'General Consultation' as details
                FROM consultations 
                WHERE patient_id = ? AND status = 'Completed'
                
                UNION ALL
                
                SELECT 
                    'Treatment' as type,
                    appointment_date,
                    '' as doctor_name,
                    treatment_type as details
                FROM treatments 
                WHERE patient_id = ? AND status = 'Completed'
            ) as history
            ORDER BY appointment_date DESC
            LIMIT 5
        ");
        
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    // Check if profile exists
    public function profileExists($user_id) {
        $stmt = $this->conn->prepare("SELECT id FROM patient_info WHERE patient_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $result;
    }

    // Delete patient account
    public function deleteAccount($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM patient_info WHERE patient_id = ?");
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>