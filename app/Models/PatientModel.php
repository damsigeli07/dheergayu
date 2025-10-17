<?php
// app/Models/PatientModel.php - Update existing file

class PatientModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get patient profile
    public function getProfile($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // Update patient profile
    public function updateProfile($patient_id, $data) {
        $fields = [];
        $values = [];
        $types = "";

        foreach ($data as $field => $value) {
            if (in_array($field, ['first_name', 'last_name', 'email', 'phone', 'address'])) {
                $fields[] = "$field = ?";
                $values[] = $value;
                $types .= "s";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $patient_id;
        $types .= "i";

        $stmt = $this->conn->prepare("UPDATE patients SET " . implode(", ", $fields) . " WHERE id = ?");
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get appointment count
    public function getAppointmentStats($patient_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'confirmed') as upcoming,
                (SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'completed') as completed
        ");
        $stmt->bind_param("ii", $patient_id, $patient_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }
}
?>