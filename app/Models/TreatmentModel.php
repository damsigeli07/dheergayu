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
        $sql = "SELECT treatment_id, treatment_name, description, duration, price, status FROM treatments ORDER BY treatment_id";
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
        $stmt = $this->db->prepare("SELECT treatment_id, treatment_name, description, duration, price, status FROM treatments WHERE treatment_id = ? LIMIT 1");
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
        $res = $this->db->query("SELECT COALESCE(MAX(treatment_id), 0) + 1 AS next_id FROM treatments");
        if ($res) {
            $row = $res->fetch_assoc();
            $nextId = (int)$row['next_id'];
        } else {
            $nextId = 1;
        }
        $stmt = $this->db->prepare("INSERT INTO treatments (treatment_id, treatment_name, description, duration, price, status) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('isssds', $nextId, $name, $description, $duration, $price, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update(int $id, string $name, ?string $description, string $duration, float $price, string $status): bool {
        $stmt = $this->db->prepare("UPDATE treatments SET treatment_name = ?, description = ?, duration = ?, price = ?, status = ? WHERE treatment_id = ?");
        if (!$stmt) { return false; }
        // types: s (name), s (description), s (duration), d (price), s (status), i (id)
        $stmt->bind_param('sssdsi', $name, $description, $duration, $price, $status, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $ok && $affected >= 0; // true on success even if values unchanged
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM treatments WHERE treatment_id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}


