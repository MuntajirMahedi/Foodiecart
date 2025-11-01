<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (isAdmin()) {
    redirect('index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $errors[] = 'Username and password are required.';
    } else {
        $res = loginAdmin($username, $password);
        if ($res['success']) {
            redirect('index.php');
        } else {
            $errors[] = $res['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>.card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:20px;max-width:420px;margin:60px auto}</style>
</head>
<body>
    <div class="card">
        <h3 class="mb-2">Admin Login</h3>
        <?php if (!empty($errors)): ?>
        <div style="background:#ffe8e8;color:#b00020;padding:10px;border-radius:8px;margin-bottom:10px;">
            <?php echo implode('<br>', $errors); ?>
        </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="search-input" style="width:100%;margin-top:6px;" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="search-input" style="width:100%;margin-top:6px;" required>
            </div>
            <button class="btn btn-primary" style="width:100%;">Login</button>
        </form>
        <div class="mt-2">
            <a href="../index.php" class="btn btn-outline" style="width:100%;text-align:center;">Back to site</a>
        </div>
    </div>
</body>
</html>


