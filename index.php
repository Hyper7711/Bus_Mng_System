<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>🚍 Student Bus Management System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>🚍 Student Bus Management System</h1>
    <p>Welcome! Choose an option below:</p>

    <a href="admin/dashboard.php">
        <button style="padding:10px 20px; margin:10px;">Admin Dashboard</button>
    </a>

    <a href="reports/students.php">
        <button style="padding:10px 20px; margin:10px;">View Student Reports</button>
    </a>

    <a href="reports/buses.php">
        <button style="padding:10px 20px; margin:10px;">View Bus Reports</button>
    </a>
</body>
</html>
