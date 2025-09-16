<?php
require_once __DIR__ . '/config.php';

class RazorpayConfig 
{
    public static function getKeyId(): string
    {
        return $_ENV['RAZORPAY_KEY_ID'] ?? '';
    }
    
    public static function getKeySecret(): string
    {
        return $_ENV['RAZORPAY_KEY_SECRET'] ?? '';
    }
    
    public static function getWebhookSecret(): string
    {
        return $_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? '';
    }
    
    public static function isConfigured(): bool
    {
        return !empty(self::getKeyId()) && !empty(self::getKeySecret());
    }
    
    public static function isDemoMode(): bool
    {
        return defined('APP_MODE') && APP_MODE === 'demo';
    }
}

class RazorpayPayment 
{
    private string $keyId;
    private string $keySecret;
    
    public function __construct()
    {
        $this->keyId = RazorpayConfig::getKeyId();
        $this->keySecret = RazorpayConfig::getKeySecret();
    }
    
    public function createOrder(float $amount, string $currency = 'INR', array $options = []): array
    {
        // Demo mode - return mock order only in explicit demo mode
        if (RazorpayConfig::isDemoMode()) {
            return [
                'success' => true,
                'order_id' => 'order_demo_' . uniqid(),
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'created'
            ];
        }
        
        // Production mode - require proper configuration
        if (!RazorpayConfig::isConfigured()) {
            error_log("CRITICAL: Payment order creation attempted without Razorpay configuration in production");
            return ['success' => false, 'errors' => ['Payment service not configured']];
        }
        
        $orderData = [
            'amount' => (int)($amount * 100), // Amount in paise
            'currency' => $currency,
            'receipt' => $options['receipt'] ?? 'receipt_' . uniqid(),
            'notes' => $options['notes'] ?? []
        ];
        
        try {
            $response = $this->makeRequest('POST', '/orders', $orderData);
            
            if ($response && isset($response['id'])) {
                return [
                    'success' => true,
                    'order_id' => $response['id'],
                    'amount' => $response['amount'] / 100,
                    'currency' => $response['currency'],
                    'status' => $response['status']
                ];
            } else {
                return ['success' => false, 'errors' => ['Failed to create Razorpay order']];
            }
        } catch (Exception $e) {
            error_log("Razorpay order creation error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Payment processing failed']];
        }
    }
    
    public function verifyPayment(string $paymentId, string $orderId, string $signature): bool
    {
        // Demo mode - only in explicitly demo environment
        if (RazorpayConfig::isDemoMode()) {
            error_log("WARNING: Payment verification in demo mode - always returning true");
            return true;
        }
        
        // Production - require proper configuration
        if (!RazorpayConfig::isConfigured()) {
            error_log("CRITICAL: Payment verification attempted without Razorpay configuration in production");
            return false;
        }
        
        $body = $orderId . "|" . $paymentId;
        $expectedSignature = hash_hmac('sha256', $body, $this->keySecret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    public function capturePayment(string $paymentId, float $amount): array
    {
        // Demo mode - only in explicit demo mode
        if (RazorpayConfig::isDemoMode()) {
            return [
                'success' => true,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'status' => 'captured'
            ];
        }
        
        // Production mode - require proper configuration
        if (!RazorpayConfig::isConfigured()) {
            error_log("CRITICAL: Payment capture attempted without Razorpay configuration in production");
            return ['success' => false, 'errors' => ['Payment service not configured']];
        }
        
        try {
            $captureData = ['amount' => (int)($amount * 100)];
            $response = $this->makeRequest('POST', "/payments/$paymentId/capture", $captureData);
            
            if ($response && isset($response['id'])) {
                return [
                    'success' => true,
                    'payment_id' => $response['id'],
                    'amount' => $response['amount'] / 100,
                    'status' => $response['status']
                ];
            } else {
                return ['success' => false, 'errors' => ['Failed to capture payment']];
            }
        } catch (Exception $e) {
            error_log("Razorpay payment capture error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Payment capture failed']];
        }
    }
    
    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        // Demo mode - only in explicit demo mode  
        if (RazorpayConfig::isDemoMode()) {
            return [
                'success' => true,
                'refund_id' => 'rfnd_demo_' . uniqid(),
                'payment_id' => $paymentId,
                'amount' => $amount ?? 0,
                'status' => 'processed'
            ];
        }
        
        // Production mode - require proper configuration
        if (!RazorpayConfig::isConfigured()) {
            error_log("CRITICAL: Payment refund attempted without Razorpay configuration in production");
            return ['success' => false, 'errors' => ['Payment service not configured']];
        }
        
        try {
            $refundData = [];
            if ($amount !== null) {
                $refundData['amount'] = (int)($amount * 100);
            }
            
            $response = $this->makeRequest('POST', "/payments/$paymentId/refund", $refundData);
            
            if ($response && isset($response['id'])) {
                return [
                    'success' => true,
                    'refund_id' => $response['id'],
                    'payment_id' => $response['payment_id'],
                    'amount' => $response['amount'] / 100,
                    'status' => $response['status']
                ];
            } else {
                return ['success' => false, 'errors' => ['Failed to process refund']];
            }
        } catch (Exception $e) {
            error_log("Razorpay refund error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Refund failed']];
        }
    }
    
    public function verifyWebhook(string $body, string $signature): bool
    {
        $webhookSecret = RazorpayConfig::getWebhookSecret();
        
        if (empty($webhookSecret)) {
            return false;
        }
        
        $expectedSignature = hash_hmac('sha256', $body, $webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    private function makeRequest(string $method, string $endpoint, array $data = []): ?array
    {
        $url = 'https://api.razorpay.com/v1' . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }
        
        return null;
    }
}
?>