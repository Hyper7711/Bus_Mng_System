<?php 
include '../db_connect.php';  

$success = "";
$error = "";

// ✅ Fetch all buses for dropdown
try {
    $busesQuery = $conn->query("SELECT bus_id, bus_no FROM buses");
    $buses = $busesQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ Could not fetch buses: " . $e->getMessage();
}

// ✅ Handle Adding Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $class = $_POST['class'];
    $bus_id = !empty($_POST['bus_id']) ? $_POST['bus_id'] : null;

    try {
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

// ✅ Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $sql = "DELETE FROM students WHERE student_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $delete_id]);
        $success = "🗑️ Student deleted successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error deleting: " . $e->getMessage();
    }
}

// ✅ Fetch Students with Bus Numbers
try {
    $stmt = $conn->query("
        SELECT s.student_id, s.name, s.class, s.bus_id, b.bus_no 
        FROM students s
        LEFT JOIN buses b ON s.bus_id = b.bus_id
        ORDER BY s.student_id DESC
    ");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ Error fetching students: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
        table {
            width: 70%;
            margin-top: 20px;
        }
        th {
            background-color: #f2f2f2;
        }
        .delete-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>➕ Add Student</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Add Student Form -->
    <form method="POST">
        <input type="hidden" name="add_student" value="1">

        <label>Student Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Class:</label><br>
        <input type="text" name="class" required><br><br>

        <label>Select Bus:</label><br>
        <select name="bus_id">
            <option value="">--Select Bus--</option>
            <?php
            if (!empty($buses)) {
                foreach ($buses as $bus) {
                    echo "<option value='{$bus['bus_id']}'>{$bus['bus_no']}</option>";
                }
            } else {
                echo "<option disabled>No buses found</option>";
            }
            ?>
        </select><br><br>

        <button type="submit">Add Student</button>
    </form>

    <br>
    <a href="dashboard.php"><button type="button">⬅ Back to Dashboard</button></a>

    <!-- Student List -->
    <h2>📋 Student List</h2>
    <?php if (!empty($students)) { ?>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Class</th>
                <th>Bus</th>
                <th>Action</th>
            </tr>
            <?php foreach ($students as $student) { ?>
                <tr>
                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['class']) ?></td>
                    <td><?= htmlspecialchars($student['bus_no'] ?? 'N/A') ?></td>
                    <td>
                        <a class="delete-btn" 
                           href="manage_student.php?delete_id=<?= $student['student_id'] ?>"
                           onclick="return confirm('Are you sure you want to delete this student?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No students found.</p>
    <?php } ?>
</body>
</html>
