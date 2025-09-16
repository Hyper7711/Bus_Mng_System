<?php
include '../db_connect.php';

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bus_id = $_POST['bus_id'];
    $current_location = $_POST['current_location'];

    try {
        $sql = "INSERT INTO tracking (bus_id, current_location) VALUES (:bus_id, :current_location)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':bus_id' => $bus_id,
            ':current_location' => $current_location
        ]);
        $success = "✅ Location updated successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Fetch latest location per bus
$locations = $conn->query("
    SELECT b.bus_no, t.current_location, t.timestamp
    FROM buses b
    LEFT JOIN tracking t ON b.bus_id = t.bus_id
    WHERE t.track_id = (
        SELECT MAX(track_id) FROM tracking WHERE bus_id = b.bus_id
    )
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bus Tracking</title>
</head>
<body>
    <h2>🚌 Update Bus Location</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Select Bus:</label><br>
        <select name="bus_id" required>
            <option value="">--Select Bus--</option>
            <?php
            $buses = $conn->query("SELECT bus_id, bus_no FROM buses")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($buses as $bus) {
                echo "<option value='{$bus['bus_id']}'>{$bus['bus_no']}</option>";
            }
            ?>
        </select><br><br>

        <label>Current Location:</label><br>
        <input type="text" name="current_location" required><br><br>

        <button type="submit">Update Location</button>
    </form>

    <h2>📍 Latest Bus Locations</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Bus Number</th>
            <th>Current Location</th>
            <th>Last Updated</th>
        </tr>
        <?php
        foreach ($locations as $loc) {
            echo "<tr>
                    <td>{$loc['bus_no']}</td>
                    <td>{$loc['current_location']}</td>
                    <td>{$loc['timestamp']}</td>
                  </tr>";
        }
        ?>
    </table>

    <br>
    <a href="dashboard.php"><button type="button">⬅ Back to Dashboard</button></a>
</body>
</html>
