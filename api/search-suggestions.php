<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$suggestions = [];

if ($q !== '') {
    $stmt = $pdo->prepare("SELECT id, name FROM foods WHERE is_available = 1 AND name LIKE ? ORDER BY name LIMIT 5");
    $term = "%$q%";
    $stmt->execute([$term]);
    $suggestions = $stmt->fetchAll();
}

echo json_encode(['suggestions' => $suggestions]);


