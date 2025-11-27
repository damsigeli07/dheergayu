<?php
class patienthistoryModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Search patients in consultation table by patient_no OR name + dob
    public function searchPatients($patient_no = "", $name = "", $dob = "") {
        $sql = "SELECT * FROM consultationforms WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($patient_no)) {
            $sql .= " AND patient_no = ?";
            $params[] = $patient_no;
            $types .= 's';
        }

        if (!empty($name)) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
            $params[] = "%$name%";
            $params[] = "%$name%";
            $types .= 'ss';
        }

        if (!empty($dob)) {
            $sql .= " AND dob = ?"; // make sure consultation table has dob column, otherwise join with patients table
            $params[] = $dob;
            $types .= 's';
        }

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get consultation history for a patient
    public function getConsultationFormHistory($patient_no) {
        $sql = "SELECT diagnosis, personal_products, recommended_treatment, notes, created_at , age FROM consultationforms WHERE patient_no = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $patient_no);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
