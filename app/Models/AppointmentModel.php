<?php
// app/Models/AppointmentModel.php

class AppointmentModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // CREATE: Book consultation (channeling)
    public function bookConsultation($patient_id, $doctor_id, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $payment_method) {
        $consultation_fee = 2000.00;
        $status = 'Pending';
        $payment_status = 'Pending';
        
        $doctor_name = $this->getDoctorName($doctor_id);
        
        $stmt = $this->conn->prepare("
            INSERT INTO consultations (patient_id, doctor_id, doctor_name, appointment_date, appointment_time, patient_name, email, phone, age, gender, consultation_fee, status, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("iissssssidssss", $patient_id, $doctor_id, $doctor_name, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $consultation_fee, $status, $payment_method, $payment_status);
        
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
        $treatment_fee = $this->getTreatmentFee($treatment_type);
        $status = 'Pending';
        $payment_status = 'Pending';
        
        $stmt = $this->conn->prepare("
            INSERT INTO treatments (patient_id, treatment_type, appointment_date, appointment_time, patient_name, email, phone, age, gender, treatment_fee, status, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isssssssdssss", $patient_id, $treatment_type, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $treatment_fee, $status, $payment_method, $payment_status);
        
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

    // READ: Get all appointments for doctor dashboard
    public function getAllDoctorAppointments() {
        // Try different possible table names for appointments
        $possible_tables = ['appointments', 'doctor_appointments', 'appointment_list'];
        $appointments = [];
        
        foreach ($possible_tables as $table_name) {
            $stmt = $this->conn->prepare("SELECT * FROM $table_name ORDER BY appointment_datetime DESC");
            if ($stmt && $stmt->execute()) {
                $result = $stmt->get_result();
                $raw_appointments = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                
                if (!empty($raw_appointments)) {
                    // Transform the data to match expected format
                    foreach ($raw_appointments as $row) {
                        $appointment = [
                            'appointment_id' => $row['appointment_id'] ?? '',
                            'patient_no' => $row['patient_no'] ?? '',
                            'patient_name' => $row['patient_name'] ?? '',
                            'appointment_datetime' => $row['appointment_datetime'] ?? '',
                            'status' => $row['status'] ?? '',
                            'reason' => $row['reason'] ?? '',
                            'actions' => $row['actions'] ?? ''
                        ];
                        
                        $appointments[] = $appointment;
                    }
                    break; // Found data, stop looking
                }
            }
        }
        
        return $appointments;
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

    // Helper: Get doctor name
    private function getDoctorName($doctor_id) {
        $stmt = $this->conn->prepare("SELECT name FROM doctors WHERE id = ?");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ? $result['name'] : 'Unknown Doctor';
    }

    // Helper: Get treatment fee from treatment_list
    private function getTreatmentFee($treatment_name) {
        $stmt = $this->conn->prepare("SELECT price FROM treatment_list WHERE treatment_name = ?");
        $stmt->bind_param("s", $treatment_name);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ? $result['price'] : 2000.00;
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

    // UPDATE: Cancel appointment with reason (for appointments table)
    public function cancelAppointmentWithReason($appointment_id, $reason) {
        // Try different possible table names for appointments
        $possible_tables = ['appointments', 'doctor_appointments', 'appointment_list'];
        
        foreach ($possible_tables as $table_name) {
            // First try with reason column
            $stmt = $this->conn->prepare("UPDATE $table_name SET status = 'Cancelled', reason = ? WHERE appointment_id = ?");
            if ($stmt && $stmt->bind_param("si", $reason, $appointment_id) && $stmt->execute()) {
                $stmt->close();
                return true;
            }
            if ($stmt) $stmt->close();
            
            // If that fails, try without reason column
            $stmt = $this->conn->prepare("UPDATE $table_name SET status = 'Cancelled' WHERE appointment_id = ?");
            if ($stmt && $stmt->bind_param("i", $appointment_id) && $stmt->execute()) {
                $stmt->close();
                return true;
            }
            if ($stmt) $stmt->close();
        }
        
        return false;
    }

    // UPDATE: Update appointment date and time
    public function updateAppointment($id, $type, $date, $time) {
        $table = ($type === 'consultation') ? 'consultations' : 'treatments';
        
        $stmt = $this->conn->prepare("UPDATE $table SET appointment_date = ?, appointment_time = ? WHERE id = ?");
        $stmt->bind_param("ssi", $date, $time, $id);
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

    // UPDATE: Set appointment status to completed
    public function setCompletedStatus($appointment_id) {
        // Try to update in appointments table first
        $stmt = $this->conn->prepare("UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $appointment_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    // READ: Get doctors list
    public function getDoctors() {
        $stmt = $this->conn->prepare("SELECT id, name, specialty, consultation_fee FROM doctors WHERE is_available = TRUE");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }



    // Lock a time slot temporarily (5 minutes)
    public function lockSlot($date, $time, $user_id) {
        // First, clean expired locks
        $this->cleanExpiredLocks();
        
        // Check if slot is already locked or booked
        if ($this->isSlotLocked($date, $time) || !$this->isSlotAvailable($date, $time)) {
            return false;
        }
        
        // Lock the slot
        $stmt = $this->conn->prepare("
            INSERT INTO slot_locks (slot_date, slot_time, locked_by) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("ssi", $date, $time, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Release slot lock
    public function releaseSlot($date, $time, $user_id) {
        $stmt = $this->conn->prepare("
            DELETE FROM slot_locks 
            WHERE slot_date = ? AND slot_time = ? AND locked_by = ?
        ");
        $stmt->bind_param("ssi", $date, $time, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Check if slot is locked
    public function isSlotLocked($date, $time) {
        $this->cleanExpiredLocks();
        
        $stmt = $this->conn->prepare("
            SELECT id FROM slot_locks 
            WHERE slot_date = ? AND slot_time = ? AND expires_at > NOW()
        ");
        $stmt->bind_param("ss", $date, $time);
        $stmt->execute();
        $result = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $result;
    }
    
    // Check if slot is available (not booked in consultations or treatments)
    public function isSlotAvailable($date, $time) {
        $stmt = $this->conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM consultations 
                 WHERE appointment_date = ? AND appointment_time = ? 
                 AND status IN ('Pending', 'Confirmed')) +
                (SELECT COUNT(*) FROM treatments 
                 WHERE appointment_date = ? AND appointment_time = ? 
                 AND status IN ('Pending', 'Confirmed')) as total
        ");
        $stmt->bind_param("ssss", $date, $time, $date, $time);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result['total'] == 0;
    }
    
    // Clean expired locks (older than 5 minutes)
    private function cleanExpiredLocks() {
        $stmt = $this->conn->prepare("DELETE FROM slot_locks WHERE expires_at < NOW()");
        $stmt->execute();
        $stmt->close();
    }
    
    // UPDATED: Get available time slots with lock status
    public function getAvailableSlots($date) {
        $slots = ['08:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
        $result = [];
        
        $this->cleanExpiredLocks();
        
        foreach ($slots as $slot) {
            $status = 'available';
            
            // Check if booked
            if (!$this->isSlotAvailable($date, $slot)) {
                $status = 'booked';
            } 
            // Check if locked by someone else
            else if ($this->isSlotLocked($date, $slot)) {
                $status = 'locked';
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