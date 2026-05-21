<?php
include 'db_connect.php';
include __DIR__ . '/admin/ai/smart_search.php';

$search = new SmartRouteSearch($conn);

$suggestions = [];

if (isset($_GET['query'])) {
    $input = $_GET['query'];
    $suggestions = $search->fuzzySearch($input);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>AI Smart Search</title>

<style>
body {
    font-family: Arial;
    padding: 30px;
    background: #f1f5f9;
}

input {
    padding: 10px;
    width: 300px;
}

button {
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
}

.result {
    background: white;
    padding: 12px;
    margin: 10px 0;
    border-radius: 8px;
}
</style>
</head>

<body>

<h2>🤖 Smart Search (AI Powered)</h2>

<form method="GET">
    <input type="text" name="query" placeholder="Enter location (e.g. Nagpur)" required>
    <button type="submit">Search</button>
</form>

<?php if (!empty($suggestions)): ?>

<h3>Suggestions:</h3>

<?php foreach ($suggestions as $s): ?>
<div class="result">
📍 <?= htmlspecialchars($s['location']) ?>  
<br>
Match: <?= $s['similarity'] ?>%
</div>
<?php endforeach; ?>

<?php endif; ?>

</body>
</html>