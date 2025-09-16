<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/jwt.php';

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
    // Check authentication
    if (!isset($_SESSION['user'])) {
        $token = JWTHandler::getTokenFromHeader();
        
        if (!$token) {
            sendJsonResponse(['error' => 'Authentication required'], 401);
        }
        
        $validation = JWTHandler::validateToken($token);
        if (!$validation['valid']) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
        }
        
        $_SESSION['user'] = $validation['data'];
    }
    
    sendJsonResponse([
        'success' => true,
        'user' => $_SESSION['user']
    ]);
}

function handleUpdateUserProfile(): void
{
    if (!isset($_SESSION['user'])) {
        sendJsonResponse(['error' => 'Authentication required'], 401);
    }
    
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
                $_SESSION['user'][$field] = sanitizeInput($input[$field]);
                $updated = true;
            }
        }
        
        if ($updated) {
            sendJsonResponse([
                'success' => true,
                'message' => 'Profile updated successfully (Demo Mode)',
                'user' => $_SESSION['user']
            ]);
        } else {
            sendJsonResponse(['success' => false, 'errors' => ['No valid fields to update']], 400);
        }
        return;
    }
    
    $userModel = new User();
    $result = $userModel->updateProfile($_SESSION['user']['id'], $input);
    
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
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        sendJsonResponse(['error' => 'Admin access required'], 403);
    }
    
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
    if (!isset($_SESSION['user'])) {
        sendJsonResponse(['error' => 'Authentication required'], 401);
    }
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->getCart($_SESSION['user']['id']);
    
    sendJsonResponse($result);
}

function handleAddToCart(): void
{
    if (!isset($_SESSION['user'])) {
        sendJsonResponse(['error' => 'Authentication required'], 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        sendJsonResponse(['error' => 'Product ID is required'], 400);
    }
    
    $quantity = (int)($input['quantity'] ?? 1);
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->addToCart($_SESSION['user']['id'], $input['product_id'], $quantity);
    
    sendJsonResponse($result, $result['success'] ? 201 : 400);
}

function handleUpdateCartItem(): void
{
    if (!isset($_SESSION['user'])) {
        sendJsonResponse(['error' => 'Authentication required'], 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
        sendJsonResponse(['error' => 'Product ID and quantity are required'], 400);
    }
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->updateCartItem($_SESSION['user']['id'], $input['product_id'], (int)$input['quantity']);
    
    sendJsonResponse($result, $result['success'] ? 200 : 400);
}

function handleClearCart(): void
{
    if (!isset($_SESSION['user'])) {
        sendJsonResponse(['error' => 'Authentication required'], 401);
    }
    
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    
    $result = $cartModel->clearCart($_SESSION['user']['id']);
    
    sendJsonResponse($result);
}
?>