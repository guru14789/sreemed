
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'routes/auth.php';
require_once 'routes/products.php';
require_once 'routes/cart.php';
require_once 'routes/orders.php';
require_once 'routes/users.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove /api from the path if present
$path = str_replace('/api', '', $request_uri);

// Route handling
switch ($path) {
    // Auth routes
    case '/auth/login':
        if ($request_method === 'POST') {
            handleLogin();
        }
        break;
    case '/auth/register':
        if ($request_method === 'POST') {
            handleRegister();
        }
        break;
    case '/auth/logout':
        if ($request_method === 'POST') {
            handleLogout();
        }
        break;
    case '/auth/forgot-password':
        if ($request_method === 'POST') {
            handleForgotPassword();
        }
        break;
    case '/auth/profile':
        if ($request_method === 'GET') {
            handleGetProfile();
        } elseif ($request_method === 'PUT') {
            handleUpdateProfile();
        }
        break;
    
    // Product routes
    case '/products':
        if ($request_method === 'GET') {
            handleGetProducts();
        } elseif ($request_method === 'POST') {
            handleCreateProduct();
        }
        break;
    case (preg_match('/\/products\/(\d+)/', $path, $matches) ? true : false):
        $product_id = $matches[1];
        if ($request_method === 'GET') {
            handleGetProduct($product_id);
        } elseif ($request_method === 'PUT') {
            handleUpdateProduct($product_id);
        } elseif ($request_method === 'DELETE') {
            handleDeleteProduct($product_id);
        }
        break;
    
    // Cart routes
    case '/cart':
        if ($request_method === 'GET') {
            handleGetCart();
        } elseif ($request_method === 'POST') {
            handleAddToCart();
        }
        break;
    case (preg_match('/\/cart\/(\d+)/', $path, $matches) ? true : false):
        $item_id = $matches[1];
        if ($request_method === 'PUT') {
            handleUpdateCartItem($item_id);
        } elseif ($request_method === 'DELETE') {
            handleRemoveFromCart($item_id);
        }
        break;
    
    // Order routes
    case '/orders':
        if ($request_method === 'GET') {
            handleGetOrders();
        } elseif ($request_method === 'POST') {
            handleCreateOrder();
        }
        break;
    case (preg_match('/\/orders\/(\d+)/', $path, $matches) ? true : false):
        $order_id = $matches[1];
        if ($request_method === 'GET') {
            handleGetOrder($order_id);
        } elseif ($request_method === 'PUT') {
            handleUpdateOrder($order_id);
        }
        break;
    
    // Admin routes
    case '/admin/users':
        if ($request_method === 'GET') {
            handleGetUsers();
        }
        break;
    case '/admin/stats':
        if ($request_method === 'GET') {
            handleGetStats();
        }
        break;
    
    case '/test':
        if ($request_method === 'GET') {
            echo json_encode(['message' => 'API is working!', 'timestamp' => date('Y-m-d H:i:s')]);
        }
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found', 'path' => $path]);
        break;
}
?>
