<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$food = $id ? getFoodById($id) : null;
if (!$food) {
    redirect('menu.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($food['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .food-detail{display:grid;grid-template-columns:1.2fr 1fr;gap:2rem}
        .food-hero{border-radius:16px;overflow:hidden;box-shadow:0 10px 24px rgba(0, 0, 0, 0.69)}
        .qty-wrap{display:flex;align-items:center;gap:.5rem}
        .qty-input{width:72px;text-align:center}
        @media(max-width:900px){.food-detail{grid-template-columns:1fr}}
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
                    <a href="index.php" class="nav-link active">Home</a>
                    <a href="menu.php" class="nav-link active">Menu</a>
                    <a href="about.php" class="nav-link active">About</a>
                    <a href="contact.php" class="nav-link active">Contact</a>
                </div>
                <div class="nav-actions">
                    <a href="menu.php" class="btn btn-outline">Back to Menu</a>
                </div>
            </div>
        </nav>
    </header>

    <section class="categories" style="padding-top:2rem;">
        <div class="container food-detail">
            <div>
                <img class="food-hero" src="<?php echo $food['image'] ?: '..assets/images/hero-food.jpg'; ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
            </div>
            <div>
                <h1><?php echo htmlspecialchars($food['name']); ?></h1>
                <p class="food-category" style="margin-bottom:.75rem;">Category: <?php echo htmlspecialchars($food['category_name']); ?></p>
                <p style="margin-bottom:1rem;"><?php echo nl2br(htmlspecialchars($food['description'])); ?></p>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:2rem 0 1.5rem;">
                    <span class="food-price" style="font-size:1.6rem;"><?php echo formatPrice($food['price']); ?></span>
                    <div class="qty-wrap">
                        <button class="btn btn-outline" onclick="updateQuantity(document.getElementById('qty'), -1)">-</button>
                        <input id="qty" class="search-input qty-input" type="number" min="1" value="1">
                        <button class="btn btn-outline" onclick="updateQuantity(document.getElementById('qty'), 1)">+</button>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="addToCart(<?php echo (int)$food['id']; ?>, parseInt(document.getElementById('qty').value)||1)">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>


