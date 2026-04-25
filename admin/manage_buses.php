```php
<?php
include '../db_connect.php';
include '../ai_logic.php'; // ✅ AI Logic Added

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
    $bus_number = trim($_POST['bus_no']); 
    $capacity   = (int) $_POST['capacity'];
    $driver_id  = $_POST['driver_id'];
    $route_id   = $_POST['route_id'];

    if (isset($_POST['bus_id']) && !empty($_POST['bus_id'])) {
        $bus_id = $_POST['bus_id'];
        try {
            $stmt = $conn->prepare("UPDATE buses SET bus_number=:bus_number, capacity=:capacity, driver_id=:driver_id, route_id=:route_id WHERE bus_id=:bus_id");
            $stmt->execute([
                ':bus_number' => $bus_number,
                ':capacity'   => $capacity,
                ':driver_id'  => $driver_id,
                ':route_id'   => $route_id,
                ':bus_id'     => $bus_id
            ]);
            $success = "✏️ Bus updated successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error updating bus: " . $e->getMessage();
        }
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO buses (bus_number, capacity, driver_id, route_id) VALUES (:bus_number, :capacity, :driver_id, :route_id)");
            $stmt->execute([
                ':bus_number' => $bus_number,
                ':capacity'   => $capacity,
                ':driver_id'  => $driver_id,
                ':route_id'   => $route_id
            ]);
            $success = "✅ Bus added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding bus: " . $e->getMessage();
        }
    }
}

// ✅ Fetch data
$drivers = $conn->query("SELECT driver_id, name FROM drivers")->fetchAll(PDO::FETCH_ASSOC);
$routes  = $conn->query("SELECT route_id, start_point, end_point FROM routes")->fetchAll(PDO::FETCH_ASSOC);
$buses   = $conn->query("SELECT b.bus_id, b.bus_number, b.capacity, d.name AS driver_name, r.start_point, r.end_point 
                         FROM buses b
                         LEFT JOIN drivers d ON b.driver_id=d.driver_id
                         LEFT JOIN routes r ON b.route_id=r.route_id
                         ORDER BY b.bus_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Edit fetch
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
</head>

<body>
<div class="card">
<h2>🚌 Manage Buses</h2>

<?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
<?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

<form method="POST">
<input type="hidden" name="bus_id" value="<?= $edit_bus['bus_id'] ?? '' ?>">
<input type="text" name="bus_no" placeholder="Bus Number" value="<?= $edit_bus['bus_number'] ?? '' ?>" required>
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
</form>

<h2>📋 Bus List</h2>

<table border="1">
<thead>
<tr>
<th>ID</th>
<th>Bus No</th>
<th>Capacity</th>
<th>Driver</th>
<th>Route</th>
<th>Actions</th>
<th>AI Status</th> <!-- ✅ Added -->
</tr>
</thead>

<tbody>
<?php foreach ($buses as $bus): 

// ✅ AI Simulation (no DB change)
$mileage = rand(1000, 10000);
$days = rand(1, 100);
$ai = get_ai_prediction($mileage, $days);

?>
<tr>
<td><?= $bus['bus_id'] ?></td>
<td><?= htmlspecialchars($bus['bus_number']) ?></td>
<td><?= $bus['capacity'] ?></td>
<td><?= htmlspecialchars($bus['driver_name']) ?></td>
<td><?= htmlspecialchars($bus['start_point']) ?> ➡ <?= htmlspecialchars($bus['end_point']) ?></td>

<td>
<a href="manage_buses.php?edit_id=<?= $bus['bus_id'] ?>">Edit</a>
<a href="manage_buses.php?delete_id=<?= $bus['bus_id'] ?>">Delete</a>
</td>

<!-- ✅ AI COLUMN -->
<td style="background:#f8f9fa;border-left:4px solid <?= $ai['color'] ?>;">
<strong><?= $ai['status'] ?></strong><br>
<small><?= $ai['insight'] ?></small>
</td>

</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</body>
</html>
```
