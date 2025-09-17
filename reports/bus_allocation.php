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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Allocation</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #dce35b, #45b649);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 30px;
        }

        .card {
            width: 100%;
            max-width: 1100px;
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            margin: 0 0 15px;
            color: #2c3e50;
        }

        .message {
            font-weight: 600;
            margin: 10px 0;
            text-align: center;
            font-size: 1rem;
            border-radius: 8px;
            padding: 10px;
        }

        .success { background: #e8fdf0; color: #1e7b34; border: 1px solid #a9e5b9; }
        .error { background: #fde8e8; color: #b91c1c; border: 1px solid #f5b5b5; }

        .search-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        .search-box input {
            padding: 8px 10px;
            width: 250px;
            border-radius: 6px;
            border: 1px solid #ccc;
            outline: none;
        }

        .table-container {
            overflow-x: auto;
            max-height: 65vh;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            font-size: 0.95rem;
        }

        th {
            background: #f9fafb;
            font-weight: bold;
            border-bottom: 2px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        tr:nth-child(even) td { background: #fcfcfc; }
        tr:hover td { background: #f1f5f9; transition: 0.2s; }

        select {
            padding: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        button:hover {
            background: #0056b3;
            transform: scale(1.05);
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background: #ddd;
            color: #333;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

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
        <h2>🚌 Student Bus Allocation</h2>

        <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

        <?php if (!empty($students)) { ?>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search students..." onkeyup="searchTable()">
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Assigned Bus</th>
                            <th>Change Bus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) { ?>
                            <tr>
                                <td><?= $student['student_id'] ?></td>
                                <td><?= htmlspecialchars($student['student_name']) ?></td>
                                <td><?= htmlspecialchars($student['class']) ?></td>
                                <td><?= $student['bus_no'] ?? "<em>Not assigned</em>" ?></td>
                                <td>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                        <select name="bus_id">
                                            <option value="">--Select--</option>
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
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p style="text-align:center;">No students found.</p>
        <?php } ?>

        <a href="../admin/dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
