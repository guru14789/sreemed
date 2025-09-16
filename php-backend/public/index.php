<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/health.php';

// Perform startup health check
HealthChecker::failIfProductionMisconfigured();

// Test database connection (non-blocking for development)
$dbConnected = DatabaseConnection::testConnection();

// Simple router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = strtok($uri, '?');

// Basic routing
switch ($uri) {
    case '/':
    case '/index.php':
        if ($method === 'GET') {
            sendJsonResponse([
                'message' => 'Sree Meditec PHP API Server',
                'version' => '1.0.0',
                'status' => 'running',
                'php_version' => PHP_VERSION,
                'mongodb_extension' => extension_loaded('mongodb'),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        break;
        
    case '/health':
        if ($method === 'GET') {
            $health = [
                'status' => 'healthy',
                'database' => DatabaseConnection::testConnection() ? 'connected' : 'disconnected',
                'php_version' => PHP_VERSION,
                'extensions' => [
                    'mongodb' => extension_loaded('mongodb'),
                    'curl' => extension_loaded('curl'),
                    'json' => extension_loaded('json')
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            sendJsonResponse($health);
        }
        break;
        
    default:
        // Check if it's an API route
        if (str_starts_with($uri, '/api/')) {
            require_once __DIR__ . '/../routes/api.php';
        } else {
            http_response_code(404);
            sendJsonResponse(['error' => 'Route not found'], 404);
        }
        break;
}
?>