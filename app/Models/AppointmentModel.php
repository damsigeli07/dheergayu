<?php
class AppointmentModel {
    public function setCompletedStatus($appointment_id) {
        $stmt = $this->conn->prepare("UPDATE appointments SET status='Completed' WHERE appointment_id=?");
        $stmt->bind_param('i', $appointment_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    public function cancelAppointment($appointment_id, $reason) {
        $stmt = $this->conn->prepare("UPDATE appointments SET status='Cancelled', reason=? WHERE appointment_id=?");
        $stmt->bind_param('ss', $reason, $appointment_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllAppointments() {
    $sql = "SELECT appointment_id, patient_no, patient_name, appointment_datetime, status, reason FROM appointments";
        $result = $this->conn->query($sql);
        $appointments = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        return $appointments;
    }
}
