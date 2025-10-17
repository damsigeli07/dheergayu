<?php
// public/test-db.php - Test database and sessions

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<h2>Database & Session Test</h2>";

// Test 1: Session
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test 2: Database Connection
echo "<h3>Database Connection:</h3>";
require_once __DIR__ . '/../config/config.php';

if ($conn->connect_error) {
    echo "❌ FAILED: " . $conn->connect_error;
} else {
    echo "✅ Connected successfully<br>";
    echo "Database: dheergayu_db<br><br>";
    
    // Test 3: Check tables
    echo "<h3>Tables:</h3>";
    $tables = ['patients', 'users', 'doctors', 'consultations', 'treatments'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Table '$table' exists<br>";
            
            // Count rows
            $count = $conn->query("SELECT COUNT(*) as total FROM $table")->fetch_assoc();
            echo "&nbsp;&nbsp;&nbsp;Records: " . $count['total'] . "<br>";
        } else {
            echo "❌ Table '$table' NOT FOUND<br>";
        }
    }
    
    // Test 4: Check doctors
    echo "<h3>Doctors:</h3>";
    $doctors = $conn->query("SELECT * FROM doctors");
    if ($doctors && $doctors->num_rows > 0) {
        while ($doc = $doctors->fetch_assoc()) {
            echo "- " . $doc['name'] . " (" . $doc['specialty'] . ") - Rs " . $doc['consultation_fee'] . "<br>";
        }
    } else {
        echo "⚠️ No doctors found! Run the SQL insert script.<br>";
    }
    
    // Test 5: Check consultations structure
    echo "<h3>Consultations Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE consultations");
    if ($structure) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($field = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $field['Field'] . "</td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . $field['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "<br><br><a href='Views/Patient/channeling.php'>Go to Channeling</a>";
?>