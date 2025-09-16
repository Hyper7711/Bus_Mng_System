<?php
include '../db_connect.php';

$success = "";
$error = "";

// ✅ Handle bus assignment change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_bus'])) {
    $student_id = $_POST['student_id'];
    $bus_id = !empty($_POST['bus_id']) ? $_POST['bus_id'] : null;

    try {
        $stmt = $conn->prepare("UPDATE students SET bus_id = :bus_id WHERE student_id = :student_id");
        $stmt->execute([
            ':bus_id' => $bus_id,
            ':student_id' => $student_id
        ]);
        $success = "✅ Bus allocation updated successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error updating allocation: " . $e->getMessage();
    }
}

// ✅ Fetch all students with bus info
try {
    $stmt = $conn->query("
        SELECT s.student_id, s.name AS student_name, s.class, b.bus_no, b.bus_id
        FROM students s
        LEFT JOIN buses b ON s.bus_id = b.bus_id
        ORDER BY s.student_id ASC
    ");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ Error fetching students: " . $e->getMessage();
}

// ✅ Fetch all buses for dropdown
try {
    $stmt = $conn->query("SELECT bus_id, bus_no FROM buses ORDER BY bus_no ASC");
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error .= "❌ Error fetching buses: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bus Allocation</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        table { width: 80%; margin-top: 20px; }
        th { background-color: #f2f2f2; }
        select { padding: 5px; }
        button { padding: 5px 10px; margin: 2px; }
    </style>
</head>
<body>
    <h2>🚌 Student Bus Allocation</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <?php if (!empty($students)) { ?>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Class</th>
                <th>Assigned Bus</th>
                <th>Change Bus</th>
            </tr>
            <?php foreach ($students as $student) { ?>
                <tr>
                    <td><?= $student['student_id'] ?></td>
                    <td><?= htmlspecialchars($student['student_name']) ?></td>
                    <td><?= htmlspecialchars($student['class']) ?></td>
                    <td><?= $student['bus_no'] ?? "Not assigned" ?></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                            <select name="bus_id">
                                <option value="">--Select Bus--</option>
                                <?php foreach ($buses as $bus): ?>
                                    <option value="<?= $bus['bus_id'] ?>"
                                        <?= $student['bus_id'] == $bus['bus_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($bus['bus_no']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_bus">Assign</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No students found.</p>
    <?php } ?>

    <br>
    <a href="../admin/dashboard.php"><button type="button">⬅ Back to Dashboard</button></a>
</body>
</html>
