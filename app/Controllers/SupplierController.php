<?php
// app/Controllers/SupplierController.php

require_once __DIR__ . '/../Models/SupplierModel.php';

class SupplierController {
    private $conn;
    private $model;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new SupplierModel($conn);
    }

    // Getter method for model
    public function getModel() {
        return $this->model;
    }

    // Show suppliers list page
    public function showSuppliers() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $suppliers = $this->model->getAllSuppliers();
        
        // Load view with data
        include __DIR__ . '/../Views/Admin/adminsuppliers.php';
    }

    // Handle add supplier form submission
    public function addSupplier() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? '')
            ];

            // Validate required fields
            if (empty($data['supplier_name']) || empty($data['contact_person']) || 
                empty($data['phone']) || empty($data['email']) || empty($data['password'])) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                header('Location: addsupplier.php');
                exit;
            }

            // Hash the password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Please enter a valid email address.';
                header('Location: addsupplier.php');
                exit;
            }

            // Validate phone format (10 digits)
            if (!preg_match('/^\d{10}$/', $data['phone'])) {
                $_SESSION['error'] = 'Phone number must be exactly 10 digits.';
                header('Location: addsupplier.php');
                exit;
            }

            $supplier_id = $this->model->createSupplier($data);
            
            if ($supplier_id) {
                $_SESSION['success'] = 'Supplier added successfully!';
                header('Location: adminsuppliers.php');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to add supplier. Please try again.';
                header('Location: addsupplier.php');
                exit;
            }
        } else {
            header('Location: addsupplier.php');
            exit;
        }
    }

    // Show edit supplier form
    public function showEditSupplier() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = 'Invalid supplier ID.';
            header('Location: adminsuppliers.php');
            exit;
        }

        $supplier = $this->model->getSupplierById($id);
        if (!$supplier) {
            $_SESSION['error'] = 'Supplier not found.';
            header('Location: adminsuppliers.php');
            exit;
        }

        include __DIR__ . '/../Views/Admin/editsupplier.php';
    }

    // Handle edit supplier form submission
    public function updateSupplier() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['supplier_id'] ?? null;
            if (!$id || !is_numeric($id)) {
                $_SESSION['error'] = 'Invalid supplier ID.';
                header('Location: adminsuppliers.php');
                exit;
            }

            $data = [
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? '')
            ];

            // Validate required fields
            if (empty($data['supplier_name']) || empty($data['contact_person']) || 
                empty($data['phone']) || empty($data['email'])) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                header("Location: editsupplier.php?id={$id}");
                exit;
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Please enter a valid email address.';
                header("Location: editsupplier.php?id={$id}");
                exit;
            }

            // Validate phone format (10 digits)
            if (!preg_match('/^\d{10}$/', $data['phone'])) {
                $_SESSION['error'] = 'Phone number must be exactly 10 digits.';
                header("Location: editsupplier.php?id={$id}");
                exit;
            }

            $result = $this->model->updateSupplier($id, $data);
            
            if ($result) {
                $_SESSION['success'] = 'Supplier updated successfully!';
                header('Location: adminsuppliers.php');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to update supplier. Please try again.';
                header("Location: editsupplier.php?id={$id}");
                exit;
            }
        } else {
            header('Location: adminsuppliers.php');
            exit;
        }
    }

    public function deactivateSupplier() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_GET['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = 'Invalid supplier ID.';
            header('Location: adminsuppliers.php');
            exit;
        }

        // Check if supplier exists
        $supplier = $this->model->getSupplierById($id);
        if (!$supplier) {
            $_SESSION['error'] = 'Supplier not found.';
            header('Location: adminsuppliers.php');
            exit;
        }

        // Deactivate supplier
        $result = $this->model->deactivateSupplier($id);

        if ($result) {
            $_SESSION['success'] = 'Supplier deactivated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to deactivate supplier. Please try again.';
        }

        header('Location: adminsuppliers.php');
        exit;
    }

    public function activateSupplier() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_GET['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = 'Invalid supplier ID.';
            header('Location: adminsuppliers.php');
            exit;
        }

        // Check if supplier exists
        $supplier = $this->model->getSupplierById($id);
        if (!$supplier) {
            $_SESSION['error'] = 'Supplier not found.';
            header('Location: adminsuppliers.php');
            exit;
        }

        // Activate supplier
        $result = $this->model->activateSupplier($id);

        if ($result) {
            $_SESSION['success'] = 'Supplier activated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to activate supplier. Please try again.';
        }

        header('Location: adminsuppliers.php');
        exit;
    }

    public function deleteSupplier() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        $_SESSION['error'] = 'Invalid supplier ID.';
        header('Location: adminsuppliers.php');
        exit;
    }

    // Check if supplier exists
    $supplier = $this->model->getSupplierById($id);
    if (!$supplier) {
        $_SESSION['error'] = 'Supplier not found.';
        header('Location: adminsuppliers.php');
        exit;
    }

    // 1️⃣ Move supplier to delete_suppliers before deletion
    $moved = $this->model->moveToDeletedSuppliers($supplier);

    if (!$moved) {
        $_SESSION['error'] = 'Failed to move supplier to deleted records.';
        header('Location: adminsuppliers.php');
        exit;
    }

    // 2️⃣ Delete from original suppliers table
    $result = $this->model->deleteSupplier($id);

    if ($result) {
        $_SESSION['success'] = 'Supplier deleted and moved to deleted records successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete supplier from main table.';
    }

    header('Location: adminsuppliers.php');
    exit;
}


}
?>
