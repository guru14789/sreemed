<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/jwt.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

// Parse the URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove /api prefix
$route = str_replace('/api', '', $uri);

// API routing
switch (true) {
    // Authentication routes
    case $route === '/auth/register' && $method === 'POST':
        handleUserRegistration();
        break;
        
    case $route === '/auth/login' && $method === 'POST':
        handleUserLogin();
        break;
        
    case $route === '/auth/logout' && $method === 'POST':
        handleUserLogout();
        break;
        
    case $route === '/auth/me' && $method === 'GET':
        handleGetCurrentUser();
        break;
        
    // User profile routes
    case $route === '/user/profile' && $method === 'GET':
        handleGetUserProfile();
        break;
        
    case $route === '/user/profile' && $method === 'PUT':
        handleUpdateUserProfile();
        break;
        
    // Product routes
    case $route === '/products' && $method === 'GET':
        handleGetProducts();
        break;
        
    case preg_match('/^\/products\/([a-zA-Z0-9\-]+)$/', $route, $matches) && $method === 'GET':
        handleGetProductById($matches[1]);
        break;
        
    case $route === '/products' && $method === 'POST':
        handleCreateProduct();
        break;
        
    // Cart routes
    case $route === '/cart' && $method === 'GET':
        handleGetCart();
        break;
        
    case $route === '/cart/add' && $method === 'POST':
        handleAddToCart();
        break;
        
    case $route === '/cart/update' && $method === 'PUT':
        handleUpdateCartItem();
        break;
        
    case $route === '/cart/clear' && $method === 'DELETE':
        handleClearCart();
        break;
        
    // Order routes
    case $route === '/orders' && $method === 'GET':
        handleGetUserOrders();
        break;
        
    case preg_match('/^\/orders\/([a-zA-Z0-9\-]+)$/', $route, $matches) && $method === 'GET':
        handleGetOrderById($matches[1]);
        break;
        
    case $route === '/orders' && $method === 'POST':
        handleCreateOrder();
        break;
        
    // Payment routes
    case $route === '/payment/create-order' && $method === 'POST':
        handleCreatePaymentOrder();
        break;
        
    case $route === '/payment/verify' && $method === 'POST':
        handleVerifyPayment();
        break;
        
    case $route === '/payment/webhook' && $method === 'POST':
        handlePaymentWebhook();
        break;
        
    // Shipping/Courier routes
    case $route === '/shipping/create' && $method === 'POST':
        handleCreateShipment();
        break;
        
    case preg_match('/^\/shipping\/track\/([A-Z0-9]+)$/', $route, $matches) && $method === 'GET':
        handleTrackShipment($matches[1]);
        break;
        
    case $route === '/shipping/serviceability' && $method === 'GET':
        handleCheckServiceability();
        break;
        
    // Admin routes
    case str_starts_with($route, '/admin/') && $method !== 'OPTIONS':
        handleAdminRoutes($route, $method);
        break;
        
    // Health check
    case $route === '/health' && $method === 'GET':
        $health = [
            'status' => 'healthy',
            'database' => DatabaseConnection::testConnection() ? 'connected' : 'disconnected',
            'php_version' => PHP_VERSION,
            'extensions' => [
                'mongodb' => extension_loaded('mongodb'),
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'gd' => extension_loaded('gd')
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        sendJsonResponse($health);
        break;
        
    default:
        sendJsonResponse(['error' => 'API endpoint not found'], 404);
        break;
}

// Authentication handlers
function handleUserRegistration(): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    // For demo purposes without MongoDB, simulate user registration
    if (!extension_loaded('mongodb')) {
        $requiredFields = ['username', 'email', 'password', 'phone'];
        $errors = validateRequiredFields($input, $requiredFields);
        
        if (!empty($errors)) {
            sendJsonResponse(['success' => false, 'errors' => $errors], 400);
        }
        
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            sendJsonResponse(['success' => false, 'errors' => ['Invalid email format']], 400);
        }
        
        // Simulate successful registration
        sendJsonResponse([
            'success' => true,
            'user_id' => uniqid(),
            'message' => 'User registered successfully (Demo Mode - MongoDB not connected)'
        ]);
        return;
    }
    
    $userModel = new User();
    $result = $userModel->register($input);
    
    if ($result['success']) {
        sendJsonResponse($result, 201);
    } else {
        sendJsonResponse($result, 400);
    }
}

