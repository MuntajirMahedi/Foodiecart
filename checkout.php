<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = (int)$_SESSION['user_id'];
$items = getCartItems($userId);
$total = getCartTotal($userId);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? 'COD';
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($items)) {
        $errors[] = 'Your cart is empty.';
    }
    if (!$address) {
        $errors[] = 'Delivery address is required.';
    }
    // Validate phone number
if (!$phone) {
    $errors[] = 'Phone number is required.';
} else {
    // Normalize phone (remove spaces, dashes, parentheses)
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

    // Remove leading 0 or 91 if present
    if (preg_match('/^(0|91)/', $cleanPhone)) {
        $cleanPhone = preg_replace('/^(0|91)/', '', $cleanPhone);
    }

    // Must be exactly 10 digits after cleaning
    if (strlen($cleanPhone) !== 10) {
        $errors[] = 'Phone number must be 10 digits.';
    } else {
        // Prepend +91 for storage or display
        $phone = '+91' . $cleanPhone;
    }
}
    if (empty($errors)) {
        $result = createOrder($userId, $total, $paymentMethod, $address, $phone);
        if ($result['success']) {
            redirect('orders.php?placed=1');
        } else {
            $errors[] = $result['message'] ?? 'Failed to place order.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .grid{display:grid;grid-template-columns:1.5fr 1fr;gap:2rem}
        .card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:16px}
        @media(max-width:900px){.grid{grid-template-columns:1fr}}
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
                    <a href="cart.php" class="nav-link">Cart</a>
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
        <div class="container grid">
            <div>
                <div class="card">
                    <h3 class="mb-2">Delivery Details</h3>
                    <?php if (!empty($errors)): ?>
                        <div style="background:#ffe8e8;color:#b00020;padding:12px 16px;border-radius:8px;margin-bottom:1rem;">
                            <?php echo implode('<br>', $errors); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" class="search-input" style="width:100%;height:120px;margin-top:6px;" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
    <label for="phone">Phone (India only)</label>
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="background:#f1f1f1;padding:8px 12px;border-radius:8px;">+91</span>
        <input type="text" 
               name="phone" 
               id="phone" 
               class="search-input" 
               style="flex:1;margin-top:6px;" 
               placeholder="Enter 10-digit mobile number"
               maxlength="10"
               pattern="[0-9]{10}"
               title="Please enter a valid 10-digit mobile number"
               required
               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
               value="<?php echo htmlspecialchars(preg_replace('/^\+91/', '', $_POST['phone'] ?? '')); ?>">
    </div>
</div>
                        <div class="mb-3">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="search-input" style="width:100%;margin-top:6px;">
                                <option value="COD" <?php echo (($_POST['payment_method'] ?? '')==='COD')?'selected':''; ?>>Cash on Delivery</option>
                                <option value="CARD" <?php echo (($_POST['payment_method'] ?? '')==='CARD')?'selected':''; ?>>Card</option>
                                <option value="UPI" <?php echo (($_POST['payment_method'] ?? '')==='UPI')?'selected':''; ?>>UPI</option>
                                <option value="PAYPAL" <?php echo (($_POST['payment_method'] ?? '')==='PAYPAL')?'selected':''; ?>>PayPal</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </form>
                </div>
            </div>
            <div>
                <div class="card">
                    <h3 class="mb-2">Order Summary</h3>
                    <?php if (empty($items)): ?>
                        <p>Your cart is empty.</p>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                                <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo (int)$item['qty']; ?></span>
                                <span><?php echo formatPrice($item['price'] * $item['qty']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr style="border:none;border-top:1px solid #eee;margin:1rem 0;">
                        <div style="display:flex;justify-content:space-between;font-weight:600;">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>


