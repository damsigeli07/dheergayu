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

    /** Detect product table ID & name columns */
    private function detectProductColumns(): void {
        $cols = [];
        $res = $this->db->query("DESCRIBE products");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cols[] = $row['Field'];
            }
            $res->close();
        }

        if (in_array('id', $cols, true)) {
            $this->productIdColumn = 'id';
        } elseif (in_array('product_id', $cols, true)) {
            $this->productIdColumn = 'product_id';
        }

        if (in_array('name', $cols, true)) {
            $this->productNameColumn = 'name';
        } elseif (in_array('product_name', $cols, true)) {
            $this->productNameColumn = 'product_name';
        } elseif (in_array('title', $cols, true)) {
            $this->productNameColumn = 'title';
        }
    }

    /** Get all products */
    public function getProducts(): array {
        $idCol = $this->productIdColumn;
        $nameCol = $this->productNameColumn;
        $res = $this->db->query("SELECT `$idCol` AS id, `$nameCol` AS name FROM products ORDER BY `$nameCol`");
        $products = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
            $res->close();
        }
        return $products;
    }

    /** Get inventory overview */
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
            $res->close();
        }
        return $rows;
    }

    /** Get all batches of a product */
    public function getBatchesByProductId(int $productId, ?string $productSource = null): array {
        if ($productSource) {
            $stmt = $this->db->prepare("
                SELECT product_id, batch_number, quantity, mfd, exp, supplier, status 
                FROM batches WHERE product_id = ? AND product_source = ? ORDER BY mfd DESC
            ");
            $stmt->bind_param('is', $productId, $productSource);
        } else {
            $stmt = $this->db->prepare("
                SELECT product_id, batch_number, quantity, mfd, exp, supplier, status 
                FROM batches WHERE product_id = ? ORDER BY mfd DESC
            ");
            $stmt->bind_param('i', $productId);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /** Get a single batch */
    public function getBatch(int $productId, string $batchNumber): ?array {
        $stmt = $this->db->prepare("SELECT * FROM batches WHERE product_id = ? AND batch_number = ? LIMIT 1");
        $stmt->bind_param('is', $productId, $batchNumber);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /** Create new batch */
    public function createBatch(int $productId, string $batchNumber, int $quantity, string $mfd, string $exp, string $supplier, string $status, ?string $productSource = 'admin'): bool {
        $stmt = $this->db->prepare("
            INSERT INTO batches (product_id, product_source, batch_number, quantity, mfd, exp, supplier, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('isisssss', $productId, $productSource, $batchNumber, $quantity, $mfd, $exp, $supplier, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /** Update batch */
    public function updateBatch(int $productId, string $batchNumber, int $quantity, string $mfd, string $exp, string $supplier, string $status): bool {
        $stmt = $this->db->prepare("
            UPDATE batches SET quantity=?, mfd=?, exp=?, supplier=?, status=? 
            WHERE product_id=? AND batch_number=?
        ");
        $stmt->bind_param('issssis', $quantity, $mfd, $exp, $supplier, $status, $productId, $batchNumber);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /** Delete batch safely (no duplicate in deleted_batches) */
    public function deleteBatch(int $productId, string $batchNumber): bool {
        $batch = $this->getBatch($productId, $batchNumber);
        if (!$batch) return false;

        // Check if already exists in expired_batches
        $stmtCheck = $this->db->prepare("
            SELECT 1 FROM expired_batches WHERE batch_id=? LIMIT 1
        ");
        $stmtCheck->bind_param('i', $batch['batch_id']);
        $stmtCheck->execute();
        $exists = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if (!$exists) {
            $stmtInsert = $this->db->prepare("
                INSERT INTO expired_batches
                (batch_id, product_id, batch_number, quantity, mfd, exp, supplier, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtInsert->bind_param(
                'iisissssss',
                $batch['batch_id'],
                $batch['product_id'],
                $batch['batch_number'],
                $batch['quantity'],
                $batch['mfd'],
                $batch['exp'],
                $batch['supplier'],
                $batch['status'],
                $batch['created_at'],
                $batch['updated_at']
            );
            $stmtInsert->execute();
            $stmtInsert->close();
        }

        // Delete from main batches table
        $stmtDelete = $this->db->prepare("DELETE FROM batches WHERE product_id=? AND batch_number=?");
        $stmtDelete->bind_param('is', $productId, $batchNumber);
        $okDelete = $stmtDelete->execute();
        $stmtDelete->close();

        return $okDelete;
    }

    /** Find product ID by name */
    public function findProductIdByName(string $productName): ?int {
        $idCol = $this->productIdColumn;
        $nameCol = $this->productNameColumn;
        $stmt = $this->db->prepare("SELECT `$idCol` AS id FROM products WHERE `$nameCol`=? LIMIT 1");
        $stmt->bind_param('s', $productName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['id'] : null;
    }
}
