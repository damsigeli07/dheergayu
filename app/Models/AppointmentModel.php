<?php
// app/Models/AppointmentModel.php

class AppointmentModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // CREATE: Book consultation (channeling)
    public function bookConsultation($patient_id, $doctor_id, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $payment_method) {
        $consultation_fee = 1500.00;
        $status = 'Pending';
        $payment_status = 'Pending';
        
                // Get doctor name
        $doctor_stmt = $this->conn->prepare("SELECT name FROM doctors WHERE id = ?");
        $doctor_stmt->bind_param("i", $doctor_id);
        $doctor_stmt->execute();
        $doctor_result = $doctor_stmt->get_result()->fetch_assoc();
        $doctor_name = $doctor_result['name'] ?? 'Unknown Doctor';
        $doctor_stmt->close();
        
        $stmt = $this->conn->prepare("
            INSERT INTO consultations (patient_id, doctor_id, appointment_date, appointment_time, patient_name, email, phone, age, gender, consultation_fee, status, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("iisssssisiss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $consultation_fee, $status, $payment_method, $payment_status);
        
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return $id;
        }
        $stmt->close();
        return false;
    }

    // CREATE: Book treatment
    public function bookTreatment($patient_id, $treatment_type, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $payment_method) {
        $treatment_fee = 2000.00;
        $status = 'Pending';
        $payment_status = 'Pending';
        
        $stmt = $this->conn->prepare("
            INSERT INTO treatments (patient_id, treatment_type, appointment_date, appointment_time, patient_name, email, phone, age, gender, treatment_fee, status, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isssssssisiss", $patient_id, $treatment_type, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $treatment_fee, $status, $payment_method, $payment_status);
        
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return $id;
        }
        $stmt->close();
        return false;
    }

    // READ: Get patient consultations
    public function getConsultations($patient_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM consultations 
            WHERE patient_id = ? 
            ORDER BY appointment_date DESC
        ");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // READ: Get patient treatments
    public function getTreatments($patient_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM treatments 
            WHERE patient_id = ? 
            ORDER BY appointment_date DESC
        ");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // READ: Get all appointments for patient
    public function getAllAppointments($patient_id) {
        $consultations = $this->getConsultations($patient_id);
        $treatments = $this->getTreatments($patient_id);
        return [
            'consultations' => $consultations,
            'treatments' => $treatments
        ];
    }

    // READ: Get consultation by ID
    public function getConsultationById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM consultations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // READ: Get treatment by ID
    public function getTreatmentById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM treatments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // UPDATE: Confirm payment and update status
    public function confirmPayment($id, $type, $transaction_id = null) {
        $table = ($type === 'consultation') ? 'consultations' : 'treatments';
        $payment_status = 'Completed';
        $status = 'Confirmed';
        
        if ($transaction_id) {
            $stmt = $this->conn->prepare("UPDATE $table SET payment_status = ?, status = ?, transaction_id = ? WHERE id = ?");
            $stmt->bind_param("sssi", $payment_status, $status, $transaction_id, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE $table SET payment_status = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssi", $payment_status, $status, $id);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // UPDATE: Cancel appointment
    public function cancelAppointment($id, $type) {
        $table = ($type === 'consultation') ? 'consultations' : 'treatments';
        $status = 'Cancelled';
        
        $stmt = $this->conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // DELETE: Remove appointment (if needed)
    public function deleteAppointment($id, $type) {
        $table = ($type === 'consultation') ? 'consultations' : 'treatments';
        
        $stmt = $this->conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // READ: Get doctors list
    public function getDoctors() {
        $stmt = $this->conn->prepare("SELECT id, name, specialty, consultation_fee FROM doctors WHERE is_available = TRUE");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // READ: Get available time slots
    public function getAvailableSlots($date) {
        $slots = ['08:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
        
        $stmt = $this->conn->prepare("
            SELECT appointment_time FROM consultations 
            WHERE appointment_date = ? AND status IN ('Pending', 'Confirmed')
            UNION
            SELECT appointment_time FROM treatments 
            WHERE appointment_date = ? AND status IN ('Pending', 'Confirmed')
        ");
        $stmt->bind_param("ss", $date, $date);
        $stmt->execute();
        $booked = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        $booked_times = array_column($booked, 'appointment_time');
        return array_filter($slots, function($slot) use ($booked_times) {
            return !in_array($slot, $booked_times);
        });
    }
}
?>