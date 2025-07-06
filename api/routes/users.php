
<?php
require_once 'config/database.php';
require_once 'utils/jwt.php';

function handleGetUsers() {
    $user = authenticateUser();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $query = "SELECT id, name, email, phone, role, email_verified, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
}

function handleGetStats() {
    $user = authenticateUser();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    // Get total users
    $query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // Get total orders
    $query = "SELECT COUNT(*) as total_orders FROM orders";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
    
    // Get total revenue
    $query = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
    
    // Get total products
    $query = "SELECT COUNT(*) as total_products FROM products WHERE is_active = TRUE";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];
    
    // Get recent orders
    $query = "SELECT o.id, o.total_amount, o.status, o.created_at, u.name as customer_name 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC 
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order status distribution
    $query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $orderStatusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'total_users' => (int)$totalUsers,
        'total_orders' => (int)$totalOrders,
        'total_revenue' => (float)$totalRevenue,
        'total_products' => (int)$totalProducts,
        'recent_orders' => $recentOrders,
        'order_status_stats' => $orderStatusStats
    ]);
}
?>
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

function handleGetUsers() {
    $user = validateJWT();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $query = "SELECT id, name, email, phone, role, email_verified, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
}

function handleGetStats() {
    $user = validateJWT();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    // Get total users
    $usersQuery = "SELECT COUNT(*) as total FROM users";
    $usersStmt = $db->prepare($usersQuery);
    $usersStmt->execute();
    $totalUsers = $usersStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total products
    $productsQuery = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
    $productsStmt = $db->prepare($productsQuery);
    $productsStmt->execute();
    $totalProducts = $productsStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total orders
    $ordersQuery = "SELECT COUNT(*) as total FROM orders";
    $ordersStmt = $db->prepare($ordersQuery);
    $ordersStmt->execute();
    $totalOrders = $ordersStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total revenue
    $revenueQuery = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
    $revenueStmt = $db->prepare($revenueQuery);
    $revenueStmt->execute();
    $totalRevenue = $revenueStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => (int)$totalUsers,
            'total_products' => (int)$totalProducts,
            'total_orders' => (int)$totalOrders,
            'total_revenue' => (float)$totalRevenue
        ]
    ]);
}
?>
