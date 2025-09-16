<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Error reporting
if ($_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Define constants
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('MAX_FILE_SIZE', (int)$_ENV['MAX_FILE_SIZE']);
define('ALLOWED_FILE_TYPES', explode(',', $_ENV['ALLOWED_FILE_TYPES']));

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

if ($_ENV['APP_ENV'] === 'production') {
    ini_set('session.cookie_secure', 1);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS configuration
function setCorsHeaders() {
    // Allow requests from your React frontend
    $allowedOrigins = [
        'http://localhost:5173',
        'http://localhost:3000',
        $_ENV['APP_URL'] ?? 'http://localhost:5000'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Set CORS headers for all requests
setCorsHeaders();

// Helper function to send JSON response
function sendJsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper function to validate required fields
function validateRequiredFields(array $data, array $requiredFields): array {
    $errors = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = "Field '$field' is required";
        }
    }
    return $errors;
}

// Helper function to sanitize input
function sanitizeInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>