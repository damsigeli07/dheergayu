<?php

class TreatmentSelectionModel {

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get all available treatments
    public function getAllTreatments()
    {
        $sql = "SELECT treatment_id, name FROM treatments ORDER BY name ASC";
        return $this->conn->query($sql);
    }

    // Get available slots for the selected date
    public function getAvailableSlots($treatment_id, $date)
{
    $sql = "SELECT ts.slot_id, ts.slot_time,
                   CASE WHEN EXISTS (
                       SELECT 1
                       FROM treatment_bookings tb
                       WHERE tb.slot_id = ts.slot_id AND tb.booking_date = ?
                   ) THEN 1 ELSE 0 END AS booked
            FROM treatment_slots ts
            WHERE ts.treatment_id = ?
            GROUP BY ts.slot_time
            ORDER BY STR_TO_DATE(ts.slot_time, '%h:%i %p') ASC";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare slots query: ' . $this->conn->error);
    }

    $stmt->bind_param("si", $date, $treatment_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = [];
    $seen = [];
    while ($row = $result->fetch_assoc()) {
        // Skip duplicate slot_time
        if (in_array($row['slot_time'], $seen)) continue;
        $seen[] = $row['slot_time'];

        $rows[] = [
            'slot_id' => (int)$row['slot_id'],
            'slot_time' => $row['slot_time'],
            'booked' => (int)$row['booked']
        ];
    }

    return $rows;
}


    // Save selection
    public function saveSelection($patient_id, $treatment_id, $slot_id, $date, $description)
    {
        // prevent double bookings
        $checkSql = "SELECT booking_id FROM treatment_bookings WHERE slot_id = ? AND booking_date = ? LIMIT 1";
        $checkStmt = $this->conn->prepare($checkSql);
        if (!$checkStmt) {
            throw new RuntimeException('Failed to prepare slot check: ' . $this->conn->error);
        }
        $checkStmt->bind_param("is", $slot_id, $date);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $checkRes->num_rows > 0) {
            return false;
        }

        $sql = "INSERT INTO treatment_bookings 
                (patient_id, treatment_id, slot_id, booking_date, description)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare booking insert: ' . $this->conn->error);
        }

        $stmt->bind_param("iiiss", $patient_id, $treatment_id, $slot_id, $date, $description);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }
}
