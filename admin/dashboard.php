<?php include '../db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ✅ Light Animated Gradient Background */
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(-45deg, #fceabb, #f8b500, #fad0c4, #ffd1ff);
            background-size: 400% 400%;
            animation: gradient 12s ease infinite;
        }

        @keyframes gradient {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* ✅ Dashboard Container */
        .container {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 90%;
            max-width: 700px;
            text-align: center;
        }

        /* ✅ Title */
        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        /* ✅ Grid Layout */
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 20px;
        }

        /* ✅ Card Buttons */
        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 20px 10px;
            color: #333;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card i {
            font-size: 2rem;
            opacity: 0.8;
        }

        .card:hover {
            background: #ffffff;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* ✅ Full Width Back Button */
        .back-btn {
            display: inline-block;
            background: #ff6f61;
            padding: 12px 0;
            width: 100%;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .back-btn:hover {
            background: #ff4c39;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🛠 Admin Dashboard</h2>

        <div class="grid">
            <a href="manage_student.php" class="card"><i class="fas fa-user-graduate"></i> Manage Students</a>
            <a href="manage_buses.php" class="card"><i class="fas fa-bus"></i> Manage Buses</a>
            <a href="manage_drivers.php" class="card"><i class="fas fa-id-card"></i> Manage Drivers</a>
            <a href="manage_routes.php" class="card"><i class="fas fa-route"></i> Manage Routes</a>
        </div>

        <a href="../index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
</body>
</html>
