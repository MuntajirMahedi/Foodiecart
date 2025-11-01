<?php
require_once 'config.php';
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$category = $slug ? getCategoryBySlug($slug) : null;
if (!$category) {
    redirect('menu.php');
}

$foods = getAllFoods((int)$category['id'], null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <a href="menu.php" class="nav-link active">Menu</a>
                </div>
                <div class="nav-actions">
                    <a href="menu.php" class="btn btn-outline">Back to Menu</a>
                </div>
            </div>
        </nav>
    </header>

    <section class="categories" style="padding-top:2rem;">
        <div class="container">
            <h2 class="section-title"><?php echo htmlspecialchars($category['name']); ?></h2>
        </div>
    </section>

    <section class="featured-foods">
        <div class="container">
            <div class="foods-grid">
                <?php foreach ($foods as $food): ?>
                    <div class="food-card">
                        <div class="food-image">
                            <img src="<?php echo $food['image'] ?: 'D:\Xampp\htdocs\foodiecart1\assets\images\hero-food.jpg'; ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
                            <div class="food-overlay">
                                <button class="btn btn-primary add-to-cart" data-food-id="<?php echo (int)$food['id']; ?>">
                                    <i class="fas fa-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                        <div class="food-content">
                            <h3><?php echo htmlspecialchars($food['name']); ?></h3>
                            <p class="food-description"><?php echo htmlspecialchars(substr($food['description'], 0, 90)); ?>...</p>
                            <div class="food-footer">
                                <span class="food-price"><?php echo formatPrice($food['price']); ?></span>
                                <a href="food.php?id=<?php echo (int)$food['id']; ?>" class="btn btn-outline btn-sm">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>


