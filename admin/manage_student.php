<?php 
// Include database connection
include '../db_connect.php';  // change to 'db_connect.php' if file is in the same folder

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $class = $_POST['class'];
    $bus_id = $_POST['bus_id'];

    try {
        // Prepare and execute SQL query
        $sql = "INSERT INTO students (name, class, bus_id) VALUES (:name, :class, :bus_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':class' => $class,
            ':bus_id' => $bus_id
        ]);
        $success = "✅ Student added successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
</head>
<body>
    <h2>➕ Add Student</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Student Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Class:</label><br>
        <input type="text" name="class" required><br><br>

        <label>Bus ID:</label><br>
        <input type="number" name="bus_id"><br><br>

        <button type="submit">Add Student</button>
    </form>

    <br>
    <a href="dashboard.php"><button type="button">⬅ Back to Dashboard</button></a>
</body>
</html>
