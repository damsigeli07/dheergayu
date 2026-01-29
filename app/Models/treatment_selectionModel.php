<?php

class TreatmentSelectionModel {

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get all available treatments from treatment_list table
    public function getAllTreatments()
    {
        $sql = "SELECT treatment_id, treatment_name as name, description, price, duration 
                FROM treatment_list 
                WHERE status = 'Active' 
                ORDER BY treatment_name ASC";
        return $this->conn->query($sql);
    }

    // Get available slots for the selected date
    public function getAvailableSlots($treatment_id, $date)
    {
        $sql = "SELECT ts.slot_id, ts.slot_time,
                       CASE WHEN EXISTS (
                           SELECT 1
                           FROM treatment_bookings tb
                           WHERE tb.slot_id = ts.slot_id AND tb.booking_date = ? AND tb.status != 'Cancelled'
                       ) THEN 1 ELSE 0 END AS booked
                FROM treatment_slots ts
                WHERE ts.treatment_id = ? AND ts.is_active = 1
                GROUP BY ts.slot_time, ts.slot_id
                ORDER BY ts.slot_time ASC";

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

    // Save selection and create treatment booking
    public function saveSelection($patient_id, $treatment_id, $slot_id, $date, $description)
    {
        // Check for double bookings (exclude cancelled bookings)
        $checkSql = "SELECT booking_id FROM treatment_bookings WHERE slot_id = ? AND booking_date = ? AND status != 'Cancelled' LIMIT 1";
        $checkStmt = $this->conn->prepare($checkSql);
        if (!$checkStmt) {
            throw new RuntimeException('Failed to prepare slot check: ' . $this->conn->error);
        }
        $checkStmt->bind_param("is", $slot_id, $date);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $checkRes->num_rows > 0) {
            return false; // Slot already booked
        }

        $sql = "INSERT INTO treatment_bookings 
                (patient_id, treatment_id, slot_id, booking_date, description, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'Pending', NOW())";

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

    // Cancel treatment booking - frees the slot
    public function cancelBooking($booking_id, $reason = '')
    {
        $sql = "UPDATE treatment_bookings SET status = 'Cancelled', cancellation_reason = ?, updated_at = NOW() WHERE booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('si', $reason, $booking_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // Reschedule treatment booking - frees old slot and books new slot
    public function rescheduleBooking($booking_id, $new_slot_id, $new_date)
    {
        // Check if new slot is available
        $checkSql = "SELECT booking_id FROM treatment_bookings WHERE slot_id = ? AND booking_date = ? AND status != 'Cancelled' LIMIT 1";
        $checkStmt = $this->conn->prepare($checkSql);
        if (!$checkStmt) {
            return false;
        }
        $checkStmt->bind_param("is", $new_slot_id, $new_date);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $checkRes->num_rows > 0) {
            return false; // New slot already booked
        }

        // Update booking with new slot and date
        $sql = "UPDATE treatment_bookings SET slot_id = ?, booking_date = ?, updated_at = NOW() WHERE booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('isi', $new_slot_id, $new_date, $booking_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // Get treatment booking by ID
    public function getBookingById($booking_id)
    {
        $sql = "SELECT tb.*, tl.treatment_name as treatment_name, ts.slot_time 
                FROM treatment_bookings tb
                LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id
                LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id
                WHERE tb.booking_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();
        return $booking;
    }

    // Get patient's treatment bookings
    public function getPatientBookings($patient_id)
    {
        $sql = "SELECT tb.*, tl.treatment_name as treatment_name, tl.price, ts.slot_time 
                FROM treatment_bookings tb
                LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id
                LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id
                WHERE tb.patient_id = ?
                ORDER BY tb.booking_date DESC, ts.slot_time DESC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        $stmt->close();
        return $bookings;
    }

    // Mark treatment as completed
    public function completeBooking($booking_id)
    {
        $sql = "UPDATE treatment_bookings SET status = 'Completed', updated_at = NOW() WHERE booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $booking_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}