<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Step 1: PHP is running<br>";

// Database connection
$conn = new mysqli("localhost", "root", "", "db");
if ($conn->connect_error) {
    die("Step 2: Database connection failed: " . $conn->connect_error);
}

echo "Step 3: Database connected successfully<br>";

// Any remaining code
?>

