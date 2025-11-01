<?php
require_once 'config.php';
require_once 'includes/functions.php';

$categories = getAllCategories();

// Filters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest'; // latest|name|price_asc|price_desc
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 12;
$offset = ($page - 1) * $pageSize;

// Build base WHERE with filters
$where = "WHERE f.is_available = 1";
$params = [];
if ($categoryId) { $where .= " AND f.category_id = ?"; $params[] = $categoryId; }
if ($q !== '') { $where .= " AND (f.name LIKE ? OR f.description LIKE ?)"; $like = "%$q%"; $params[] = $like; $params[] = $like; }
if ($minPrice !== null) { $where .= " AND f.price >= ?"; $params[] = $minPrice; }
if ($maxPrice !== null) { $where .= " AND f.price <= ?"; $params[] = $maxPrice; }

// Total count
$countSql = "SELECT COUNT(*) FROM foods f JOIN categories c ON f.category_id = c.id $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalItems / $pageSize));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $pageSize; }

// Fetch paginated rows
$sql = "SELECT f.*, c.name as category_name FROM foods f JOIN categories c ON f.category_id = c.id $where";
switch ($sort) {
    case 'name': $sql .= " ORDER BY f.name ASC"; break;
    case 'price_asc': $sql .= " ORDER BY f.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY f.price DESC"; break;
    default: $sql .= " ORDER BY f.created_at DESC";
}
$sql .= " LIMIT $pageSize OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$foods = $stmt->fetchAll();

// Build base query string for pagination links (preserve filters)
$queryBase = http_build_query(array_filter([
    'category' => $categoryId ?: null,
    'q' => $q !== '' ? $q : null,
    'min_price' => $minPrice !== null ? $minPrice : null,
    'max_price' => $maxPrice !== null ? $maxPrice : null,
    'sort' => $sort !== 'latest' ? $sort : null,
]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - <?php echo SITE_NAME; ?></title>
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
                    <a href="index.php" class="nav-link active">Home</a>
                    <a href="menu.php" class="nav-link active">Menu</a>
                    <a href="about.php" class="nav-link active">About</a>
                    <a href="contact.php" class="nav-link active">Contact</a>
                    <a href="login.php" class="nav-link active">Login</a>
                </div>
                <div class="nav-actions">
                    <div class="search-box">
                        <form action="menu.php" method="GET">
                            <input type="hidden" name="category" value="<?php echo $categoryId ?: ''; ?>">
                            <input type="text" name="q" placeholder="Search food..." value="<?php echo htmlspecialchars($q); ?>" class="search-input">
                            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
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
    
    <!-- Mobile-only search -->
    <div class="mobile-search">
        <form action="menu.php" method="GET">
            <input type="hidden" name="category" value="<?php echo $categoryId ?: ''; ?>">
            <input type="text" name="q" placeholder="Search food..." value="<?php echo htmlspecialchars($q); ?>" class="search-input">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <section class="categories" style="padding-top:2rem;">
        <div class="container">
            <h2 class="section-title">Browse Menu</h2>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;margin-bottom:1rem;">
                <a href="menu.php" class="btn <?php echo $categoryId ? 'btn-outline' : 'btn-primary'; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="menu.php?category=<?php echo $cat['id']; ?>" class="btn <?php echo ($categoryId === (int)$cat['id']) ? 'btn-primary' : 'btn-outline'; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="featured-foods">
        <div class="container">
            <form action="menu.php" method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin:0 0 1rem 0;align-items:end;">
                <input type="hidden" name="category" value="<?php echo $categoryId ?: ''; ?>">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
                <div>
                    <label>Min Price</label>
                    <input type="number" step="0.01" name="min_price" value="<?php echo $minPrice !== null ? htmlspecialchars($minPrice) : ''; ?>" class="search-input" style="width:100%;margin-top:6px;">
                </div>
                <div>
                    <label>Max Price</label>
                    <input type="number" step="0.01" name="max_price" value="<?php echo $maxPrice !== null ? htmlspecialchars($maxPrice) : ''; ?>" class="search-input" style="width:100%;margin-top:6px;">
                </div>
                <div>
                    <label>Sort By</label>
                    <select name="sort" class="search-input" style="width:100%;margin-top:6px;">
                        <option value="latest" <?php echo $sort==='latest'?'selected':''; ?>>Latest</option>
                        <option value="name" <?php echo $sort==='name'?'selected':''; ?>>Name (A-Z)</option>
                        <option value="price_asc" <?php echo $sort==='price_asc'?'selected':''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort==='price_desc'?'selected':''; ?>>Price (High to Low)</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-primary" style="width:100%;">Apply</button>
                </div>
            </form>
            <div class="foods-grid">
                <?php if (empty($foods)): ?>
                    <p>No items found.</p>
                <?php else: ?>
                    <?php foreach ($foods as $food): ?>
                        <div class="food-card">
                            <div class="food-image">
                                <img src="<?php echo $food['image'] ?: 'assets/images/hero-food.jpg'; ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
                                <div class="food-overlay">
                                    <button class="btn btn-primary add-to-cart" data-food-id="<?php echo (int)$food['id']; ?>">
                                        <i class="fas fa-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                            <div class="food-content">
                                <h3><?php echo htmlspecialchars($food['name']); ?></h3>
                                <?php if (!empty($food['category_name'])): ?>
                                    <p class="food-category"><?php echo htmlspecialchars($food['category_name']); ?></p>
                                <?php endif; ?>
                                <p class="food-description"><?php echo htmlspecialchars(substr($food['description'], 0, 90)); ?>...</p>
                                <div class="food-footer">
                                    <span class="food-price"><?php echo formatPrice($food['price']); ?></span>
                                    <a href="food.php?id=<?php echo (int)$food['id']; ?>" class="btn btn-outline btn-sm">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div style="display:flex;justify-content:center;gap:.5rem;margin-top:1rem;">
                <?php if ($totalPages > 1): ?>
                    <?php
                        $prevPage = max(1, $page - 1);
                        $nextPage = min($totalPages, $page + 1);
                        $prefix = $queryBase ? ($queryBase . '&') : '';
                    ?>
                    <a class="btn btn-outline btn-sm" href="menu.php?<?php echo $prefix; ?>page=<?php echo $prevPage; ?>" style="<?php echo $page==1?'pointer-events:none;opacity:.6;':''; ?>">Prev</a>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a class="btn <?php echo $p==$page?'btn-primary':'btn-outline'; ?> btn-sm" href="menu.php?<?php echo $prefix; ?>page=<?php echo $p; ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                    <a class="btn btn-outline btn-sm" href="menu.php?<?php echo $prefix; ?>page=<?php echo $nextPage; ?>" style="<?php echo $page==$totalPages?'pointer-events:none;opacity:.6;':''; ?>">Next</a>
                <?php endif; ?>
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


