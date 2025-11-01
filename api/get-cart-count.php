<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$items = getCartItems($userId);
$count = 0;
foreach ($items as $item) {
    $count += (int)$item['qty'];
}

echo json_encode(['count' => $count]);


