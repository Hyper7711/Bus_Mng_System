<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "bus_management";

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";  // Uncomment to test
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

