<?php
// app/Models/SupplierModel.php

class SupplierModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all suppliers
    public function getAllSuppliers() {
        $stmt = $this->conn->prepare("
            SELECT * FROM suppliers 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    // Get supplier by ID
    public function getSupplierById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // Create new supplier
    public function createSupplier($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO suppliers (
                supplier_name, contact_person, phone, email
            ) VALUES (?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssss",
            $data['supplier_name'],
            $data['contact_person'],
            $data['phone'],
            $data['email']
        );
        
        $result = $stmt->execute();
        $supplier_id = $this->conn->insert_id;
        $stmt->close();
        
        return $result ? $supplier_id : false;
    }

    // Update supplier
    public function updateSupplier($id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE suppliers SET 
                supplier_name = ?,
                contact_person = ?,
                phone = ?,
                email = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "ssssi",
            $data['supplier_name'],
            $data['contact_person'],
            $data['phone'],
            $data['email'],
            $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Delete supplier
    public function deleteSupplier($id) {
        $stmt = $this->conn->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Move deleted supplier to delete_suppliers table
public function moveToDeletedSuppliers($supplier) {
    $stmt = $this->conn->prepare("
        INSERT INTO delete_suppliers (supplier_name, contact_person, phone, email)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssss",
        $supplier['supplier_name'],
        $supplier['contact_person'],
        $supplier['phone'],
        $supplier['email']
    );
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}


    // Check if supplier exists
    public function supplierExists($id) {
        $stmt = $this->conn->prepare("SELECT id FROM suppliers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $result;
    }
}
?>
