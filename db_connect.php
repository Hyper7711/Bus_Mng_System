<?php
// db_connect.php

$host = "localhost";   // Database host
$dbname = "bus_management"; // Your database name
$username = "root";    // Default XAMPP MySQL user
$password = "";        // Default XAMPP MySQL password is empty

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Uncomment the line below to test connection (optional)
    // echo "✅ Database connection successful!";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
