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

    // Get all requests for a pharmacist
    public function getRequestsByPharmacist($pharmacist_id) {
        $stmt = $this->conn->prepare("
            SELECT pr.*, s.supplier_name, s.contact_person, s.phone, s.email
            FROM product_requests pr
            LEFT JOIN suppliers s ON pr.supplier_id = s.id
            WHERE pr.pharmacist_id = ?
            ORDER BY pr.request_date DESC
        ");
        $stmt->bind_param("i", $pharmacist_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
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
}
?>

