<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$foodId = isset($input['food_id']) ? (int)$input['food_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if ($foodId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$ok = addToCart($foodId, $quantity, $userId);

echo json_encode([
    'success' => (bool)$ok
]);


