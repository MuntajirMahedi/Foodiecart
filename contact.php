<?php
require_once 'config.php';
require_once 'includes/functions.php';

$statusMessage = '';
$statusType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $subject && $message) {
        $to = ADMIN_EMAIL;
        $headers = "From: {$name} <{$email}>\r\n" .
                   "Reply-To: {$email}\r\n" .
                   "Content-Type: text/plain; charset=UTF-8\r\n";
        $body = "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\n\nMessage:\n{$message}";

        $sent = @mail($to, "[Contact] {$subject}", $body, $headers);
        if ($sent) {
            $statusType = 'success';
            $statusMessage = 'Thanks! Your message has been sent.';
            $_POST = [];
        } else {
            $statusType = 'error';
            $statusMessage = 'We could not send your message at the moment. Please try again later.';
        }
    } else {
        $statusType = 'error';
        $statusMessage = 'Please fill in all fields with valid information.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="description" content="Contact <?php echo SITE_NAME; ?> for support, questions, or feedback.">
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

    <section class="features" style="padding-top: 3rem;">
        <div class="container">
            <h1 class="section-title">Contact Us</h1>
            <p class="text-center mb-3">Have questions or feedback? Send us a message and we'll get back to you shortly.</p>

            <?php if ($statusMessage): ?>
                <div class="alert <?php echo $statusType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $statusMessage; ?>
                </div>
            <?php endif; ?>

            <form id="contactForm" class="form" method="post" onsubmit="return validateForm('contactForm')">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group mt-2">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                </div>
                <div class="form-group mt-2">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" class="form-control" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>

            <div class="text-center mb-3">
                <h2 class="mb-2">Other ways to reach us</h2>
                <p><i class="fas fa-envelope"></i> <?php echo ADMIN_EMAIL; ?></p>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
            </div>
        </div>
    </section>

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


