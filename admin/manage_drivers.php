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
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);

    // If editing
    if (isset($_POST['driver_id']) && !empty($_POST['driver_id'])) {
        $driver_id = $_POST['driver_id'];
        try {
            $stmt = $conn->prepare("UPDATE drivers SET name=:name, contact=:contact WHERE driver_id=:driver_id");
            $stmt->execute([
                ':name' => $name,
                ':contact' => $contact,
                ':driver_id' => $driver_id
            ]);
            $success = "✏️ Driver updated successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error updating driver: " . $e->getMessage();
        }
    } else { // Add new driver
        try {
            $stmt = $conn->prepare("INSERT INTO drivers (name, contact) VALUES (:name, :contact)");
            $stmt->execute([
                ':name' => $name,
                ':contact' => $contact
            ]);
            $success = "✅ Driver added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding driver: " . $e->getMessage();
        }
    }
}

// ✅ Fetch drivers
$drivers = $conn->query("SELECT * FROM drivers ORDER BY driver_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// If editing, fetch the driver details
$edit_driver = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM drivers WHERE driver_id=:id");
    $stmt->execute([':id' => $edit_id]);
    $edit_driver = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Drivers</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        table { width: 60%; margin-top: 20px; }
        th { background-color: #f2f2f2; }
        .action-btn { padding: 5px 10px; border-radius: 5px; text-decoration: none; color: white; }
        .delete-btn { background-color: red; }
        .edit-btn { background-color: orange; }
    </style>
</head>
<body>
    <h2>🚗 Manage Drivers</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Add/Edit Driver Form -->
    <form method="POST">
        <input type="hidden" name="driver_id" value="<?= $edit_driver['driver_id'] ?? '' ?>">
        <label>Driver Name:</label><br>
        <input type="text" name="name" value="<?= $edit_driver['name'] ?? '' ?>" required><br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact" value="<?= $edit_driver['contact'] ?? '' ?>" required><br><br>

        <button type="submit"><?= isset($edit_driver) ? "Update Driver" : "Add Driver" ?></button>
        <?php if(isset($edit_driver)): ?>
            <a href="manage_drivers.php"><button type="button">Cancel Edit</button></a>
        <?php endif; ?>
    </form>

    <br>

    <!-- Driver List -->
    <h2>📋 Driver List</h2>
    <?php if (!empty($drivers)) { ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($drivers as $driver): ?>
            <tr>
                <td><?= $driver['driver_id'] ?></td>
                <td><?= htmlspecialchars($driver['name']) ?></td>
                <td><?= htmlspecialchars($driver['contact']) ?></td>
                <td>
                    <a class="action-btn edit-btn" href="manage_drivers.php?edit_id=<?= $driver['driver_id'] ?>">Edit</a>
                    <a class="action-btn delete-btn" href="manage_drivers.php?delete_id=<?= $driver['driver_id'] ?>" onclick="return confirm('Are you sure to delete this driver?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php } else { ?>
        <p>No drivers found.</p>
    <?php } ?>
    <br>
    <a href="dashboard.php"><button type="button">⬅ Back to Dashboard</button></a>
</body>
</html>
