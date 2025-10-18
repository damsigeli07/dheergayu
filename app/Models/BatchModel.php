<?php
namespace App\Models;

use Core\Database;
use mysqli;

class BatchModel {
    private mysqli $db;
    private string $productIdColumn = 'id';
    private string $productNameColumn = 'name';

    public function __construct() {
        $this->db = Database::connect();
        $this->detectProductColumns();
    }

    private function detectProductColumns(): void {
        $cols = [];
        $res = $this->db->query("DESCRIBE products");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cols[] = $row['Field'];
            }
            $res->close();
        }
        // Determine ID column
        if (in_array('id', $cols, true)) {
            $this->productIdColumn = 'id';
        } elseif (in_array('product_id', $cols, true)) {
            $this->productIdColumn = 'product_id';
        }
        // Determine Name column
        if (in_array('name', $cols, true)) {
            $this->productNameColumn = 'name';
        } elseif (in_array('product_name', $cols, true)) {
            $this->productNameColumn = 'product_name';
        } elseif (in_array('title', $cols, true)) {
            $this->productNameColumn = 'title';
        }
    }

    public function getProducts(): array {
        $idCol = $this->productIdColumn;
        $nameCol = $this->productNameColumn;
        $sql = "SELECT `$idCol` AS id, `$nameCol` AS name FROM products ORDER BY `$nameCol`";
        $res = $this->db->query($sql);
        $products = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

    public function getInventoryOverview(): array {
        $idCol = $this->productIdColumn;
        $nameCol = $this->productNameColumn;
        $sql = "SELECT p.`$idCol` AS product_id, p.`$nameCol` AS product, 
                       COALESCE(SUM(b.quantity), 0) AS total_quantity,
                       MIN(b.exp) AS earliest_exp,
                       COUNT(b.product_id) AS batches_count
                FROM products p
                LEFT JOIN batches b ON b.product_id = p.`$idCol`
                GROUP BY p.`$idCol`, p.`$nameCol`
                ORDER BY p.`$nameCol`";
        $res = $this->db->query($sql);
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function getBatchesByProductId(int $productId): array {
        $stmt = $this->db->prepare("SELECT product_id, batch_number, quantity, mfd, exp, supplier, status FROM batches WHERE product_id = ? ORDER BY mfd DESC");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    public function getBatch(int $productId, string $batchNumber): ?array {
        $stmt = $this->db->prepare("SELECT product_id, batch_number, quantity, mfd, exp, supplier, status FROM batches WHERE product_id = ? AND batch_number = ? LIMIT 1");
        $stmt->bind_param('is', $productId, $batchNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function createBatch(int $productId, string $batchNumber, int $quantity, string $mfd, string $exp, string $supplier, string $status): bool {
        $stmt = $this->db->prepare("INSERT INTO batches (product_id, batch_number, quantity, mfd, exp, supplier, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('isissss', $productId, $batchNumber, $quantity, $mfd, $exp, $supplier, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updateBatch(int $productId, string $batchNumber, int $quantity, string $mfd, string $exp, string $supplier, string $status): bool {
        $stmt = $this->db->prepare("UPDATE batches SET quantity = ?, mfd = ?, exp = ?, supplier = ?, status = ? WHERE product_id = ? AND batch_number = ?");
        $stmt->bind_param('issssis', $quantity, $mfd, $exp, $supplier, $status, $productId, $batchNumber);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function deleteBatch(int $productId, string $batchNumber): bool {
        $stmt = $this->db->prepare("DELETE FROM batches WHERE product_id = ? AND batch_number = ?");
        $stmt->bind_param('is', $productId, $batchNumber);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function findProductIdByName(string $productName): ?int {
        $idCol = $this->productIdColumn;
        $nameCol = $this->productNameColumn;
        $stmt = $this->db->prepare("SELECT `$idCol` AS id FROM products WHERE `$nameCol` = ? LIMIT 1");
        $stmt->bind_param('s', $productName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['id'] : null;
    }
}


