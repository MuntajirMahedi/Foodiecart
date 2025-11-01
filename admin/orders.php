<?php
require_once '../config.php';
require_once '../includes/functions.php';
if (!isAdmin()) { redirect('login.php'); }

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending','paid','preparing','out_for_delivery','delivered','cancelled'];
    if (in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
    }
    redirect('orders.php');
}

// Filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$where = 'WHERE 1=1';
$params = [];
if ($statusFilter && $statusFilter !== 'all') { $where .= ' AND status = ?'; $params[] = $statusFilter; }

$stmt = $pdo->prepare("SELECT * FROM orders $where ORDER BY created_at DESC LIMIT 200");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:16px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
        .filters{display:flex;gap:1rem;align-items:center;margin-bottom:1rem}
    </style>
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
                    <a href="foods.php" class="nav-link">Foods</a>
                    <a href="orders.php" class="nav-link active">Orders</a>
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
            <h2>Manage Orders</h2>
            <div>
                <a href="index.php" class="btn btn-outline">Dashboard</a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
        <div class="card">
            <form class="filters" method="GET">
                <label>Status
                    <select name="status" class="search-input" style="margin-left:.5rem;">
                        <?php $statuses=['all','pending','paid','preparing','out_for_delivery','delivered','cancelled']; ?>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $statusFilter===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary">Apply</button>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Placed</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Items</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?php echo (int)$o['id']; ?></td>
                        <td><?php echo $o['user_id'] ? (int)$o['user_id'] : 'Guest'; ?></td>
                        <td><?php echo formatPrice($o['total_amount']); ?></td>
                        <td><?php echo htmlspecialchars($o['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($o['status']); ?></td>
                        <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                        <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo htmlspecialchars($o['address']); ?>"><?php echo htmlspecialchars($o['address']); ?></td>
                        <td><?php echo htmlspecialchars($o['phone']); ?></td>
                        <td>
                            <?php $items = getOrderItems($o['id']); ?>
                            <?php foreach ($items as $it): ?>
                                <div><?php echo htmlspecialchars($it['name']); ?> x <?php echo (int)$it['qty']; ?> (<?php echo formatPrice($it['price']); ?>)</div>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:flex;gap:.5rem;align-items:center;">
                                <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                                <select name="status" class="search-input">
                                    <?php foreach (['pending','paid','preparing','out_for_delivery','delivered','cancelled'] as $st): ?>
                                        <option value="<?php echo $st; ?>" <?php echo $o['status']===$st?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$st)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>


