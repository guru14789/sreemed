
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config/database.php';
require_once 'utils/jwt.php';
require_once 'routes/auth.php';
require_once 'routes/products.php';
require_once 'routes/cart.php';
require_once 'routes/orders.php';
require_once 'routes/users.php';
require_once 'routes/contact.php';
require_once 'routes/payments.php';

// Get the request URI and method
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove the '/api' prefix if present and clean the path
$path = str_replace('/api', '', parse_url($request_uri, PHP_URL_PATH));
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/';
}

// Split path into parts for better routing
$pathParts = explode('/', trim($path, '/'));

// Route the request
try {
    switch (true) {
        // Auth routes
        case $path === '/auth/login' && $request_method === 'POST':
            handleLogin();
            break;

        case $path === '/auth/register' && $request_method === 'POST':
            handleRegister();
            break;

        case $path === '/auth/logout' && $request_method === 'POST':
            handleLogout();
            break;

        case $path === '/auth/forgot-password' && $request_method === 'POST':
            handleForgotPassword();
            break;

        case $path === '/auth/profile' && $request_method === 'GET':
            handleGetProfile();
            break;

        case $path === '/auth/profile' && $request_method === 'PUT':
            handleUpdateProfile();
            break;

        // Product routes
        case $path === '/products' && $request_method === 'GET':
            handleGetProducts();
            break;

        case $path === '/products' && $request_method === 'POST':
            handleCreateProduct();
            break;

        case preg_match('/^\/products\/(\d+)$/', $path, $matches) && $request_method === 'GET':
            handleGetProduct($matches[1]);
            break;

        case preg_match('/^\/products\/(\d+)$/', $path, $matches) && $request_method === 'PUT':
            handleUpdateProduct($matches[1]);
            break;

        case preg_match('/^\/products\/(\d+)$/', $path, $matches) && $request_method === 'DELETE':
            handleDeleteProduct($matches[1]);
            break;

        // Cart routes
        case $path === '/cart' && $request_method === 'GET':
            handleGetCart();
            break;

        case $path === '/cart' && $request_method === 'POST':
            handleAddToCart();
            break;

        case preg_match('/^\/cart\/(\d+)$/', $path, $matches) && $request_method === 'PUT':
            handleUpdateCartItem($matches[1]);
            break;

        case preg_match('/^\/cart\/(\d+)$/', $path, $matches) && $request_method === 'DELETE':
            handleRemoveFromCart($matches[1]);
            break;

        case $path === '/cart/clear' && $request_method === 'DELETE':
            handleClearCart();
            break;

        // Order routes
        case $path === '/orders' && $request_method === 'GET':
            handleGetOrders();
            break;

        case $path === '/orders' && $request_method === 'POST':
            handleCreateOrder();
            break;

        case preg_match('/^\/orders\/(\d+)$/', $path, $matches) && $request_method === 'GET':
            handleGetOrder($matches[1]);
            break;

        case preg_match('/^\/orders\/(\d+)$/', $path, $matches) && $request_method === 'PUT':
            handleUpdateOrder($matches[1]);
            break;

        case preg_match('/^\/orders\/(\d+)\/tracking$/', $path, $matches) && $request_method === 'GET':
            handleGetOrderTracking($matches[1]);
            break;

        // Payment routes
        case $path === '/payments/create-order' && $request_method === 'POST':
            handleCreatePaymentOrder();
            break;

        case $path === '/payments/success' && $request_method === 'POST':
            handlePaymentSuccess();
            break;

        case $path === '/payments/failure' && $request_method === 'POST':
            handlePaymentFailure();
            break;

        // Contact routes
        case $path === '/contact' && $request_method === 'POST':
            handleContactForm();
            break;

        case $path === '/contact/submissions' && $request_method === 'GET':
            handleGetContactSubmissions();
            break;

        case $path === '/quote' && $request_method === 'POST':
            handleQuoteRequest();
            break;

        // Admin routes
        case $path === '/admin/users' && $request_method === 'GET':
            handleGetUsers();
            break;

        case $path === '/admin/stats' && $request_method === 'GET':
            handleGetStats();
            break;

        case preg_match('/^\/admin\/users\/(\d+)$/', $path, $matches) && $request_method === 'PUT':
            handleUpdateUser($matches[1]);
            break;

        case preg_match('/^\/admin\/users\/(\d+)$/', $path, $matches) && $request_method === 'DELETE':
            handleDeleteUser($matches[1]);
            break;

        // Health check
        case $path === '/' || $path === '' || $path === '/health':
            echo json_encode([
                'message' => 'Sreemeditec API is running',
                'version' => '1.0.0',
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'error' => 'Endpoint not found',
                'path' => $path,
                'method' => $request_method
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
?>
