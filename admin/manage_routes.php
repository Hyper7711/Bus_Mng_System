<?php
include '../db_connect.php';
include __DIR__ . '/ai/smart_search.php';

$aiResults = [];
$source = "";
$destination = "";

if (isset($_GET['source']) && isset($_GET['destination'])) {

    $search = new SmartRouteSearch($conn);

    // ✅ Fuzzy correction
    $sourceSuggestions = $search->fuzzySearch($_GET['source']);
    $destinationSuggestions = $search->fuzzySearch($_GET['destination']);

    $source = !empty($sourceSuggestions) ? $sourceSuggestions[0]['location'] : $_GET['source'];
    $destination = !empty($destinationSuggestions) ? $destinationSuggestions[0]['location'] : $_GET['destination'];

    // 🔥 Get AI results
    $aiResults = $search->recommendRoute(
        $source,
        $destination,
        date('Y-m-d')
    );
}

$success = "";
$error = "";

// DELETE
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

// ADD / EDIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $route_id    = $_POST['route_id'] ?? null;
    $start_point = trim($_POST['start_point']);
    $end_point   = trim($_POST['end_point']);

    if ($route_id) {
        $stmt = $conn->prepare("UPDATE routes SET start_point=:start_point, end_point=:end_point WHERE route_id=:route_id");
        $stmt->execute([
            ':start_point' => $start_point,
            ':end_point'   => $end_point,
            ':route_id'    => $route_id
        ]);
        $success = "✏️ Route updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO routes (start_point, end_point) VALUES (:start_point, :end_point)");
        $stmt->execute([
            ':start_point' => $start_point,
            ':end_point'   => $end_point
        ]);
        $success = "✅ Route added successfully!";
    }
}

// FETCH ROUTES
$routes = $conn->query("SELECT * FROM routes ORDER BY route_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// EDIT FETCH
$edit_route = null;
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM routes WHERE route_id=:id");
    $stmt->execute([':id' => $_GET['edit_id']]);
    $edit_route = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Routes</title>

<style>
body { margin:0; font-family:"Segoe UI"; background:linear-gradient(135deg,#4facfe,#00f2fe); padding:30px;}
.card { max-width:800px; margin:auto; background:white; padding:25px; border-radius:16px; }
h2 { text-align:center; }
.result-box { background:#fff; padding:12px; margin:10px 0; border-left:5px solid #007bff; border-radius:8px; }
</style>
</head>

<body>
<div class="card">

<h2>🗺️ Manage Routes</h2>

<!-- 🤖 AI SEARCH -->
<div style="background:#eef6ff; padding:15px; border-radius:10px; margin-bottom:20px;">
    <h3>🤖 Smart Route Finder</h3>

    <form method="GET" style="display:flex; gap:10px;">
        <input type="text" name="source" placeholder="From" required>
        <input type="text" name="destination" placeholder="To" required>
        <button type="submit">Find Best Route</button>
    </form>
</div>

<!-- 🔥 SHOW SEARCH INFO -->
<?php if (!empty($source) && !empty($destination)): ?>
<p>Showing results for: <strong><?= htmlspecialchars($source) ?></strong> → <strong><?= htmlspecialchars($destination) ?></strong></p>
<?php endif; ?>

<!-- 🤖 AI RESULTS -->
<?php if (!empty($aiResults)): ?>
<h3>🤖 AI Recommended Routes</h3>

<?php foreach ($aiResults as $r): ?>
<div class="result-box">

🚌 <strong><?= $r['bus_name'] ?></strong><br>
📍 <?= $r['start_point'] ?> → <?= $r['end_point'] ?><br>
💺 Seats: <?= $r['available_seats'] ?><br>
⭐ Score: <?= $r['ai_score'] ?><br>
👉 <?= $r['recommendation'] ?>

</div>
<?php endforeach; ?>

<?php endif; ?>

<!-- ⚠️ NO RESULT -->
<?php if (isset($_GET['source']) && empty($aiResults)): ?>
<div style="background:#fff3cd; padding:12px; border-radius:8px;">
⚠️ No matching routes found. Try different locations.
</div>
<?php endif; ?>

<hr>

<!-- FORM -->
<form method="POST">
<input type="hidden" name="route_id" value="<?= $edit_route['route_id'] ?? '' ?>">
<input type="text" name="start_point" placeholder="Start Point" value="<?= $edit_route['start_point'] ?? '' ?>" required>
<input type="text" name="end_point" placeholder="End Point" value="<?= $edit_route['end_point'] ?? '' ?>" required>
<button type="submit"><?= isset($edit_route) ? "Update Route" : "Add Route" ?></button>
</form>

<br>

<!-- ROUTE TABLE -->
<table border="1" width="100%" cellpadding="10">
<tr>
<th>ID</th>
<th>Start</th>
<th>End</th>
<th>Actions</th>
</tr>

<?php foreach ($routes as $route): ?>
<tr>
<td><?= $route['route_id'] ?></td>
<td><?= $route['start_point'] ?></td>
<td><?= $route['end_point'] ?></td>
<td>
<a href="?edit_id=<?= $route['route_id'] ?>">Edit</a> |
<a href="?delete_id=<?= $route['route_id'] ?>">Delete</a>
</td>
</tr>
<?php endforeach; ?>

</table>

</div>
</body>
</html>