<?php
// db_connect.php

$servername = "localhost";
$username   = "root";
$password   = ""; // leave empty if root has no password
$dbname     = "dheergayu_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
