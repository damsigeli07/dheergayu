<?php
// Script to update supplier passwords to "1234"
// Run this once to set correct password hashes
// Access via: http://localhost/dheergayu/database/update_supplier_passwords.php

require_once __DIR__ . '/../config/config.php';

// Generate password hash for "1234"
$password_hash = password_hash('1234', PASSWORD_DEFAULT);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Supplier Passwords</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Update Supplier Passwords</h1>
    
    <?php
    // Update all suppliers with the new password
    $stmt = $conn->prepare("UPDATE suppliers SET password = ? WHERE id IN (1, 2, 3)");
    $stmt->bind_param("s", $password_hash);

    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "<div class='success'>";
        echo "<strong>Success!</strong><br>";
        echo "Updated passwords for $affected supplier(s).<br>";
        echo "All suppliers can now login with password: <code>1234</code><br><br>";
        echo "<strong>Password hash generated:</strong><br>";
        echo "<code>" . htmlspecialchars($password_hash) . "</code>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>Error updating passwords:</strong> " . htmlspecialchars($stmt->error);
        echo "</div>";
    }

    $stmt->close();
    
    // Show current supplier info
    $result = $conn->query("SELECT id, supplier_name, email, status FROM suppliers WHERE id IN (1, 2, 3)");
    if ($result && $result->num_rows > 0) {
        echo "<div class='info'>";
        echo "<strong>Current Suppliers:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['id']} - {$row['supplier_name']} ({$row['email']}) - Status: {$row['status']}<br>";
        }
        echo "</div>";
    }
    
    $conn->close();
    ?>
    
    <p><a href="../app/Views/Patient/login.php">Go to Login Page</a></p>
</body>
</html>

