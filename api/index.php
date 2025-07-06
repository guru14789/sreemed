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

// Get the request URI and method
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove the '/api' prefix if present and clean the path
$path = str_replace('/api', '', parse_url($request_uri, PHP_URL_PATH));
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/';
}

// Route the request
switch (true) {
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

    case $path === '/admin/users' && $request_method === 'GET':
        handleGetUsers();
        break;

    case $path === '/admin/stats' && $request_method === 'GET':
        handleGetStats();
        break;

    case 'contact':
        require_once 'routes/contact.php';
        if ($request_method === 'POST' && $path === '/contact') {
            handleContactForm();
        } elseif ($request_method === 'GET' && $path === '/contact/submissions') {
            handleGetContactSubmissions();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
        break;

    case 'quote':
        require_once 'routes/contact.php';
        if ($request_method === 'POST' && $path === '/quote') {
            handleQuoteRequest();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
        break;

    case 'payments':
        require_once 'routes/payments.php';
        if ($request_method === 'POST') {
            if (isset($pathParts[2])) {
                switch ($pathParts[2]) {
                    case 'create-order':
                        handleCreatePaymentOrder();
                        break;
                    case 'success':
                        handlePaymentSuccess();
                        break;
                    case 'failure':
                        handlePaymentFailure();
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Payment endpoint not found']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Payment endpoint not found']);
            }
        }
        break;

    case $path === '/' || $path === '':
        echo json_encode(['message' => 'Sreemeditec API is running', 'version' => '1.0.0']);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found: ' . $path]);
        break;
}
?>