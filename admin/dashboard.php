<?php include '../db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>🛠 Admin Dashboard</h1>
        <p>Choose what you want to manage:</p>

        <div class="cards">
            <a href="manage_student.php" class="card">
                <h2>Students</h2>
                <p>Add / Manage Students</p>
            </a>
            <a href="manage_buses.php" class="card">
                <h2>Buses</h2>
                <p>Add / Manage Buses</p>
            </a>
            <a href="manage_drivers.php" class="card">
                <h2>Drivers</h2>
                <p>Add / Manage Drivers</p>
            </a>
            <a href="manage_routes.php" class="card">
                <h2>Routes</h2>
                <p>Add / Manage Routes</p>
            </a>
        </div>

        <a href="../index.php" class="btn btn-back">⬅ Back to Home</a>
    </div>
</body>
</html>
