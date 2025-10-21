<?php
namespace App\Models;

use Core\Database;
use mysqli;

class TreatmentModel {
    private mysqli $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {
        $sql = "SELECT treatment_id, treatment_name, description, duration, price, status FROM admin_treatment ORDER BY treatment_id";
        $res = $this->db->query($sql);
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT treatment_id, treatment_name, description, duration, price, status FROM admin_treatment WHERE treatment_id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function create(string $name, ?string $description, string $duration, float $price, string $status): bool {
        // If treatment_id is not AUTO_INCREMENT, compute next id
        $nextId = null;
        $res = $this->db->query("SELECT COALESCE(MAX(treatment_id), 0) + 1 AS next_id FROM admin_treatment");
        if ($res) {
            $row = $res->fetch_assoc();
            $nextId = (int)$row['next_id'];
        } else {
            $nextId = 1;
        }
        $stmt = $this->db->prepare("INSERT INTO admin_treatment (treatment_id, treatment_name, description, duration, price, status) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('isssds', $nextId, $name, $description, $duration, $price, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update(int $id, string $name, ?string $description, string $duration, float $price, string $status): bool {
        $stmt = $this->db->prepare("UPDATE admin_treatment SET treatment_name = ?, description = ?, duration = ?, price = ?, status = ? WHERE treatment_id = ?");
        if (!$stmt) { return false; }
        // types: s (name), s (description), s (duration), d (price), s (status), i (id)
        $stmt->bind_param('sssdsi', $name, $description, $duration, $price, $status, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $ok && $affected >= 0; // true on success even if values unchanged
    }

    
    public function delete(int $id): bool {
    // Step 1: Fetch treatment data before deletion
    $treatment = $this->getById($id);
    if (!$treatment) return false;

    $this->db->begin_transaction();
    try {
        // Step 2: Insert into deletedadmintreatment table
        $stmt1 = $this->db->prepare("
            INSERT INTO deletedadmintreatment (treatment_id, treatment_name, description, duration, price, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt1->bind_param(
            'isssds',
            $treatment['treatment_id'],
            $treatment['treatment_name'],
            $treatment['description'],
            $treatment['duration'],
            $treatment['price'],
            $treatment['status']
        );
        $stmt1->execute();
        $stmt1->close();

        // Step 3: Delete from treatments table
        $stmt2 = $this->db->prepare("DELETE FROM admin_treatment WHERE treatment_id = ?");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $stmt2->close();

        $this->db->commit();
        return true;
    } catch (\Throwable $e) {
        $this->db->rollback();
        return false;
    }
}

}


