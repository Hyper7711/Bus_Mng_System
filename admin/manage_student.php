<?php
include '../db_connect.php';

$success = "";
$error = "";

// ✅ Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = :id");
        $stmt->execute([':id' => $delete_id]);
        $success = "🗑️ Student deleted successfully!";
    } catch (PDOException $e) {
        $error = "❌ Error deleting student: " . $e->getMessage();
    }
}

// ✅ Handle Add/Edit Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'] ?? null;
    $name       = trim($_POST['name']);
    $class      = trim($_POST['class']);

    if ($student_id) { // Edit
        try {
            $stmt = $conn->prepare("UPDATE students SET name=:name, class=:class WHERE student_id=:student_id");
            $stmt->execute([
                ':name' => $name,
                ':class' => $class,
                ':student_id' => $student_id
            ]);
            $success = "✏️ Student updated successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error updating student: " . $e->getMessage();
        }
    } else { // Add
        try {
            $stmt = $conn->prepare("INSERT INTO students (name, class) VALUES (:name, :class)");
            $stmt->execute([
                ':name' => $name,
                ':class' => $class
            ]);
            $success = "✅ Student added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding student: " . $e->getMessage();
        }
    }
}

// ✅ Fetch students
$students = $conn->query("SELECT * FROM students ORDER BY student_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// If editing
$edit_student = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id=:id");
    $stmt->execute([':id' => $edit_id]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Students</title>
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
        max-width: 900px;
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
    table { width: 100%; border-collapse: collapse; min-width: 600px; }
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
    <h2>👨‍🎓 Manage Students</h2>

    <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

    <!-- Add/Edit Student Form -->
    <form method="POST">
        <input type="hidden" name="student_id" value="<?= $edit_student['student_id'] ?? '' ?>">
        <input type="text" name="name" placeholder="Student Name" value="<?= $edit_student['name'] ?? '' ?>" required>
        <input type="text" name="class" placeholder="Class" value="<?= $edit_student['class'] ?? '' ?>" required>
        <button type="submit"><?= isset($edit_student) ? "Update Student" : "Add Student" ?></button>
        <?php if(isset($edit_student)): ?>
            <a href="manage_student.php"><button type="button">Cancel Edit</button></a>
        <?php endif; ?>
    </form>

    <!-- Search -->
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="🔍 Search students..." onkeyup="searchTable()">
    </div>

    <!-- Student List -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= $student['student_id'] ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['class']) ?></td>
                        <td>
                            <a class="action-btn edit-btn" href="manage_student.php?edit_id=<?= $student['student_id'] ?>">Edit</a>
                            <a class="action-btn delete-btn" href="manage_student.php?delete_id=<?= $student['student_id'] ?>" onclick="return confirm('Are you sure to delete this student?');">Delete</a>
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
