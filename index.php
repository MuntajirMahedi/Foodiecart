<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Get featured foods (latest 8)
$featuredFoods = getAllFoods(null, 8);
$categories = getAllCategories();
// Dynamic stats
$totalCategories = count($categories);
$totalFoods = (int)$pdo->query("SELECT COUNT(*) FROM foods WHERE is_available = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Delicious Food Delivered</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
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
                                    <!-- <a href="profile.php">Profile</a> -->
                                    <a href="orders.php">My Orders</a>
                                    <a href="logout.php">Logout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- <a href="login.php" class="btn btn-outline">Login</a> -->
                            <!-- <a href="register.php" class="btn btn-primary">Sign Up</a> -->
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile-only search -->
    <div class="mobile-search">
        <form action="menu.php" method="GET">
            <input type="text" name="q" placeholder="Search food..." class="search-input">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Hero Section -->
<section class="hero">
  <div class="hero-content">
    <h1>Delicious Food Delivered to Your Door</h1>
    <p>Order from the best restaurants in your area and enjoy fresh, tasty meals at home</p>
    <div class="hero-actions">
      <a href="menu.php" class="btn btn-primary btn-large">Order Now</a>
      <a href="#categories" class="btn btn-outline btn-large">Browse Menu</a>
    </div>
  </div>
  <!-- <div class="hero-image">
    Optional image inside hero
    <img src="assets/images/hero-food.jpg" alt="Delicious Food">
  </div> -->
</section>

    <!-- Stats Bar -->
    <!-- <section style="background:#fff;">
        <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;padding:2rem 0;">
            <div style="background:#f8f9fa;border-radius:12px;padding:18px;text-align:center;">
                <div style="font-size:2rem;color:#ff6b35;font-weight:700;"><?php echo $totalFoods; ?></div>
                <div style="color:#666;">Available Foods</div>
            </div>
            <div style="background:#f8f9fa;border-radius:12px;padding:18px;text-align:center;">
                <div style="font-size:2rem;color:#ff6b35;font-weight:700;"><?php echo $totalCategories; ?></div>
                <div style="color:#666;">Categories</div>
            </div>
            <div style="background:#f8f9fa;border-radius:12px;padding:18px;text-align:center;">
                <div style="font-size:2rem;color:#ff6b35;font-weight:700;">30 min</div>
                <div style="color:#666;">Avg Delivery</div>
            </div>
        </div>
    </section> -->

    <!-- Categories Section -->
    <section id="categories" class="categories">
        <div class="container">
            <h2 class="section-title">Food Categories</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <a href="category.php?slug=<?php echo $category['slug']; ?>">
                        <div class="category-image">
                            <img src="<?php echo $category['image'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo $category['name']; ?>">
                        </div>
                        <h3><?php echo $category['name']; ?></h3>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Foods Section -->
    <section class="featured-foods">
        <div class="container">
            <h2 class="section-title">Featured Foods</h2>
            <div class="foods-grid">
                <?php foreach ($featuredFoods as $food): ?>
                <div class="food-card">
                    <div class="food-image">
                        <img src="<?php echo $food['image'] ?: 'assets/images/hero-food.jpg'; ?>" 
                             alt="<?php echo $food['name']; ?>">
                        <div class="food-overlay">
                            <button class="btn btn-primary add-to-cart" data-food-id="<?php echo $food['id']; ?>">
                                <i class="fas fa-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                    <div class="food-content">
                        <h3><?php echo $food['name']; ?></h3>
                        <p class="food-category"><?php echo $food['category_name']; ?></p>
                        <p class="food-description"><?php echo substr($food['description'], 0, 80) . '...'; ?></p>
                        <div class="food-footer">
                            <span class="food-price"><?php echo formatPrice($food['price']); ?></span>
                            <a href="food.php?id=<?php echo $food['id']; ?>" class="btn btn-outline btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="menu.php" class="btn btn-primary">View All Foods</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Get your food delivered in 30 minutes or less</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Fresh Food</h3>
                    <p>Only the freshest ingredients used in every dish</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Easy Payment</h3>
                    <p>Multiple payment options for your convenience</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock customer support for any issues</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Delivering delicious food to your doorstep with love and care.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="help.php">Help Center</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="refund.php">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@foodiecart.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Food Street, City, State 12345</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
