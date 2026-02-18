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

    /** Get inventory overview for admin products */
    public function getInventoryOverview(): array {
        $idCol = $this->productIdColumn;
        $nameCol = $this->productNameColumn;
        $sql = "SELECT p.`$idCol` AS product_id, p.`$nameCol` AS product, 
                       COALESCE(SUM(b.quantity), 0) AS total_quantity,
                       MIN(b.exp) AS earliest_exp,
                       COUNT(b.product_id) AS batches_count
                FROM products p
                LEFT JOIN batches b ON b.product_id = p.`$idCol` AND (b.product_source = 'admin' OR b.product_source IS NULL)
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

    /** Get inventory overview for patient products */
    public function getPatientProductsOverview(): array {
        $sql = "SELECT p.product_id, p.name AS product, 
                       COALESCE(SUM(b.quantity), 0) AS total_quantity,
                       MIN(b.exp) AS earliest_exp,
                       COUNT(b.product_id) AS batches_count
                FROM patient_products p
                LEFT JOIN batches b ON b.product_id = p.product_id AND b.product_source = 'patient'
                GROUP BY p.product_id, p.name
                ORDER BY p.name";
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
                SELECT product_id, batch_number, quantity, mfd, exp, status 
                FROM batches WHERE product_id = ? AND product_source = ? ORDER BY mfd DESC
            ");
            $stmt->bind_param('is', $productId, $productSource);
        } else {
            $stmt = $this->db->prepare("
                SELECT product_id, batch_number, quantity, mfd, exp, status 
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
    public function createBatch(int $productId, ?string $productSource, string $batchNumber, int $quantity, string $mfd, string $exp, string $status): bool {
        // Log parameters for debugging
        error_log("BatchModel::createBatch - productId: $productId, productSource: " . var_export($productSource, true) . ", batchNumber: " . var_export($batchNumber, true) . ", quantity: $quantity");
        error_log("BatchModel::createBatch - batchNumber type: " . gettype($batchNumber) . ", length: " . strlen($batchNumber));
        
        // Default product_source to 'admin' if not provided
        $productSource = $productSource ?? 'admin';
        
        $stmt = $this->db->prepare("
            INSERT INTO batches (product_id, product_source, batch_number, quantity, mfd, exp, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("BatchModel::createBatch prepare error: " . $this->db->error);
            return false;
        }
        
        // Parameter order in SQL: product_id, product_source, batch_number, quantity, mfd, exp, status
        $typeString = 'ississs';  // product_id, product_source, batch_number, quantity, mfd, exp, status
        
        error_log("BatchModel::createBatch - Type string: '$typeString' (length: " . strlen($typeString) . ")");
        error_log("BatchModel::createBatch - Parameters count: 7");
        
        $result = $stmt->bind_param($typeString, $productId, $productSource, $batchNumber, $quantity, $mfd, $exp, $status);
        if (!$result) {
            error_log("BatchModel::createBatch - bind_param failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $ok = $stmt->execute();
        if (!$ok) {
            error_log("BatchModel::createBatch execute error: " . $stmt->error);
            error_log("BatchModel::createBatch - SQL state: " . $stmt->sqlstate);
        } else {
            error_log("BatchModel::createBatch - Successfully inserted batch with batch_number: " . var_export($batchNumber, true));
        }
        $stmt->close();
        return $ok;
    }

    /** Update batch */
    public function updateBatch(int $productId, string $batchNumber, int $quantity, string $mfd, string $exp, string $status): bool {
        $stmt = $this->db->prepare("
            UPDATE batches SET quantity=?, mfd=?, exp=?, status=? 
            WHERE product_id=? AND batch_number=?
        ");
        $stmt->bind_param('isssis', $quantity, $mfd, $exp, $status, $productId, $batchNumber);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /** Delete batch safely (no duplicate in deleted_batches) */
    public function deleteBatch(int $productId, string $batchNumber): bool {
        $batch = $this->getBatch($productId, $batchNumber);
        if (!$batch) {
            error_log("BatchModel::deleteBatch - Batch not found: product_id=$productId, batch_number=$batchNumber");
            return false;
        }

        // Try to archive to expired_batches if table exists and batch_id exists
        if (isset($batch['batch_id'])) {
            try {
                // Check if expired_batches table exists
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'expired_batches'");
                if ($tableCheck && $tableCheck->num_rows > 0) {
                    // Check if already exists in expired_batches
                    $stmtCheck = $this->db->prepare("
                        SELECT 1 FROM expired_batches WHERE batch_id=? LIMIT 1
                    ");
                    if ($stmtCheck) {
                        $stmtCheck->bind_param('i', $batch['batch_id']);
                        $stmtCheck->execute();
                        $exists = $stmtCheck->get_result()->fetch_assoc();
                        $stmtCheck->close();

                        if (!$exists) {
                            $stmtInsert = $this->db->prepare("
                                INSERT INTO expired_batches
                                (batch_id, product_id, batch_number, quantity, mfd, exp, status, created_at, updated_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            if ($stmtInsert) {
                                $createdAt = $batch['created_at'] ?? date('Y-m-d H:i:s');
                                $updatedAt = $batch['updated_at'] ?? date('Y-m-d H:i:s');
                                $stmtInsert->bind_param(
                                    'iisisssss',
                                    $batch['batch_id'],
                                    $batch['product_id'],
                                    $batch['batch_number'],
                                    $batch['quantity'],
                                    $batch['mfd'],
                                    $batch['exp'],
                                    $batch['status'],
                                    $createdAt,
                                    $updatedAt
                                );
                                $stmtInsert->execute();
                                if ($stmtInsert->error) {
                                    error_log("BatchModel::deleteBatch - Error inserting into expired_batches: " . $stmtInsert->error);
                                }
                                $stmtInsert->close();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // If expired_batches table doesn't exist or has issues, just log and continue
                error_log("BatchModel::deleteBatch - Error archiving to expired_batches: " . $e->getMessage());
            }
        }

        // Delete from main batches table
        $stmtDelete = $this->db->prepare("DELETE FROM batches WHERE product_id=? AND batch_number=?");
        if (!$stmtDelete) {
            error_log("BatchModel::deleteBatch - Prepare error: " . $this->db->error);
            return false;
        }
        $stmtDelete->bind_param('is', $productId, $batchNumber);
        $okDelete = $stmtDelete->execute();
        if (!$okDelete) {
            error_log("BatchModel::deleteBatch - Execute error: " . $stmtDelete->error);
        }
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