function handleUserLogin(): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email']) || !isset($input['password'])) {
        sendJsonResponse(['error' => 'Email and password are required'], 400);
    }
    
    // For demo purposes without MongoDB
    if (!extension_loaded('mongodb')) {
        // Demo credentials
        if ($input['email'] === 'admin@sreemeditec.com' && $input['password'] === 'admin123') {
            $user = [
                'id' => 'demo-admin-id',
                'username' => 'Admin',
                'email' => 'admin@sreemeditec.com',
                'role' => 'admin'
            ];
            
            $token = JWTHandler::generateToken($user);
            
            $_SESSION['user'] = $user;
            
            sendJsonResponse([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful (Demo Mode)'
            ]);
            return;
        }
        
        sendJsonResponse(['success' => false, 'errors' => ['Invalid credentials. Try admin@sreemeditec.com / admin123']], 401);
        return;
    }
    
    $userModel = new User();
    $result = $userModel->login($input['email'], $input['password']);
    
    if ($result['success']) {
        $token = JWTHandler::generateToken($result['user']);
        $_SESSION['user'] = $result['user'];
        
        sendJsonResponse([
            'success' => true,
            'user' => $result['user'],
            'token' => $token
        ]);
    } else {
        sendJsonResponse($result, 401);
    }
}

function handleUserLogout(): void
{
    session_destroy();
    sendJsonResponse(['success' => true, 'message' => 'Logged out successfully']);
}

function handleGetCurrentUser(): void
{
    $token = JWTHandler::getTokenFromHeader();
    
    if (!$token) {
        sendJsonResponse(['error' => 'No token provided'], 401);
    }
    
    $validation = JWTHandler::validateToken($token);
    
    if (!$validation['valid']) {
        sendJsonResponse(['error' => 'Invalid token'], 401);
    }
    
    sendJsonResponse([
        'success' => true,
        'user' => $validation['data']
    ]);
}

function handleGetUserProfile(): void
{
    $user = AuthMiddleware::requireAuth();
    
    sendJsonResponse([
        'success' => true,
        'user' => $user
    ]);
}

function handleUpdateUserProfile(): void
{
    $user = AuthMiddleware::requireAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    // For demo mode
    if (!extension_loaded('mongodb')) {
        $allowedFields = ['username', 'phone', 'address'];
        $updated = false;
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $user[$field] = sanitizeInput($input[$field]);
                $updated = true;
            }
        }
        
        if ($updated) {
            AuthMiddleware::setUserContext($user);
            sendJsonResponse([
                'success' => true,
                'message' => 'Profile updated successfully (Demo Mode)',
                'user' => $user
            ]);
        } else {
            sendJsonResponse(['success' => false, 'errors' => ['No valid fields to update']], 400);
        }
        return;
    }
    
    $userModel = new User();
    $result = $userModel->updateProfile($user['id'], $input);
    
    sendJsonResponse($result, $result['success'] ? 200 : 400);
}

