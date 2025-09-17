<?php
include '../db_connect.php';

$success = "";
$error = "";

// ✅ Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM drivers WHERE driver_id = :id");
        $stmt->execute([':id' => $delete_id]);
        $success = "🗑️ Driver deleted successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error deleting driver: " . $e->getMessage();
    }
}

// ✅ Handle Add/Edit Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $driver_id = $_POST['driver_id'] ?? null;
    $name      = trim($_POST['name']);

    if ($driver_id) { // Edit
        try {
            $stmt = $conn->prepare("UPDATE drivers SET name=:name WHERE driver_id=:driver_id");
            $stmt->execute([
                ':name' => $name,
                ':driver_id' => $driver_id
            ]);
            $success = "✏️ Driver updated successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error updating driver: " . $e->getMessage();
        }
    } else { // Add
        try {
            $stmt = $conn->prepare("INSERT INTO drivers (name) VALUES (:name)");
            $stmt->execute([
                ':name' => $name
            ]);
            $success = "✅ Driver added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding driver: " . $e->getMessage();
        }
    }
}

// ✅ Fetch drivers
$drivers = $conn->query("SELECT * FROM drivers ORDER BY driver_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// If editing
$edit_driver = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM drivers WHERE driver_id=:id");
    $stmt->execute([':id' => $edit_id]);
    $edit_driver = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Drivers</title>
<style>
    body {
        margin: 0;
        font-family: "Segoe UI", sans-serif;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
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
    form input, form select, form button { padding: 8px; margin: 5px 0; border-radius: 6px; border: 1px solid #ccc; width: 100%; box-sizing: border-box; }
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
    <h2>🚗 Manage Drivers</h2>

    <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

    <!-- Add/Edit Driver Form -->
    <form method="POST">
        <input type="hidden" name="driver_id" value="<?= $edit_driver['driver_id'] ?? '' ?>">
        <input type="text" name="name" placeholder="Driver Name" value="<?= $edit_driver['name'] ?? '' ?>" required>
        <button type="submit"><?= isset($edit_driver) ? "Update Driver" : "Add Driver" ?></button>
        <?php if(isset($edit_driver)): ?>
            <a href="manage_drivers.php"><button type="button">Cancel Edit</button></a>
        <?php endif; ?>
    </form>

    <!-- Search -->
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="🔍 Search drivers..." onkeyup="searchTable()">
    </div>

    <!-- Driver List -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drivers as $driver): ?>
                    <tr>
                        <td><?= $driver['driver_id'] ?></td>
                        <td><?= htmlspecialchars($driver['name']) ?></td>
                        <td>
                            <a class="action-btn edit-btn" href="manage_drivers.php?edit_id=<?= $driver['driver_id'] ?>">Edit</a>
                            <a class="action-btn delete-btn" href="manage_drivers.php?delete_id=<?= $driver['driver_id'] ?>" onclick="return confirm('Are you sure to delete this driver?');">Delete</a>
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
