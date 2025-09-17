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
    $route_id    = $_POST['route_id'] ?? null;
    $start_point = trim($_POST['start_point']);
    $end_point   = trim($_POST['end_point']);

    if ($route_id) { // Edit
        try {
            $stmt = $conn->prepare("UPDATE routes SET start_point=:start_point, end_point=:end_point WHERE route_id=:route_id");
            $stmt->execute([
                ':start_point' => $start_point,
                ':end_point'   => $end_point,
                ':route_id'    => $route_id
            ]);
            $success = "✏️ Route updated successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error updating route: " . $e->getMessage();
        }
    } else { // Add
        try {
            $stmt = $conn->prepare("INSERT INTO routes (start_point, end_point) VALUES (:start_point, :end_point)");
            $stmt->execute([
                ':start_point' => $start_point,
                ':end_point'   => $end_point
            ]);
            $success = "✅ Route added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding route: " . $e->getMessage();
        }
    }
}

// ✅ Fetch all routes
$routes = $conn->query("SELECT * FROM routes ORDER BY route_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// If editing
$edit_route = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM routes WHERE route_id=:id");
    $stmt->execute([':id' => $edit_id]);
    $edit_route = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Routes</title>
<style>
    body {
        margin: 0;
        font-family: "Segoe UI", sans-serif;
        background: linear-gradient(135deg, #4facfe, #00f2fe); /* Blue gradient */
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 30px;
    }
    .card {
        width: 100%;
        max-width: 800px;
        background: white;
        padding: 25px;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    h2 { text-align: center; font-size: 2rem; margin-bottom: 20px; color: #2c3e50; }
    .message { font-weight: 600; margin: 10px 0; text-align: center; font-size: 1rem; border-radius: 8px; padding: 10px; }
    .success { background: #e8fdf0; color: #1e7b34; border: 1px solid #a9e5b9; }
    .error { background: #fde8e8; color: #b91c1c; border: 1px solid #f5b5b5; }
    form input, form button { padding: 8px; margin: 5px 0; border-radius: 6px; border: 1px solid #ccc; width: 100%; box-sizing: border-box; }
    button { background: #007bff; color: white; border: none; cursor: pointer; transition: 0.3s; }
    button:hover { background: #0056b3; }
    .table-container { overflow-x: auto; margin-top: 20px; max-height: 60vh; }
    table { width: 100%; border-collapse: collapse; min-width: 400px; }
    th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
    th { background: #f9fafb; position: sticky; top: 0; }
    tr:hover td { background: #f1f5f9; }
    .action-btn { padding: 5px 10px; border-radius: 6px; text-decoration: none; color: white; }
    .edit-btn { background: orange; }
    .delete-btn { background: red; }
    .search-box { display: flex; justify-content: flex-end; margin-bottom: 15px; }
    .search-box input { padding: 8px 10px; width: 250px; border-radius: 6px; border: 1px solid #ccc; outline: none; }
    .back-btn { display: inline-block; margin-top: 20px; background: #ddd; color: #333; padding: 8px 16px; border-radius: 8px; text-decoration: none; transition: 0.3s; }
    .back-btn:hover { background: #bbb; }
</style>
<script>
    function searchTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let rows = document.querySelectorAll("table tbody tr");
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? "" : "none";
        });
    }
</script>
</head>
<body>
<div class="card">
    <h2>🗺️ Manage Routes</h2>

    <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

    <!-- Add/Edit Route Form -->
    <form method="POST">
        <input type="hidden" name="route_id" value="<?= $edit_route['route_id'] ?? '' ?>">
        <input type="text" name="start_point" placeholder="Start Point" value="<?= $edit_route['start_point'] ?? '' ?>" required>
        <input type="text" name="end_point" placeholder="End Point" value="<?= $edit_route['end_point'] ?? '' ?>" required>
        <button type="submit"><?= isset($edit_route) ? "Update Route" : "Add Route" ?></button>
        <?php if(isset($edit_route)): ?>
            <a href="manage_routes.php"><button type="button">Cancel Edit</button></a>
        <?php endif; ?>
    </form>

    <!-- Search -->
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="🔍 Search routes..." onkeyup="searchTable()">
    </div>

    <!-- Route List -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Start Point</th>
                    <th>End Point</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routes as $route): ?>
                    <tr>
                        <td><?= $route['route_id'] ?></td>
                        <td><?= htmlspecialchars($route['start_point']) ?></td>
                        <td><?= htmlspecialchars($route['end_point']) ?></td>
                        <td>
                            <a class="action-btn edit-btn" href="manage_routes.php?edit_id=<?= $route['route_id'] ?>">Edit</a>
                            <a class="action-btn delete-btn" href="manage_routes.php?delete_id=<?= $route['route_id'] ?>" onclick="return confirm('Are you sure to delete this route?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="../admin/dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
</div>
</body>
</html>
