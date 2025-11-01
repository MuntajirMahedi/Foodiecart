<?php
require_once __DIR__ . '/../config.php';

// User functions
function registerUser($name, $email, $password, $phone = '', $address = '') {
    global $pdo;
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, address) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $passwordHash, $phone, $address])) {
        return ['success' => true, 'message' => 'Registration successful'];
    }
    
    return ['success' => false, 'message' => 'Registration failed'];
}

function loginUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

function loginAdmin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        return ['success' => true, 'message' => 'Admin login successful'];
    }
    
    return ['success' => false, 'message' => 'Invalid username or password'];
}

// Category functions
function getAllCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

function getCategoryById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCategoryBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// Food functions
function getAllFoods($categoryId = null, $limit = null) {
    global $pdo;
    
    $sql = "SELECT f.*, c.name as category_name FROM foods f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.is_available = 1";
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND f.category_id = ?";
        $params[] = $categoryId;
    }
    
    $sql .= " ORDER BY f.created_at DESC";
    
    // Inline LIMIT as integer; MariaDB/MySQL do not allow binding LIMIT placeholders reliably
    if ($limit !== null) {
        $limitInt = (int)$limit;
        if ($limitInt > 0) {
            $sql .= " LIMIT " . $limitInt;
        }
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getFoodById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT f.*, c.name as category_name FROM foods f 
                          JOIN categories c ON f.category_id = c.id 
                          WHERE f.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function searchFoods($query) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT f.*, c.name as category_name FROM foods f 
                          JOIN categories c ON f.category_id = c.id 
                          WHERE f.is_available = 1 AND (f.name LIKE ? OR f.description LIKE ?)
                          ORDER BY f.name");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

// Cart functions
function addToCart($foodId, $quantity = 1, $userId = null) {
    global $pdo;
    
    $sessionId = session_id();
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare("SELECT id, qty FROM cart WHERE food_id = ? AND (user_id = ? OR session_id = ?)");
    $stmt->execute([$foodId, $userId, $sessionId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update quantity
        $newQty = $existing['qty'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET qty = ? WHERE id = ?");
        return $stmt->execute([$newQty, $existing['id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, session_id, food_id, qty) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $sessionId, $foodId, $quantity]);
    }
}

function getCartItems($userId = null) {
    global $pdo;
    
    $sessionId = session_id();
    $stmt = $pdo->prepare("SELECT c.*, f.name, f.price, f.image FROM cart c 
                          JOIN foods f ON c.food_id = f.id 
                          WHERE (c.user_id = ? OR c.session_id = ?) AND f.is_available = 1
                          ORDER BY c.created_at DESC");
    $stmt->execute([$userId, $sessionId]);
    return $stmt->fetchAll();
}

function updateCartQuantity($cartId, $quantity) {
    global $pdo;
    
    if ($quantity <= 0) {
        return removeFromCart($cartId);
    }
    
    $stmt = $pdo->prepare("UPDATE cart SET qty = ? WHERE id = ?");
    return $stmt->execute([$quantity, $cartId]);
}

function removeFromCart($cartId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    return $stmt->execute([$cartId]);
}

function clearCart($userId = null) {
    global $pdo;
    $sessionId = session_id();
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? OR session_id = ?");
    return $stmt->execute([$userId, $sessionId]);
}

function getCartTotal($userId = null) {
    $items = getCartItems($userId);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

// Order functions
function createOrder($userId, $totalAmount, $paymentMethod, $address, $phone) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_method, address, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $totalAmount, $paymentMethod, $address, $phone]);
        $orderId = $pdo->lastInsertId();
        
        // Add order items
        $cartItems = getCartItems($userId);
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, food_id, qty, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['food_id'], $item['qty'], $item['price']]);
        }
        
        // Clear cart
        clearCart($userId);
        
        $pdo->commit();
        return ['success' => true, 'order_id' => $orderId];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Order creation failed'];
    }
}

function getUserOrders($userId) { 
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getOrderItems($orderId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT oi.*, f.name, f.image FROM order_items oi 
                          JOIN foods f ON oi.food_id = f.id 
                          WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}
?>
