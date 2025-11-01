<?php
require_once '../config.php';
require_once '../includes/functions.php';
if (!isAdmin()) { redirect('login.php'); }

$categories = getAllCategories();

// Add food
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = sanitize($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $image = sanitize($_POST['image'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    if ($name && $categoryId && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO foods (category_id, name, price, description, image, is_available) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$categoryId, $name, $price, $description, $image, $isAvailable]);
    }
    redirect('foods.php');
}

// Delete food
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM foods WHERE id = ?")->execute([$id]);
    redirect('foods.php');
}

$foods = getAllFoods(null, null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foods - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>.grid{display:grid;grid-template-columns:1fr 2fr;gap:1rem}.card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:16px}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}</style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php"><i class="fas fa-user-shield"></i> Admin</a>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link">Dashboard</a>
                    <a href="categories.php" class="nav-link">Categories</a>
                    <a href="foods.php" class="nav-link active">Foods</a>
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
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h2>Manage Foods</h2>
            <div>
                <a href="index.php" class="btn btn-outline">Dashboard</a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
        <div class="grid">
            <div class="card">
                <h3 class="mb-2">Add Food</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="search-input" style="width:100%;margin-top:6px;" required>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" class="search-input" style="width:100%;margin-top:6px;" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Price</label>
                        <input type="number" name="price" step="0.01" min="0" class="search-input" style="width:100%;margin-top:6px;" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="search-input" style="width:100%;height:120px;margin-top:6px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Image URL</label>
                        <input type="text" name="image" class="search-input" style="width:100%;margin-top:6px;" placeholder="/assets/images/example.jpg">
                    </div>
                    <div class="mb-3">
                        <label><input type="checkbox" name="is_available" checked> Available</label>
                    </div>
                    <button class="btn btn-primary">Add</button>
                </form>
            </div>
            <div class="card">
                <h3 class="mb-2">All Foods</h3>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Avail.</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($foods as $f): ?>
                                <tr>
                                    <td><?php echo (int)$f['id']; ?></td>
                                    <td><?php echo htmlspecialchars($f['name']); ?></td>
                                    <td><?php echo htmlspecialchars($f['category_name']); ?></td>
                                    <td><?php echo formatPrice($f['price']); ?></td>
                                    <td><?php echo $f['is_available'] ? 'Yes' : 'No'; ?></td>
                                    <td>
                                        <a class="btn btn-outline btn-sm" href="?delete=<?php echo (int)$f['id']; ?>" onclick="return confirm('Delete this food?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>


