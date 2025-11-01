<?php
// create_admin.php — one-time use. Delete after use!

require_once __DIR__ . '/config.php'; // path adjust if needed

$username = 'admin';
$passwordPlain = 'Mahedi@477';
$email = 'admin@foodiecart.com'; // optional, if your admin table has email column

try {
    // create secure hash
    $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

    // Check if admin table uses columns: id, username, password_hash
    // Adjust columns if your table differs (e.g., 'password' instead of 'password_hash')
    $stmt = $pdo->prepare("INSERT INTO admin (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $passwordHash]);

    echo "Admin created successfully. Username: {$username}\n";
    echo "Password (plain): {$passwordPlain}\n";
    echo "Password hash stored in DB.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
