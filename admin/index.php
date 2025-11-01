<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Quick stats
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalFoods = (int)$pdo->query("SELECT COUNT(*) FROM foods")->fetchColumn();
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
        .card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:16px}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}
        .nav a{margin-right:.5rem}
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php"><i class="fas fa-user-shield"></i> Admin</a>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link active">Dashboard</a>
                    <a href="categories.php" class="nav-link">Categories</a>
                    <a href="foods.php" class="nav-link">Foods</a>
                    <a href="orders.php" class="nav-link">Orders</a>
                </div>
                <div class="nav-actions">
                    <div class="user-menu">
                        <div class="dropdown">
                            <button class="dropdown-btn">
                                <i class="fas fa-user"></i>
                                <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-content">
                                <a href="logout.php">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
            </div>
        </nav>
    </header>

    <div class="container" style="padding:24px 0;">
        <div class="grid">
            <div class="card"><h3>Users</h3><p><?php echo $totalUsers; ?></p></div>
            <div class="card"><h3>Foods</h3><p><?php echo $totalFoods; ?></p></div>
            <div class="card"><h3>Orders</h3><p><?php echo $totalOrders; ?></p></div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>


