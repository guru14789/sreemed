<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/courier.php';

class HealthChecker 
{
    public static function performStartupCheck(): array
    {
        $errors = [];
        $warnings = [];
        
        // Check critical production configuration
        if (defined('IS_PRODUCTION') && IS_PRODUCTION) {
            // Razorpay configuration check for production
            if (!RazorpayConfig::isConfigured()) {
                $errors[] = "CRITICAL: Razorpay not configured in production environment";
            }
            
            // MongoDB configuration check
            if (empty($_ENV['MONGODB_URI'])) {
                $errors[] = "CRITICAL: MongoDB URI not configured in production";
            }
            
            // DTDC configuration check
            if (!DTDCConfig::isConfigured()) {
                $warnings[] = "WARNING: DTDC courier service not configured";
            }
            
            // Security checks
            if (empty($_ENV['JWT_SECRET']) || $_ENV['JWT_SECRET'] === 'your-super-secret-jwt-key-change-in-production') {
                $errors[] = "CRITICAL: JWT secret not properly configured in production";
            }
            
            if (empty($_ENV['RAZORPAY_WEBHOOK_SECRET'])) {
                $errors[] = "CRITICAL: Razorpay webhook secret not configured in production";
            }
        }
        
        // Check app mode consistency
        if (!defined('APP_MODE')) {
            $errors[] = "CRITICAL: APP_MODE not defined";
        } elseif (!in_array(APP_MODE, ['demo', 'production'])) {
            $errors[] = "CRITICAL: Invalid APP_MODE value: " . APP_MODE;
        }
        
        return [
            'passed' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    public static function failIfProductionMisconfigured(): void
    {
        $check = self::performStartupCheck();
        
        if (!$check['passed']) {
            error_log("STARTUP FAILURE: Configuration errors detected:");
            foreach ($check['errors'] as $error) {
                error_log($error);
            }
            
            // Send error response and exit
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Server configuration error',
                'details' => 'Check server logs for configuration issues'
            ]);
            exit;
        }
        
        if (!empty($check['warnings'])) {
            foreach ($check['warnings'] as $warning) {
                error_log($warning);
            }
        }
    }
}
?>