<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = (int)$_SESSION['user_id'];
$orders = getUserOrders($userId);
$placed = isset($_GET['placed']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .order-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:16px;margin-bottom:1rem}
        .order-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem}
        .badge{background:#f1f3f5;border-radius:999px;padding:4px 10px;font-size:12px}
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php"><i class="fas fa-utensils"></i> <?php echo SITE_NAME; ?></a>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="menu.php" class="nav-link">Menu</a>
                    <a href="about.php" class="nav-link">About</a>
                    <a href="contact.php" class="nav-link">Contact</a>
                    <a href="orders.php" class="nav-link active">My Orders</a>
                </div>
                <div class="nav-actions">
                    <div class="cart-icon">
                        <a href="cart.php" class="cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </div>
                    <div class="user-menu">
                        <?php if (isLoggedIn()): ?>
                            <div class="dropdown">
                                <button class="dropdown-btn">
                                    <i class="fas fa-user"></i>
                                    <?php echo $_SESSION['user_name']; ?>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-content">
                                    <a href="orders.php">My Orders</a>
                                    <a href="logout.php">Logout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline">Login</a>
                            <a href="register.php" class="btn btn-primary">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
            </div>
        </nav>
    </header>

    <section class="categories" style="padding-top:2rem;">
        <div class="container">
            <h2 class="section-title">My Orders</h2>
            <?php if ($placed): ?>
                <div style="background:#e8fff1;color:#0f7a3f;padding:12px 16px;border-radius:8px;margin-bottom:1rem;">Order placed successfully!</div>
            <?php endif; ?>
            <?php if (empty($orders)): ?>
                <p>You have no orders yet.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-head">
                            <div>
                                <strong>Order #<?php echo (int)$order['id']; ?></strong>
                                <div style="color:#666;font-size:14px;">Placed on <?php echo htmlspecialchars($order['created_at']); ?></div>
                            </div>
                            <span class="badge"><?php echo htmlspecialchars($order['status']); ?></span>
                        </div>
                        <div style="margin:.5rem 0;">Total: <strong><?php echo formatPrice($order['total_amount']); ?></strong></div>
                        <div style="margin:.5rem 0;color:#666;">Payment: <?php echo htmlspecialchars($order['payment_method']); ?></div>
                        <?php $items = getOrderItems($order['id']); ?>
                        <div style="border-top:1px solid #eee;margin-top:.75rem;padding-top:.75rem;">
                            <?php foreach ($items as $it): ?>
                                <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
                                    <span><?php echo htmlspecialchars($it['name']); ?> x <?php echo (int)$it['qty']; ?></span>
                                    <span><?php echo formatPrice($it['price'] * $it['qty']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>


