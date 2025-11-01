<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $cartId = (int)($_POST['cart_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        updateCartQuantity($cartId, $qty);
    } elseif ($action === 'remove') {
        $cartId = (int)($_POST['cart_id'] ?? 0);
        removeFromCart($cartId);
    } elseif ($action === 'clear') {
        $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
        clearCart($userId);
    }
    redirect('cart.php');
}

$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$items = getCartItems($userId);
$total = getCartTotal($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ===== Cart Layout ===== */
        body { background: #f9fafb; }

        .cart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            padding: 1rem;
        }

        .cart-card, .cart-summary {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .cart-row {
            display: grid;
            grid-template-columns: 80px 1fr 140px 100px;
            align-items: center;
            gap: 1rem;
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-row:last-child { border-bottom: none; }

        .cart-row img {
            width: 80px; height: 80px;
            border-radius: 10px;
            object-fit: cover;
        }

        .cart-row strong { display: block; color: #333; }

        .cart-summary {
            padding: 20px;
            position: sticky;
            top: 80px;
            height: fit-content;
        }

        /* Buttons & Inputs */
        .cart-row form { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
        .cart-row input[type="number"] {
            width: 60px;
            text-align: center;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 6px;
            outline: none;
        }

        .cart-row input[type="number"]:focus { border-color: #ff6b35; }

        .btn {
            background: #ff6b35;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover { background: #e85c2a; }
        .btn-outline {
            background: transparent;
            border: 2px solid #ff6b35;
            color: #ff6b35;
        }
        .btn-outline:hover {
            background: #ff6b35;
            color: #fff;
        }
        .btn-sm { padding: 6px 10px; font-size: 0.9rem; }

        /* Summary Section */
        .cart-summary h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .cart-summary div {
            display: flex;
            justify-content: space-between;
            margin-bottom: .75rem;
            font-size: 1rem;
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #777;
        }

        /* Responsive Design */
        @media(max-width: 992px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
            .cart-summary {
                position: relative;
                top: auto;
                margin-top: 1.5rem;
            }
        }

        @media(max-width: 600px) {
            .cart-row {
                grid-template-columns: 70px 1fr;
                grid-template-rows: auto auto;
                gap: .75rem;
                padding: 12px;
            }
            .cart-row img { width: 70px; height: 70px; }
            .cart-row form { width: 100%; justify-content: space-between; }
            .cart-row strong { font-size: .95rem; }
            .btn, .btn-outline { padding: 6px 12px; font-size: .9rem; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php"><i class="fas fa-utensils"></i> <?php echo SITE_NAME; ?></a>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link active">Home</a>
                    <a href="menu.php" class="nav-link active">Menu</a>
                    <a href="about.php" class="nav-link active">About</a>
                    <a href="contact.php" class="nav-link active">Contact</a>
                    <a href="login.php" class="nav-link active">Login</a>
                    <!-- <a href="cart.php" class="nav-link active">Cart</a> -->
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
                            <!-- <a href="login.php" class="btn btn-outline">Login</a>
                            <a href="register.php" class="btn btn-primary">Sign Up</a> -->
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
            </div>
        </nav>
    </header>

    <!-- Cart Section -->
    <section style="padding:2rem 0;">
        <div class="container cart-grid">
            <div>
                <div class="cart-card">
                    <div class="cart-row" style="background:#f8f9fa;font-weight:600;">
                        <div>Item</div>
                        <div>Details</div>
                        <div>Qty</div>
                        <div>Subtotal</div>
                    </div>

                    <?php if (empty($items)): ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart" style="font-size:3rem;color:#ccc;"></i>
                            <p>Your cart is empty.</p>
                            <a href="menu.php" class="btn btn-primary">Shop Now</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <div class="cart-row">
                                <div>
                                    <img src="<?php echo $item['image'] ?: 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <div class="food-price" style="font-size:1.6rem;"><strong><?php echo formatPrice($item['price'] * $item['qty']); ?></strong></div>
                                    <form method="POST" style="margin-top:6px;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo (int)$item['id']; ?>">
                                        <button type="submit" class="btn btn-primary">Remove</button>
                                    </form>
                                </div>
                                <div>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo (int)$item['id']; ?>">
                                        <div style="display:flex;gap:.5rem;align-items:center;">
                                            <button type="button" class="btn btn-outline" onclick="updateQuantity(document.getElementById('qty-<?php echo (int)$item['id']; ?>'), -1)">-</button>
                                            <input id="qty-<?php echo (int)$item['id']; ?>" name="qty" type="number" min="1" value="<?php echo (int)$item['qty']; ?>">
                                            <button type="button" class="btn btn-outline" onclick="updateQuantity(document.getElementById('qty-<?php echo (int)$item['id']; ?>'), 1)">+</button>
                                        </div>
                                        <button type="submit" class="btn btn-primary" style="margin-top:6px;">Update</button>
                                    </form>
                                </div>
                                
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($items)): ?>
                <form method="POST" style="margin-top:1rem;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-primary">Clear Cart</button>
                </form>
                <?php endif; ?>
            </div>

            <?php if (!empty($items)): ?>
            <div>
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div><span>Subtotal</span><span><?php echo formatPrice($total); ?></span></div>
                    <div style="color:#666;"><span>Delivery</span><span>Free</span></div>
                    <hr style="margin:1rem 0;border:none;border-top:1px solid #eee;">
                    <div style="font-weight:600;"><span>Total</span><span><?php echo formatPrice($total); ?></span></div>
                    <a href="checkout.php" class="btn" style="width:100%;margin-top:1rem;<?php echo $total<=0?'pointer-events:none;opacity:.6;':''; ?>">Proceed to Checkout</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>
