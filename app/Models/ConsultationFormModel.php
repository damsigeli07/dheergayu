<?php
class ConsultationFormModel {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getConsultationFormByAppointmentId($appointment_id) {
        $stmt = $this->conn->prepare("SELECT * FROM consultationforms WHERE appointment_id = ? LIMIT 1");
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $form = $result->fetch_assoc();
        $stmt->close();
        return $form;
    }
    public function getAppointmentDetails($appointment_id) {
        $stmt = $this->conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $details = $result->fetch_assoc();
        $stmt->close();
        return $details;
    }
    public function saveConsultationForm($data) {
        // Check if record exists for appointment_id
        $stmt = $this->conn->prepare("SELECT id FROM consultationforms WHERE appointment_id = ? LIMIT 1");
        $stmt->bind_param('i', $data['appointment_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->fetch_assoc();
        $stmt->close();
        if ($exists) {
            return $this->updateConsultationForm($data);
        }
        $sql = "INSERT INTO consultationforms (
            first_name, last_name, age, diagnosis, gender, personal_products, recommended_treatment,
            question_1, question_2, question_3, question_4, notes, patient_no, last_visit_date,
            total_visits, contact_info, check_patient_vitals, review_previous_medications,
            update_patient_history, follow_up_appointment, send_to_pharmacy, appointment_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param(
            'ssissssssssssssiissiii',
            $data['first_name'], $data['last_name'], $data['age'], $data['diagnosis'], $data['gender'],
            $data['personal_products'], $data['recommended_treatment'],
            $data['question_1'], $data['question_2'], $data['question_3'], $data['question_4'],
            $data['notes'], $data['patient_no'], $data['last_visit_date'], $data['total_visits'],
            $data['contact_info'], $data['check_patient_vitals'], $data['review_previous_medications'],
            $data['update_patient_history'], $data['follow_up_appointment'], $data['send_to_pharmacy'],
            $data['appointment_id']
        );
        $success = $stmt->execute();
        if (!$success) {
            error_log("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        return $success;
    }

    public function updateConsultationForm($data) {
        $sql = "UPDATE consultationforms SET
            first_name = ?, last_name = ?, age = ?, diagnosis = ?, gender = ?, personal_products = ?, recommended_treatment = ?,
            question_1 = ?, question_2 = ?, question_3 = ?, question_4 = ?, notes = ?, patient_no = ?, last_visit_date = ?,
            total_visits = ?, contact_info = ?, check_patient_vitals = ?, review_previous_medications = ?,
            update_patient_history = ?, follow_up_appointment = ?, send_to_pharmacy = ?
            WHERE appointment_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param(
            'ssissssssssssssiissiii',
            $data['first_name'], $data['last_name'], $data['age'], $data['diagnosis'], $data['gender'],
            $data['personal_products'], $data['recommended_treatment'],
            $data['question_1'], $data['question_2'], $data['question_3'], $data['question_4'],
            $data['notes'], $data['patient_no'], $data['last_visit_date'], $data['total_visits'],
            $data['contact_info'], $data['check_patient_vitals'], $data['review_previous_medications'],
            $data['update_patient_history'], $data['follow_up_appointment'], $data['send_to_pharmacy'],
            $data['appointment_id']
        );
        $success = $stmt->execute();
        if (!$success) {
            error_log("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        return $success;
    }
}
