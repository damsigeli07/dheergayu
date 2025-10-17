<?php
// app/Models/AppointmentModel.php
// FIXED - Variables passed by reference

class AppointmentModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function bookConsultation($patient_id, $doctor_id, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $payment_method) {
        $consultation_fee = 1500.00;
        $status = 'Pending';
        $payment_status = 'Pending';

        $sql = "
            INSERT INTO consultations 
            (patient_id, doctor_id, appointment_date, appointment_time, patient_name, email, phone, age, gender, consultation_fee, status, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $this->conn->error);
        }

        // Pass variables by reference - this is critical for bind_param
        $stmt->bind_param(
            "iisssssidsss",
            $patient_id,
            $doctor_id,
            $appointment_date,
            $appointment_time,
            $patient_name,
            $email,
            $phone,
            $age,
            $gender,
            $consultation_fee,
            $status,
            $payment_method,
            $payment_status
        );

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $consultation_id = $stmt->insert_id;
        $stmt->close();
        
        return $consultation_id;
    }

    public function bookTreatment($patient_id, $treatment_type, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $payment_method) {
        $treatment_fee = 2000.00;
        $status = 'Pending';
        $payment_status = 'Pending';
        
        $sql = "
            INSERT INTO treatments 
            (patient_id, treatment_type, appointment_date, appointment_time, patient_name, email, phone, age, gender, treatment_fee, status, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $this->conn->error);
        }

        $stmt->bind_param(
            "isssssidsss",
            $patient_id,
            $treatment_type,
            $appointment_date,
            $appointment_time,
            $patient_name,
            $email,
            $phone,
            $age,
            $gender,
            $treatment_fee,
            $status,
            $payment_method,
            $payment_status
        );

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $treatment_id = $stmt->insert_id;
        $stmt->close();
        
        return $treatment_id;
    }

    public function getConsultations($patient_id) {
        $stmt = $this->conn->prepare("
            SELECT c.*, d.name as doctor_name 
            FROM consultations c
            LEFT JOIN doctors d ON c.doctor_id = d.id
            WHERE c.patient_id = ? 
            ORDER BY c.appointment_date DESC
        ");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

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

    public function getAllAppointments($patient_id) {
        $consultations = $this->getConsultations($patient_id);
        $treatments = $this->getTreatments($patient_id);
        return [
            'consultations' => $consultations,
            'treatments' => $treatments
        ];
    }

    public function getConsultationById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM consultations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function getTreatmentById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM treatments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

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

    public function cancelAppointment($id, $type) {
        $table = ($type === 'consultation') ? 'consultations' : 'treatments';
        $status = 'Cancelled';
        
        $stmt = $this->conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteAppointment($id, $type) {
        $table = ($type === 'consultation') ? 'consultations' : 'treatments';
        
        $stmt = $this->conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getDoctors() {
        $stmt = $this->conn->prepare("SELECT id, name, specialty, consultation_fee FROM doctors WHERE is_available = TRUE");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

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