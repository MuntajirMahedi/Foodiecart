<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            redirect('index.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .auth-card{background:#fff;padding:24px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .auth-field{width:100%;margin-top:6px}
        .alert{background:#ffe8e8;color:#b00020;padding:12px 16px;border-radius:8px;margin-bottom:1rem}
        .auth-wrap{max-width:480px;padding:40px 0}
    </style>
    </head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">
                        <i class="fas fa-utensils"></i>
                        <?php echo SITE_NAME; ?>
                    </a>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link active">Home</a>
                    <a href="menu.php" class="nav-link active">Menu</a>
                    <a href="about.php" class="nav-link active">About</a>
                    <a href="contact.php" class="nav-link active">Contact</a>
                    <a href="login.php" class="nav-link active">Login</a>
                </div>
                <div class="nav-actions">
                    <div class="search-box">
                        <form action="menu.php" method="GET">
                            <input type="text" name="q" placeholder="Search food..." class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
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
                            <!-- <a href="login.php" class="btn btn-outline">Login</a>
                            <a href="register.php" class="btn btn-primary">Sign Up</a> -->
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </nav>
    </header>
    <div class="container auth-wrap">
        <h2 class="section-title" style="margin-bottom:1rem;">Welcome back</h2>
        <p class="text-center" style="margin-bottom:1.5rem;">Login to continue</p>

        <?php if (!empty($errors)): ?>
            <div class="alert">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-card">
            <div class="mb-3">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="search-input auth-field" required>
            </div>
            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="search-input auth-field" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
            <p class="text-center mt-2">Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>
</html>


