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
<html>
<head>
    <title>Manage Buses</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        table { width: 80%; margin-top: 20px; }
        th { background-color: #f2f2f2; }
        .action-btn { padding: 5px 10px; border-radius: 5px; text-decoration: none; color: white; }
        .delete-btn { background-color: red; }
        .edit-btn { background-color: orange; }
    </style>
</head>
<body>
    <h2>🚌 Manage Buses</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Add/Edit Bus Form -->
    <form method="POST">
        <input type="hidden" name="bus_id" value="<?= $edit_bus['bus_id'] ?? '' ?>">
        <label>Bus Number:</label><br>
        <input type="text" name="bus_no" value="<?= $edit_bus['bus_no'] ?? '' ?>" required><br><br>

        <label>Capacity:</label><br>
        <input type="number" name="capacity" value="<?= $edit_bus['capacity'] ?? '' ?>" required><br><br>

        <label>Select Driver:</label><br>
        <select name="driver_id" required>
            <option value="">--Select Driver--</option>
            <?php foreach ($drivers as $driver): ?>
                <option value="<?= $driver['driver_id'] ?>" <?= (isset($edit_bus) && $edit_bus['driver_id']==$driver['driver_id'])?'selected':'' ?>>
                    <?= htmlspecialchars($driver['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Select Route:</label><br>
        <select name="route_id" required>
            <option value="">--Select Route--</option>
            <?php foreach ($routes as $route): ?>
                <option value="<?= $route['route_id'] ?>" <?= (isset($edit_bus) && $edit_bus['route_id']==$route['route_id'])?'selected':'' ?>>
                    <?= htmlspecialchars($route['start_point']) ?> ➡ <?= htmlspecialchars($route['end_point']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit"><?= isset($edit_bus) ? "Update Bus" : "Add Bus" ?></button>
        <?php if(isset($edit_bus)): ?>
            <a href="manage_buses.php"><button type="button">Cancel Edit</button></a>
        <?php endif; ?>
    </form>

    <br>

    <!-- Bus List -->
    <h2>📋 Bus List</h2>
    <?php if (!empty($buses)) { ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Bus No</th>
                <th>Capacity</th>
                <th>Driver</th>
                <th>Route</th>
                <th>Actions</th>
            </tr>
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
        </table>
    <?php } else { ?>
        <p>No buses found.</p>
    <?php } ?>
</body>
</html>
