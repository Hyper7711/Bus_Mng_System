<?php
// db_connect.php

$host = "localhost";       // Database host
$dbname = "bus_management"; // Your database name
$username = "root";        // Default XAMPP MySQL user
$password = "";            // Default XAMPP MySQL password is empty
$port = 3307;              // MySQL port

try {
    // Create PDO connection with port
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);

    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Uncomment the line below to test connection (optional)
    // echo "✅ Database connection successful!";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
