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

    try {
        // Get total users
        $stmt = $db->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

        // Get total orders
        $stmt = $db->query("SELECT COUNT(*) as total_orders FROM orders");
        $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

        // Get total revenue
        $stmt = $db->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'");
        $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

        // Get recent orders
        $stmt = $db->query("
            SELECT o.*, u.name as user_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 10
        ");
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get monthly revenue for chart
        $stmt = $db->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as revenue
            FROM orders 
            WHERE status != 'cancelled' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stats' => [
                'total_users' => $totalUsers,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'recent_orders' => $recentOrders,
                'monthly_revenue' => $monthlyRevenue
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to get stats: ' . $e->getMessage()]);
    }
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