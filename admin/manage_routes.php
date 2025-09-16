<?php
include '../db_connect.php';

$success = "";
$error = "";

// ✅ Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM routes WHERE route_id = :id");
        $stmt->execute([':id' => $delete_id]);
        $success = "🗑️ Route deleted successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error deleting route: " . $e->getMessage();
    }
}

// ✅ Handle Add/Edit Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_point = trim($_POST['start_point']);
    $end_point   = trim($_POST['end_point']);
    $stops       = trim($_POST['stops']);

    // If editing
    if (isset($_POST['route_id']) && !empty($_POST['route_id'])) {
        $route_id = $_POST['route_id'];
        try {
            $stmt = $conn->prepare("UPDATE routes SET start_point=:start_point, end_point=:end_point, stops=:stops WHERE route_id=:route_id");
            $stmt->execute([
                ':start_point' => $start_point,
                ':end_point'   => $end_point,
                ':stops'       => $stops,
                ':route_id'    => $route_id
            ]);
            $success = "✏️ Route updated successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error updating route: " . $e->getMessage();
        }
    } else { // Add new route
        try {
            $stmt = $conn->prepare("INSERT INTO routes (start_point, end_point, stops) VALUES (:start_point, :end_point, :stops)");
            $stmt->execute([
                ':start_point' => $start_point,
                ':end_point'   => $end_point,
                ':stops'       => $stops
            ]);
            $success = "✅ Route added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding route: " . $e->getMessage();
        }
    }
}

// ✅ Fetch routes
$routes = $conn->query("SELECT * FROM routes ORDER BY route_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// If editing, fetch the route details
$edit_route = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM routes WHERE route_id=:id");
    $stmt->execute([':id' => $edit_id]);
    $edit_route = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Routes</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        table { width: 70%; margin-top: 20px; }
        th { background-color: #f2f2f2; }
        .action-btn { padding: 5px 10px; border-radius: 5px; text-decoration: none; color: white; }
        .delete-btn { background-color: red; }
        .edit-btn { background-color: orange; }
    </style>
</head>
<body>
    <h2>🛣️ Manage Routes</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Add/Edit Route Form -->
    <form method="POST">
        <input type="hidden" name="route_id" value="<?= $edit_route['route_id'] ?? '' ?>">
        <label>Start Point:</label><br>
        <input type="text" name="start_point" value="<?= $edit_route['start_point'] ?? '' ?>" required><br><br>

        <label>End Point:</label><br>
        <input type="text" name="end_point" value="<?= $edit_route['end_point'] ?? '' ?>" required><br><br>

        <label>Stops (comma separated):</label><br>
        <input type="text" name="stops" value="<?= $edit_route['stops'] ?? '' ?>"><br><br>

        <button type="submit"><?= isset($edit_route) ? "Update Route" : "Add Route" ?></button>
        <?php if(isset($edit_route)): ?>
            <a href="manage_routes.php"><button type="button">Cancel Edit</button></a>
        <?php endif; ?>
    </form>

    <br>

    <!-- Route List -->
    <h2>📋 Route List</h2>
    <?php if (!empty($routes)) { ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Start Point</th>
                <th>End Point</th>
                <th>Stops</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($routes as $route): ?>
            <tr>
                <td><?= $route['route_id'] ?></td>
                <td><?= htmlspecialchars($route['start_point']) ?></td>
                <td><?= htmlspecialchars($route['end_point']) ?></td>
                <td><?= htmlspecialchars($route['stops']) ?></td>
                <td>
                    <a class="action-btn edit-btn" href="manage_routes.php?edit_id=<?= $route['route_id'] ?>">Edit</a>
                    <a class="action-btn delete-btn" href="manage_routes.php?delete_id=<?= $route['route_id'] ?>" onclick="return confirm('Are you sure to delete this route?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php } else { ?>
        <p>No routes found.</p>
    <?php } ?>
    <br>
    <a href="dashboard.php"><button type="button">⬅ Back to Dashboard</button></a>
</body>
</html>