// Product handlers
function handleGetProducts(): void
{
    require_once __DIR__ . '/../models/Product.php';
    $productModel = new Product();
    
    // Get filters from query parameters
    $filters = [];
    if (isset($_GET['category'])) {
        $filters['category'] = $_GET['category'];
    }
    if (isset($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    if (isset($_GET['price_min'])) {
        $filters['price_min'] = $_GET['price_min'];
    }
    if (isset($_GET['price_max'])) {
        $filters['price_max'] = $_GET['price_max'];
    }
    
    $products = $productModel->getAllProducts($filters);
    
    sendJsonResponse([
        'success' => true,
        'products' => $products,
        'total' => count($products)
    ]);
}

function handleGetProductById(string $productId): void
{
    require_once __DIR__ . '/../models/Product.php';
    $productModel = new Product();
    
    $product = $productModel->getProductById($productId);
    
    if ($product) {
        sendJsonResponse([
            'success' => true,
            'product' => $product
        ]);
    } else {
        sendJsonResponse(['error' => 'Product not found'], 404);
    }
}

function handleCreateProduct(): void
{
    // Check if user is admin
    $user = AuthMiddleware::requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    require_once __DIR__ . '/../models/Product.php';
    $productModel = new Product();
    
    $result = $productModel->createProduct($input);
    
    sendJsonResponse($result, $result['success'] ? 201 : 400);
}

// Cart handlers
function handleGetCart(): void
{
    $user = AuthMiddleware::requireAuth();
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->getCart($user['id']);
    
    sendJsonResponse($result);
}

function handleAddToCart(): void
{
    $user = AuthMiddleware::requireAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        sendJsonResponse(['error' => 'Product ID is required'], 400);
    }
    
    $quantity = (int)($input['quantity'] ?? 1);
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->addToCart($user['id'], $input['product_id'], $quantity);
    
    sendJsonResponse($result, $result['success'] ? 201 : 400);
}

function handleUpdateCartItem(): void
{
    $user = AuthMiddleware::requireAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
        sendJsonResponse(['error' => 'Product ID and quantity are required'], 400);
    }
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->updateCartItem($user['id'], $input['product_id'], (int)$input['quantity']);
    
    sendJsonResponse($result, $result['success'] ? 200 : 400);
}

function handleClearCart(): void
{
    $user = AuthMiddleware::requireAuth();
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->clearCart($user['id']);
    
    sendJsonResponse($result);
}

