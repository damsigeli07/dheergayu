<?php

class TreatmentProgressModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Save treatment progress record
     */
    public function saveProgress($data) {
        $sql = "INSERT INTO treatment_progress (
            appointment_id, booking_id, progress_notes, materials_used, 
            patient_response, observations, additional_notes, duration_minutes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        // Prepare variables for binding
        $appointment_id = $data['appointment_id'] ?? 0;
        $booking_id = $data['booking_id'] ?? null;
        $progress_notes = $data['progress_notes'] ?? '';
        $materials_used = $data['materials_used'] ?? '';
        $patient_response = $data['patient_response'] ?? 'good';
        $observations = json_encode($data['observations'] ?? []);
        $additional_notes = $data['additional_notes'] ?? '';
        $duration_minutes = $data['duration_minutes'] ?? null;
        
        // Bind parameters
        $stmt->bind_param(
            'iisssssi',
            $appointment_id,
            $booking_id,
            $progress_notes,
            $materials_used,
            $patient_response,
            $observations,
            $additional_notes,
            $duration_minutes
        );

        $success = $stmt->execute();
        if (!$success) {
            error_log("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        return $success;
    }

    /**
     * Get progress record by appointment_id
     */
    public function getProgressByAppointmentId($appointment_id) {
        $stmt = $this->conn->prepare("SELECT * FROM treatment_progress WHERE appointment_id = ? LIMIT 1");
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $record = $result->fetch_assoc();
        $stmt->close();
        
        // Decode observations JSON
        if ($record && isset($record['observations'])) {
            $record['observations'] = json_decode($record['observations'], true) ?? [];
        }
        
        return $record;
    }

    /**
     * Get all progress records for a date range
     */
    public function getProgressByDateRange($start_date, $end_date, $staff_id = null) {
        $sql = "SELECT tp.*, a.patient_name, a.patient_no, tb.booking_date 
                FROM treatment_progress tp
                LEFT JOIN appointments a ON tp.appointment_id = a.appointment_id
                LEFT JOIN treatment_bookings tb ON tp.booking_id = tb.booking_id
                WHERE DATE(tp.created_at) BETWEEN ? AND ?";
        
        if ($staff_id) {
            $sql .= " AND tp.staff_id = ?";
        }
        
        $sql .= " ORDER BY tp.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return [];
        }

        if ($staff_id) {
            $stmt->bind_param('ssi', $start_date, $end_date, $staff_id);
        } else {
            $stmt->bind_param('ss', $start_date, $end_date);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $records = [];
        
        while ($row = $result->fetch_assoc()) {
            if (isset($row['observations'])) {
                $row['observations'] = json_decode($row['observations'], true) ?? [];
            }
            $records[] = $row;
        }
        
        $stmt->close();
        return $records;
    }

    /**
     * Update progress record
     */
    public function updateProgress($progress_id, $data) {
        $sql = "UPDATE treatment_progress SET 
                progress_notes = ?, materials_used = ?, patient_response = ?,
                observations = ?, additional_notes = ?, duration_minutes = ?
                WHERE progress_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        // Prepare variables for binding
        $progress_notes = $data['progress_notes'] ?? '';
        $materials_used = $data['materials_used'] ?? '';
        $patient_response = $data['patient_response'] ?? 'good';
        $observations = json_encode($data['observations'] ?? []);
        $additional_notes = $data['additional_notes'] ?? '';
        $duration_minutes = $data['duration_minutes'] ?? null;
        
        $stmt->bind_param(
            'sssssii',
            $progress_notes,
            $materials_used,
            $patient_response,
            $observations,
            $additional_notes,
            $duration_minutes,
            $progress_id
        );

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    /**
     * Check if progress record exists for appointment
     */
    public function existsForAppointment($appointment_id) {
        $stmt = $this->conn->prepare("SELECT progress_id FROM treatment_progress WHERE appointment_id = ? LIMIT 1");
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
