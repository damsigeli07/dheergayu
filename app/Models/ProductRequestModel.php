<?php
// app/Models/ProductRequestModel.php

class ProductRequestModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Create a new product request
    public function createRequest($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_requests (
                product_name, quantity, supplier_id, request_date, status, pharmacist_id
            ) VALUES (?, ?, ?, ?, 'pending', ?)
        ");
        
        $stmt->bind_param(
            "siisi",
            $data['product_name'],
            $data['quantity'],
            $data['supplier_id'],
            $data['request_date'],
            $data['pharmacist_id']
        );
        
        $result = $stmt->execute();
        $request_id = $this->conn->insert_id;
        $stmt->close();
        
        return $result ? $request_id : false;
    }

    /**
     * Create multiple product requests (one order with multiple lines).
     * @param int $supplier_id
     * @param string $request_date
     * @param int $pharmacist_id
     * @param array $items Array of ['product_name' => string, 'quantity' => int]
     * @return int|false Number of rows inserted, or false on failure
     */
    public function createRequestBatch($supplier_id, $request_date, $pharmacist_id, $items) {
        if (empty($items)) {
            return 0;
        }
        $stmt = $this->conn->prepare("
            INSERT INTO product_requests (
                product_name, quantity, supplier_id, request_date, status, pharmacist_id
            ) VALUES (?, ?, ?, ?, 'pending', ?)
        ");
        if (!$stmt) {
            return false;
        }
        $count = 0;
        foreach ($items as $item) {
            $qty = (int)($item['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $name = trim($item['product_name'] ?? '');
            if ($name === '') {
                continue;
            }
            $stmt->bind_param(
                "siisi",
                $name,
                $qty,
                $supplier_id,
                $request_date,
                $pharmacist_id
            );
            if ($stmt->execute()) {
                $count++;
            }
        }
        $stmt->close();
        return $count;
    }

    // Get all requests for a pharmacist - always reads from DB
    public function getRequestsByPharmacist($pharmacist_id) {
        $pharmacist_id = (int) $pharmacist_id;
        if ($pharmacist_id <= 0) {
            return [];
        }
        $stmt = $this->conn->prepare("
            SELECT pr.*, s.supplier_name, s.contact_person, s.phone, s.email
            FROM product_requests pr
            LEFT JOIN suppliers s ON pr.supplier_id = s.id
            WHERE pr.pharmacist_id = ?
            ORDER BY pr.request_date DESC, pr.id DESC
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $pharmacist_id);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }
        $res = $stmt->get_result();
        $rows = ($res && $res->num_rows > 0) ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    // Get all requests for a supplier
    public function getRequestsBySupplier($supplier_id) {
        $stmt = $this->conn->prepare("
            SELECT pr.*, u.first_name, u.last_name
            FROM product_requests pr
            LEFT JOIN users u ON pr.pharmacist_id = u.id
            WHERE pr.supplier_id = ?
            ORDER BY pr.request_date DESC
        ");
        $stmt->bind_param("i", $supplier_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    // Update request status (accept/reject)
    public function updateRequestStatus($request_id, $status) {
        $stmt = $this->conn->prepare("
            UPDATE product_requests 
            SET status = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $status, $request_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get all pending requests
    public function getAllPendingRequests() {
        $stmt = $this->conn->prepare("
            SELECT pr.*, s.supplier_name, s.contact_person, s.phone, s.email,
                   u.first_name, u.last_name
            FROM product_requests pr
            LEFT JOIN suppliers s ON pr.supplier_id = s.id
            LEFT JOIN users u ON pr.pharmacist_id = u.id
            WHERE pr.status = 'pending'
            ORDER BY pr.request_date DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function markAsDelivered($requestId) {
        $stmt = $this->conn->prepare("UPDATE product_requests SET status = 'delivered' WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        return $stmt->execute();
    }

    /**
     * Get a single request by id; only return if owned by pharmacist.
     */
    public function getRequestById($request_id, $pharmacist_id) {
        $stmt = $this->conn->prepare("
            SELECT pr.*, s.supplier_name FROM product_requests pr
            LEFT JOIN suppliers s ON pr.supplier_id = s.id
            WHERE pr.id = ? AND pr.pharmacist_id = ?
        ");
        $stmt->bind_param("ii", $request_id, $pharmacist_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Update product_name and quantity for a request. Only if pending and owned by pharmacist.
     */
    public function updateRequest($request_id, $pharmacist_id, $product_name, $quantity) {
        $stmt = $this->conn->prepare("
            UPDATE product_requests
            SET product_name = ?, quantity = ?
            WHERE id = ? AND pharmacist_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("siii", $product_name, $quantity, $request_id, $pharmacist_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Delete a request (cancel order line). Only if pending and owned by pharmacist.
     */
    public function deleteRequest($request_id, $pharmacist_id) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_requests
            WHERE id = ? AND pharmacist_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("ii", $request_id, $pharmacist_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>