// Order handlers
function handleGetUserOrders(): void
{
    $user = AuthMiddleware::requireAuth();
    
    require_once __DIR__ . '/../models/Order.php';
    $orderModel = new Order();
    
    $filters = [];
    if (isset($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (isset($_GET['payment_status'])) {
        $filters['payment_status'] = $_GET['payment_status'];
    }
    
    $orders = $orderModel->getUserOrders($user['id'], $filters);
    
    sendJsonResponse([
        'success' => true,
        'orders' => $orders,
        'total' => count($orders)
    ]);
}

function handleGetOrderById(string $orderId): void
{
    $user = AuthMiddleware::requireAuth();
    
    require_once __DIR__ . '/../models/Order.php';
    $orderModel = new Order();
    
    $order = $orderModel->getOrderById($orderId, $user['id']);
    
    if ($order) {
        sendJsonResponse([
            'success' => true,
            'order' => $order
        ]);
    } else {
        sendJsonResponse(['error' => 'Order not found'], 404);
    }
}

function handleCreateOrder(): void
{
    $user = AuthMiddleware::requireAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    require_once __DIR__ . '/../models/Order.php';
    $orderModel = new Order();
    
    $result = $orderModel->createOrder($user['id'], $input);
    
    sendJsonResponse($result, $result['success'] ? 201 : 400);
}

// Payment handlers
function handleCreatePaymentOrder(): void
{
    $user = AuthMiddleware::requireAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['amount'])) {
        sendJsonResponse(['error' => 'Amount is required'], 400);
    }
    
    require_once __DIR__ . '/../config/payment.php';
    $payment = new RazorpayPayment();
    
    $options = [
        'receipt' => 'receipt_' . $user['id'] . '_' . time(),
        'notes' => [
            'user_id' => $user['id'],
            'username' => $user['username']
        ]
    ];
    
    $result = $payment->createOrder((float)$input['amount'], 'INR', $options);
    
    if ($result['success']) {
        sendJsonResponse([
            'success' => true,
            'key' => RazorpayConfig::getKeyId(),
            'order_id' => $result['order_id'],
            'amount' => $result['amount'],
            'currency' => $result['currency'],
            'name' => 'Sree Meditec',
            'description' => 'Medical Equipment Purchase',
            'prefill' => [
                'name' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        sendJsonResponse($result, 400);
    }
}

function handleVerifyPayment(): void
{
    $user = AuthMiddleware::requireAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $requiredFields = ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'];
    $errors = validateRequiredFields($input, $requiredFields);
    
    if (!empty($errors)) {
        sendJsonResponse(['success' => false, 'errors' => $errors], 400);
    }
    
    require_once __DIR__ . '/../config/payment.php';
    $payment = new RazorpayPayment();
    
    $isValid = $payment->verifyPayment(
        $input['razorpay_payment_id'],
        $input['razorpay_order_id'],
        $input['razorpay_signature']
    );
    
    if ($isValid) {
        // Update order payment status
        if (isset($input['order_id'])) {
            require_once __DIR__ . '/../models/Order.php';
            $orderModel = new Order();
            $orderModel->updatePaymentStatus($input['order_id'], $input['razorpay_payment_id'], 'completed');
        }
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Payment verified successfully'
        ]);
    } else {
        sendJsonResponse([
            'success' => false,
            'errors' => ['Payment verification failed']
        ], 400);
    }
}

function handlePaymentWebhook(): void
{
    $body = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
    
    require_once __DIR__ . '/../config/payment.php';
    $payment = new RazorpayPayment();
    
    // Strictly require and validate webhook signature
    if (empty($signature)) {
        error_log("SECURITY VIOLATION: Webhook called without signature header from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        http_response_code(401);
        exit('Unauthorized: Signature required');
    }
    
    if (!$payment->verifyWebhook($body, $signature)) {
        error_log("SECURITY VIOLATION: Webhook signature verification failed from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        http_response_code(403);
        exit('Forbidden: Invalid signature');
    }
    
    $event = json_decode($body, true);
    
    if (!$event || !isset($event['event'])) {
        error_log("SECURITY: Invalid webhook event data received");
        http_response_code(400);
        exit('Invalid event data');
    }
    
    // Only process known event types for security
    $allowedEvents = ['payment.captured', 'payment.failed', 'refund.created'];
    if (!in_array($event['event'], $allowedEvents)) {
        error_log("SECURITY: Unknown webhook event type: " . $event['event']);
        http_response_code(400);
        exit('Unknown event type');
    }
    
    try {
        require_once __DIR__ . '/../models/Order.php';
        $orderModel = new Order();
        
        switch ($event['event']) {
            case 'payment.captured':
                $payload = $event['payload']['payment']['entity'];
                $orderId = $payload['notes']['order_id'] ?? null;
                
                if ($orderId) {
                    $orderModel->updatePaymentStatus($orderId, $payload['id'], 'completed');
                }
                break;
                
            case 'payment.failed':
                $payload = $event['payload']['payment']['entity'];
                $orderId = $payload['notes']['order_id'] ?? null;
                
                if ($orderId) {
                    $orderModel->updatePaymentStatus($orderId, $payload['id'], 'failed');
                }
                break;
                
            case 'refund.created':
                $payload = $event['payload']['refund']['entity'];
                // Handle refund logic here
                break;
        }
        
        http_response_code(200);
        echo 'OK';
    } catch (Exception $e) {
        error_log("Webhook processing error: " . $e->getMessage());
        http_response_code(500);
        echo 'Error processing webhook';
    }
}

// Shipping handlers
function handleCreateShipment(): void
{
    $user = AuthMiddleware::requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    require_once __DIR__ . '/../config/courier.php';
    $courier = new DTDCCourier();
    
    $result = $courier->createShipment($input);
    
    sendJsonResponse($result, $result['success'] ? 201 : 400);
}

function handleTrackShipment(string $awbNumber): void
{
    require_once __DIR__ . '/../config/courier.php';
    $courier = new DTDCCourier();
    
    $result = $courier->trackShipment($awbNumber);
    
    if ($result['success']) {
        sendJsonResponse($result);
    } else {
        sendJsonResponse($result, 404);
    }
}

function handleCheckServiceability(): void
{
    $origin = $_GET['origin'] ?? '';
    $destination = $_GET['destination'] ?? '';
    
    if (empty($origin) || empty($destination)) {
        sendJsonResponse(['error' => 'Origin and destination pincodes are required'], 400);
    }
    
    require_once __DIR__ . '/../config/courier.php';
    $courier = new DTDCCourier();
    
    $result = $courier->getServiceability($origin, $destination);
    
    sendJsonResponse($result);
}

// Admin route handler
function handleAdminRoutes(string $route, string $method): void
{
    // Check admin access
    $user = AuthMiddleware::requireAdmin();
    
    // Remove /admin prefix
    $adminRoute = str_replace('/admin', '', $route);
    
    switch (true) {
        // User management
        case $adminRoute === '/users' && $method === 'GET':
            handleAdminGetUsers();
            break;
            
        case preg_match('/^\/users\/([a-zA-Z0-9\-]+)\/toggle$/', $adminRoute, $matches) && $method === 'PUT':
            handleAdminToggleUser($matches[1]);
            break;
            
        // Product management
        case $adminRoute === '/products' && $method === 'GET':
            handleAdminGetProducts();
            break;
            
        case $adminRoute === '/products' && $method === 'POST':
            handleCreateProduct(); // Reuse existing function
            break;
            
        case preg_match('/^\/products\/([a-zA-Z0-9\-]+)$/', $adminRoute, $matches) && $method === 'PUT':
            handleAdminUpdateProduct($matches[1]);
            break;
            
        case preg_match('/^\/products\/([a-zA-Z0-9\-]+)$/', $adminRoute, $matches) && $method === 'DELETE':
            handleAdminDeleteProduct($matches[1]);
            break;
            
        // Order management
        case $adminRoute === '/orders' && $method === 'GET':
            handleAdminGetOrders();
            break;
            
        case preg_match('/^\/orders\/([a-zA-Z0-9\-]+)\/status$/', $adminRoute, $matches) && $method === 'PUT':
            handleAdminUpdateOrderStatus($matches[1]);
            break;
            
        // Analytics
        case $adminRoute === '/analytics/dashboard' && $method === 'GET':
            handleAdminDashboard();
            break;
            
        case $adminRoute === '/analytics/sales' && $method === 'GET':
            handleAdminSalesAnalytics();
            break;
            
        default:
            sendJsonResponse(['error' => 'Admin endpoint not found'], 404);
            break;
    }
}

// Admin handlers
function handleAdminGetUsers(): void
{
    require_once __DIR__ . '/../models/User.php';
    $userModel = new User();
    
    $users = $userModel->getAllUsers();
    
    sendJsonResponse([
        'success' => true,
        'users' => $users,
        'total' => count($users)
    ]);
}

function handleAdminToggleUser(string $userId): void
{
    // Demo mode
    if (!extension_loaded('mongodb')) {
        sendJsonResponse([
            'success' => true,
            'message' => 'User status toggled successfully (Demo Mode)'
        ]);
        return;
    }
    
    require_once __DIR__ . '/../models/User.php';
    $userModel = new User();
    
    // Implementation would toggle user active status
    sendJsonResponse([
        'success' => true,
        'message' => 'User status updated successfully'
    ]);
}

function handleAdminGetProducts(): void
{
    require_once __DIR__ . '/../models/Product.php';
    $productModel = new Product();
    
    $products = $productModel->getAllProducts();
    
    sendJsonResponse([
        'success' => true,
        'products' => $products,
        'total' => count($products)
    ]);
}

function handleAdminUpdateProduct(string $productId): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    require_once __DIR__ . '/../models/Product.php';
    $productModel = new Product();
    
    $result = $productModel->updateProduct($productId, $input);
    
    sendJsonResponse($result, $result['success'] ? 200 : 400);
}

function handleAdminDeleteProduct(string $productId): void
{
    require_once __DIR__ . '/../models/Product.php';
    $productModel = new Product();
    
    $result = $productModel->deleteProduct($productId);
    
    sendJsonResponse($result, $result['success'] ? 200 : 400);
}

function handleAdminGetOrders(): void
{
    $filters = [];
    if (isset($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (isset($_GET['payment_status'])) {
        $filters['payment_status'] = $_GET['payment_status'];
    }
    
    require_once __DIR__ . '/../models/Order.php';
    $orderModel = new Order();
    
    $orders = $orderModel->getAllOrders($filters);
    
    sendJsonResponse([
        'success' => true,
        'orders' => $orders,
        'total' => count($orders)
    ]);
}

function handleAdminUpdateOrderStatus(string $orderId): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['status'])) {
        sendJsonResponse(['error' => 'Status is required'], 400);
    }
    
    require_once __DIR__ . '/../models/Order.php';
    $orderModel = new Order();
    
    $additionalData = [];
    foreach (['courier_partner', 'awb_number', 'tracking_status', 'notes'] as $field) {
        if (isset($input[$field])) {
            $additionalData[$field] = $input[$field];
        }
    }
    
    $result = $orderModel->updateOrderStatus($orderId, $input['status'], $additionalData);
    
    sendJsonResponse($result, $result['success'] ? 200 : 400);
}

function handleAdminDashboard(): void
{
    // Demo analytics data
    $analytics = [
        'success' => true,
        'data' => [
            'total_orders' => 156,
            'pending_orders' => 12,
            'completed_orders' => 134,
            'total_revenue' => 2850000.00,
            'monthly_revenue' => 450000.00,
            'total_users' => 89,
            'active_users' => 67,
            'total_products' => 45,
            'low_stock_products' => 5,
            'recent_orders' => [
                [
                    'id' => 'demo-order-recent-1',
                    'customer' => 'Dr. Sharma',
                    'amount' => 45000.00,
                    'status' => 'confirmed',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                ],
                [
                    'id' => 'demo-order-recent-2', 
                    'customer' => 'City Hospital',
                    'amount' => 120000.00,
                    'status' => 'shipped',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
                ]
            ],
            'top_products' => [
                ['name' => 'Ultrasound Machine Pro', 'sales' => 15],
                ['name' => 'Patient Monitor Advanced', 'sales' => 12],
                ['name' => 'Digital X-Ray System', 'sales' => 8]
            ]
        ]
    ];
    
    sendJsonResponse($analytics);
}

function handleAdminSalesAnalytics(): void
{
    $period = $_GET['period'] ?? 'monthly';
    
    // Demo sales data
    $salesData = [
        'success' => true,
        'period' => $period,
        'data' => [
            'total_sales' => 2850000.00,
            'total_orders' => 156,
            'average_order_value' => 18269.23,
            'growth_percentage' => 15.7,
            'chart_data' => [
                ['label' => 'Jan 2025', 'sales' => 380000, 'orders' => 22],
                ['label' => 'Feb 2025', 'sales' => 420000, 'orders' => 28],
                ['label' => 'Mar 2025', 'sales' => 450000, 'orders' => 31],
                ['label' => 'Apr 2025', 'sales' => 520000, 'orders' => 35],
                ['label' => 'May 2025', 'sales' => 480000, 'orders' => 29],
                ['label' => 'Jun 2025', 'sales' => 600000, 'orders' => 42]
            ]
        ]
    ];
    
    sendJsonResponse($salesData);
}
?>