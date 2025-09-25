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
    // collect POST safely
    $driver_id = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;
    $name      = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone     = isset($_POST['phone']) ? trim($_POST['phone']) : '';

    // If editing, fetch current license_file (so we can keep it if no new file uploaded)
    $current_license = "";
    if ($driver_id) {
        try {
            $stmt = $conn->prepare("SELECT license_file FROM drivers WHERE driver_id = :id");
            $stmt->execute([':id' => $driver_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_license = $row['license_file'] ?? "";
        } catch (PDOException $e) {
            // non-fatal, keep current_license empty
        }
    }

    // prepare license value default to current (for update) or empty (for new)
    $license_file_db = $current_license;

    // Handle license upload if a file is provided
    if (!empty($_FILES['license']['name'])) {
        $upload_dir_rel = 'uploads/licenses/';              // path stored in DB (relative to project root)
        $upload_dir_fs  = __DIR__ . '/../' . $upload_dir_rel; // filesystem path where file will be saved

        if (!is_dir($upload_dir_fs)) {
            mkdir($upload_dir_fs, 0777, true);
        }

        $original_name = basename($_FILES['license']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','pdf']; // allowed file types
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if (!in_array($ext, $allowed)) {
            $error .= "❌ Invalid file type. Allowed: jpg, jpeg, png, pdf. ";
        } elseif ($_FILES['license']['size'] > $maxSize) {
            $error .= "❌ File too large. Max 2MB. ";
        } else {
            $file_name = time() . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '_', $original_name);
            $target_fs = $upload_dir_fs . $file_name;
            $target_db = $upload_dir_rel . $file_name; // what we store in DB

            if (move_uploaded_file($_FILES['license']['tmp_name'], $target_fs)) {
                $license_file_db = $target_db;
            } else {
                $error .= "❌ Failed to move uploaded file. ";
            }
        }
    }

    // If there were upload validation errors, skip DB write.
    if (empty($error)) {
        // If editing
        if ($driver_id) {
            try {
                $stmt = $conn->prepare("UPDATE drivers SET name=:name, phone=:phone, license_file=:license WHERE driver_id=:driver_id");
                $stmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':license' => $license_file_db,
                    ':driver_id' => $driver_id
                ]);
                $success = "✏️ Driver updated successfully!";
            } catch (PDOException $e) {
                $error = "❌ Error updating driver: " . $e->getMessage();
            }
        } else { // Add new driver
            try {
                $stmt = $conn->prepare("INSERT INTO drivers (name, phone, license_file) VALUES (:name, :phone, :license)");
                $stmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':license' => $license_file_db
                ]);
                $success = "✅ Driver added successfully!";
            } catch (PDOException $e) {
                $error = "❌ Error adding driver: " . $e->getMessage();
            }
        }
    }
}

// ✅ Fetch drivers
$drivers = $conn->query("SELECT * FROM drivers ORDER BY driver_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// If editing (for prefill)
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
        body { font-family: Arial, sans-serif; background: #e3f2fd; padding: 20px; }
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        table { width: 90%; margin-top: 20px; }
        th { background-color: #f2f2f2; }
        .action-btn { padding: 5px 10px; border-radius: 5px; text-decoration: none; color: white; }
        .delete-btn { background-color: red; }
        .edit-btn { background-color: orange; }
        .message { font-weight: 600; padding: 8px; border-radius: 6px; display: inline-block; margin-bottom: 10px; }
        .success { background: #e8fdf0; color: #1e7b34; border: 1px solid #a9e5b9; }
        .error { background: #fde8e8; color: #b91c1c; border: 1px solid #f5b5b5; }
        .form-row { margin-bottom: 12px; }
        input[type="text"], input[type="file"] { padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 320px; }
        button { padding: 8px 12px; border-radius: 6px; border: none; background: #007bff; color: white; cursor: pointer; }
        button:hover { background: #0056b3; }
        .back-btn { margin-top: 12px; display: inline-block; background: #ddd; color:#333; padding:8px 12px; border-radius:6px; text-decoration:none; }
    </style>
</head>
<body>
    <h2>👨‍✈️ Manage Drivers</h2>

    <?php if (!empty($success)) echo "<div class='message success'>$success</div><br>"; ?>
    <?php if (!empty($error)) echo "<div class='message error'>$error</div><br>"; ?>

    <!-- Add/Edit Driver Form -->
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="driver_id" value="<?= htmlspecialchars($edit_driver['driver_id'] ?? '') ?>">

        <div class="form-row">
            <label>Driver Name:</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($edit_driver['name'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <label>Phone Number:</label><br>
            <input type="text" name="phone" value="<?= htmlspecialchars($edit_driver['phone'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <label>Upload License (jpg/png/pdf, max 2MB):</label><br>
            <input type="file" name="license">
            <?php if (!empty($edit_driver['license_file'])): ?>
                <div>Current License:
                    <!-- link adjusted so it works from admin/ page -->
                    <a href="<?= htmlspecialchars('../' . $edit_driver['license_file']) ?>" target="_blank">View</a>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit"><?= isset($edit_driver) ? "Update Driver" : "Add Driver" ?></button>
        <?php if(isset($edit_driver)): ?>
            <a class="back-btn" href="manage_drivers.php">Cancel Edit</a>
        <?php endif; ?>
    </form>

    <br>

    <!-- Driver List -->
    <h2>📋 Driver List</h2>
    <?php if (!empty($drivers)) { ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Driver Name</th>
                <th>Phone</th>
                <th>License</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($drivers as $driver): ?>
            <tr>
                <td><?= htmlspecialchars($driver['driver_id']) ?></td>
                <td><?= htmlspecialchars($driver['name']) ?></td>
                <td><?= htmlspecialchars($driver['phone'] ?? '') ?></td>
                <td>
                    <?php if (!empty($driver['license_file'])): ?>
                        <a href="<?= htmlspecialchars('../' . $driver['license_file']) ?>" target="_blank">View License</a>
                    <?php else: ?>
                        No file
                    <?php endif; ?>
                </td>
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
</body>
</html>
