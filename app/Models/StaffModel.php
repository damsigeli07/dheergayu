<?php

class StaffModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get staff's treatment assignment by looking up which treatments
     * they are assigned to in the treatment_staff table.
     * Returns the first matching assignment with room (treatment_id) and treatment info.
     */
    public function getStaffRoomAssignment($staffName) {
        // Parse first and last name
        $parts = explode(' ', trim($staffName), 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        if (empty($firstName)) return null;

        // Find the user ID for this staff member
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE first_name = ? AND last_name = ? AND role = 'staff' LIMIT 1");
        $stmt->bind_param('ss', $firstName, $lastName);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) return null;

        return $this->getStaffRoomAssignmentById($user['id']);
    }

    /**
     * Get staff's treatment assignment by user ID.
     */
    public function getStaffRoomAssignmentById($staffId) {
        $staffId = (int)$staffId;

        // Check treatment_staff for this staff member in any role
        $stmt = $this->conn->prepare("
            SELECT ts.treatment_id, tl.treatment_name,
                CASE
                    WHEN ts.primary_staff1_id = ? THEN 'Primary Therapist'
                    WHEN ts.primary_staff2_id = ? THEN 'Secondary Therapist'
                END as role
            FROM treatment_staff ts
            JOIN treatment_list tl ON ts.treatment_id = tl.treatment_id
            WHERE ts.primary_staff1_id = ? OR ts.primary_staff2_id = ?
            LIMIT 1
        ");
        $stmt->bind_param('iiii', $staffId, $staffId, $staffId, $staffId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        return [
            'room' => $row['treatment_id'],
            'treatment_type' => $row['treatment_name'],
            'role' => $row['role']
        ];
    }

    /**
     * Get all staff assigned to a specific treatment (room).
     */
    public function getStaffByRoom($treatmentId) {
        $treatmentId = (int)$treatmentId;
        $staff = [];

        $stmt = $this->conn->prepare("
            SELECT ts.primary_staff1_id, ts.primary_staff2_id,
                CONCAT(u1.first_name, ' ', u1.last_name) as p1_name,
                CONCAT(u2.first_name, ' ', u2.last_name) as p2_name,
                tl.treatment_name
            FROM treatment_staff ts
            JOIN treatment_list tl ON ts.treatment_id = tl.treatment_id
            LEFT JOIN users u1 ON ts.primary_staff1_id = u1.id
            LEFT JOIN users u2 ON ts.primary_staff2_id = u2.id
            WHERE ts.treatment_id = ?
        ");
        $stmt->bind_param('i', $treatmentId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $staff[] = ['name' => $row['p1_name'], 'role' => 'Primary Therapist', 'treatment_type' => $row['treatment_name']];
            $staff[] = ['name' => $row['p2_name'], 'role' => 'Secondary Therapist', 'treatment_type' => $row['treatment_name']];
        }

        return $staff;
    }

    /**
     * Get all staff members with role='staff' from users table.
     */
    public function getAllStaffNames() {
        $names = [];
        $result = $this->conn->query("SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'staff' ORDER BY first_name");
        while ($row = $result->fetch_assoc()) {
            $names[] = $row['name'];
        }
        return $names;
    }
}

?>
