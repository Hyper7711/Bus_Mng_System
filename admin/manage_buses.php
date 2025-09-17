<?php
include '../db_connect.php';

$success = "";
$error = "";

// ✅ Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM buses WHERE bus_id = :id");
        $stmt->execute([':id' => $delete_id]);
        $success = "🗑️ Bus deleted successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error deleting bus: " . $e->getMessage();
    }
}

// ✅ Handle Add/Edit Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bus_no   = trim($_POST['bus_no']);
    $capacity = (int) $_POST['capacity'];
    $driver_id = $_POST['driver_id'];
    $route_id  = $_POST['route_id'];

    // If editing
    if (isset($_POST['bus_id']) && !empty($_POST['bus_id'])) {
        $bus_id = $_POST['bus_id'];
        try {
            $stmt = $conn->prepare("UPDATE buses SET bus_no=:bus_no, capacity=:capacity, driver_id=:driver_id, route_id=:route_id WHERE bus_id=:bus_id");
            $stmt->execute([
                ':bus_no' => $bus_no,
                ':capacity' => $capacity,
                ':driver_id' => $driver_id,
                ':route_id' => $route_id,
                ':bus_id' => $bus_id
            ]);
            $success = "✏️ Bus updated successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error updating bus: " . $e->getMessage();
        }
    } else { // Add new bus
        try {
            $stmt = $conn->prepare("INSERT INTO buses (bus_no, capacity, driver_id, route_id) VALUES (:bus_no, :capacity, :driver_id, :route_id)");
            $stmt->execute([
                ':bus_no' => $bus_no,
                ':capacity' => $capacity,
                ':driver_id' => $driver_id,
                ':route_id' => $route_id
            ]);
            $success = "✅ Bus added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding bus: " . $e->getMessage();
        }
    }
}

// ✅ Fetch buses, drivers, and routes
$drivers = $conn->query("SELECT driver_id, name FROM drivers")->fetchAll(PDO::FETCH_ASSOC);
$routes  = $conn->query("SELECT route_id, start_point, end_point FROM routes")->fetchAll(PDO::FETCH_ASSOC);
$buses   = $conn->query("SELECT b.bus_id, b.bus_no, b.capacity, d.name AS driver_name, r.start_point, r.end_point 
                         FROM buses b
                         LEFT JOIN drivers d ON b.driver_id=d.driver_id
                         LEFT JOIN routes r ON b.route_id=r.route_id
                         ORDER BY b.bus_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// If editing, fetch the bus details
$edit_bus = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM buses WHERE bus_id=:id");
    $stmt->execute([':id' => $edit_id]);
    $edit_bus = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Buses</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 30px;
        }

        .card {
            width: 100%;
            max-width: 1100px;
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            margin: 0 0 20px;
            color: #2c3e50;
        }

        .message {
            font-weight: 600;
            margin: 10px 0;
            text-align: center;
            font-size: 1rem;
            border-radius: 8px;
            padding: 10px;
        }

        .success { background: #e8fdf0; color: #1e7b34; border: 1px solid #a9e5b9; }
        .error { background: #fde8e8; color: #b91c1c; border: 1px solid #f5b5b5; }

        form {
            margin-bottom: 25px;
        }

        form input, form select {
            padding: 8px;
            width: 100%;
            max-width: 300px;
            margin-bottom: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        form button {
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            transition: 0.3s;
        }

        form button:hover { background: #0056b3; }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            font-size: 0.95rem;
        }

        th {
            background: #f9fafb;
            font-weight: bold;
            border-bottom: 2px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        tr:nth-child(even) td { background: #fcfcfc; }
        tr:hover td { background: #f1f5f9; transition: 0.2s; }

        .action-btn {
            padding: 5px 10px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: 0.2s;
        }

        .edit-btn { background: orange; }
        .edit-btn:hover { background: darkorange; }

        .delete-btn { background: red; }
        .delete-btn:hover { background: darkred; }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background: #ddd;
            color: #333;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .back-btn:hover { background: #bbb; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🚌 Manage Buses</h2>

        <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

        <!-- Add/Edit Bus Form -->
        <form method="POST">
            <input type="hidden" name="bus_id" value="<?= $edit_bus['bus_id'] ?? '' ?>">
            <input type="text" name="bus_no" placeholder="Bus Number" value="<?= $edit_bus['bus_no'] ?? '' ?>" required>
            <input type="number" name="capacity" placeholder="Capacity" value="<?= $edit_bus['capacity'] ?? '' ?>" required>

            <select name="driver_id" required>
                <option value="">--Select Driver--</option>
                <?php foreach ($drivers as $driver): ?>
                    <option value="<?= $driver['driver_id'] ?>" <?= (isset($edit_bus) && $edit_bus['driver_id']==$driver['driver_id'])?'selected':'' ?>>
                        <?= htmlspecialchars($driver['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="route_id" required>
                <option value="">--Select Route--</option>
                <?php foreach ($routes as $route): ?>
                    <option value="<?= $route['route_id'] ?>" <?= (isset($edit_bus) && $edit_bus['route_id']==$route['route_id'])?'selected':'' ?>>
                        <?= htmlspecialchars($route['start_point']) ?> ➡ <?= htmlspecialchars($route['end_point']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit"><?= isset($edit_bus) ? "Update Bus" : "Add Bus" ?></button>
            <?php if(isset($edit_bus)): ?>
                <a href="manage_buses.php"><button type="button">Cancel Edit</button></a>
            <?php endif; ?>
        </form>

        <!-- Bus List -->
        <h2>📋 Bus List</h2>
        <div class="table-container">
            <?php if (!empty($buses)) { ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bus No</th>
                            <th>Capacity</th>
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($buses as $bus): ?>
                        <tr>
                            <td><?= $bus['bus_id'] ?></td>
                            <td><?= htmlspecialchars($bus['bus_no']) ?></td>
                            <td><?= $bus['capacity'] ?></td>
                            <td><?= htmlspecialchars($bus['driver_name']) ?></td>
                            <td><?= htmlspecialchars($bus['start_point']) ?> ➡ <?= htmlspecialchars($bus['end_point']) ?></td>
                            <td>
                                <a class="action-btn edit-btn" href="manage_buses.php?edit_id=<?= $bus['bus_id'] ?>">Edit</a>
                                <a class="action-btn delete-btn" href="manage_buses.php?delete_id=<?= $bus['bus_id'] ?>" onclick="return confirm('Are you sure to delete this bus?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p style="text-align:center;">No buses found.</p>
            <?php } ?>
        </div>

        <a href="../admin/dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
