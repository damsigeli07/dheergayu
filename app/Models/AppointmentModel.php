<?php
// app/Models/AppointmentModel.php

class AppointmentModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get doctor appointments by doctor's user_id
    public function getDoctorAppointments($doctor_id) {
        if (!$doctor_id) {
            return [];
        }
        
        $query = "SELECT 
                    id as appointment_id,
                    patient_id,
                    doctor_id,
                    doctor_name,
                    patient_no,
                    patient_name,
                    CONCAT(appointment_date, ' ', appointment_time) as appointment_datetime,
                    status,
                    notes as reason
                  FROM consultations 
                  WHERE doctor_id = ? AND treatment_type = 'General Consultation'
                  ORDER BY appointment_date DESC, appointment_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        
        $stmt->close();
        return $appointments;
    }

    // Get patient appointments (consultations + treatments)
    public function getPatientAppointments($patient_id) {
        $consultations = [];
        $stmt = $this->conn->prepare("
            SELECT 
                id,
                doctor_id,
                doctor_name,
                patient_no,
                'General Consultation' as treatment_type,
                appointment_date,
                appointment_time,
                status,
                payment_method,
                payment_status
            FROM consultations 
            WHERE patient_id = ?
            ORDER BY appointment_date DESC, appointment_time DESC
        ");
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $consultations[] = $row;
        }
        $stmt->close();
        
        // Get treatment bookings using treatment_list table
        $treatments = [];
        $stmt = $this->conn->prepare("
            SELECT 
                tb.booking_id as id,
                NULL as doctor_id,
                NULL as doctor_name,
                NULL as patient_no,
                tl.treatment_name as treatment_type,
                tb.booking_date as appointment_date,
                ts.slot_time as appointment_time,
                tb.status,
                'onsite' as payment_method,
                CASE WHEN tb.status = 'Completed' THEN 'Completed' ELSE 'Pending' END as payment_status
            FROM treatment_bookings tb
            LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id
            LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id
            WHERE tb.patient_id = ?
            ORDER BY tb.booking_date DESC, ts.slot_time DESC
        ");
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $treatments[] = $row;
        }
        $stmt->close();
        
        return [
            'consultations' => $consultations,
            'treatments' => $treatments
        ];
    }

    public function bookConsultation($patient_id, $doctor_id, $appointment_date, $appointment_time, 
                                     $patient_name, $email, $phone, $age, $gender, $payment_method) {
        // Get doctor name from users table
        $doctorQuery = "SELECT first_name, last_name FROM users WHERE id = ? AND role = 'doctor' LIMIT 1";
        $doctorStmt = $this->conn->prepare($doctorQuery);
        $doctorStmt->bind_param('i', $doctor_id);
        $doctorStmt->execute();
        $doctorResult = $doctorStmt->get_result();
        $doctor = $doctorResult->fetch_assoc();
        $doctor_name = $doctor ? 'Dr. ' . $doctor['last_name'] : 'Unknown Doctor';
        $doctorStmt->close();
        
        // FIXED: Get patient number from patients table instead of generating new one
        $patientQuery = "SELECT patient_number FROM patients WHERE id = ? LIMIT 1";
        $patientStmt = $this->conn->prepare($patientQuery);
        $patientStmt->bind_param('i', $patient_id);
        $patientStmt->execute();
        $patientResult = $patientStmt->get_result();
        $patientData = $patientResult->fetch_assoc();
        
        if ($patientData && $patientData['patient_number']) {
            // Use existing patient number
            $patient_no = $patientData['patient_number'];
        } else {
            // Fallback: Generate new patient number only if patient doesn't have one
            // This should rarely happen if your patient registration is working correctly
            $patientNoQuery = "SELECT MAX(CAST(SUBSTRING(patient_number, 2) AS UNSIGNED)) as max_no 
                               FROM patients WHERE patient_number IS NOT NULL";
            $patientNoResult = $this->conn->query($patientNoQuery);
            $row = $patientNoResult->fetch_assoc();
            $nextNo = ($row['max_no'] ?? 0) + 1;
            $patient_no = 'P' . str_pad($nextNo, 4, '0', STR_PAD_LEFT);
            
            // Update the patient record with the new number
            $updatePatientNo = $this->conn->prepare("UPDATE patients SET patient_number = ? WHERE id = ?");
            $updatePatientNo->bind_param('si', $patient_no, $patient_id);
            $updatePatientNo->execute();
            $updatePatientNo->close();
        }
        $patientStmt->close();
        
        // Insert consultation
        $status = 'Pending';
        $treatment_type = 'General Consultation';
        
        $insertQuery = "INSERT INTO consultations 
                        (patient_id, doctor_id, doctor_name, patient_no, patient_name, age, gender, 
                         email, phone, treatment_type, appointment_date, appointment_time, 
                         status, payment_method, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($insertQuery);
        $stmt->bind_param(
            'iisssisssssss',
            $patient_id,
            $doctor_id,
            $doctor_name,
            $patient_no,
            $patient_name,
            $age,
            $gender,
            $email,
            $phone,
            $treatment_type,
            $appointment_date,
            $appointment_time,
            $status,
            $payment_method
        );
        
        if ($stmt->execute()) {
            $appointment_id = $stmt->insert_id;
            $stmt->close();
            return $appointment_id;
        }
        
        $stmt->close();
        return false;
    }

    public function cancelAppointment($appointment_id, $reason = '') {
        $query = "UPDATE consultations SET status = 'Cancelled', notes = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $reason, $appointment_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function setCompletedStatus($appointment_id) {
        $query = "UPDATE consultations SET status = 'Completed' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $appointment_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getConsultationById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM consultations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function isSlotAvailable($date, $time) {
        $stmt = $this->conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM consultations 
                 WHERE appointment_date = ? AND appointment_time = ? 
                 AND status IN ('Pending', 'Confirmed')) as consultation_count
        ");
        $stmt->bind_param("ss", $date, $time);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result['consultation_count'] == 0;
    }

    public function getAvailableSlots($date) {
        $slots = ['08:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
        $result = [];
        
        foreach ($slots as $slot) {
            $status = 'available';
            
            if (!$this->isSlotAvailable($date, $slot)) {
                $status = 'booked';
            }
            
            $result[] = [
                'time' => $slot,
                'status' => $status
            ];
        }
        
        return $result;
    }
}
?>