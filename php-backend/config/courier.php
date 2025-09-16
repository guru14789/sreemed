<?php
require_once __DIR__ . '/config.php';

class DTDCConfig 
{
    public static function getApiKey(): string
    {
        return $_ENV['DTDC_API_KEY'] ?? '';
    }
    
    public static function getClientId(): string
    {
        return $_ENV['DTDC_CLIENT_ID'] ?? '';
    }
    
    public static function getSecret(): string
    {
        return $_ENV['DTDC_SECRET'] ?? '';
    }
    
    public static function getBaseUrl(): string
    {
        return $_ENV['DTDC_BASE_URL'] ?? 'https://api.dtdc.com/v2';
    }
    
    public static function isConfigured(): bool
    {
        return !empty(self::getApiKey()) && !empty(self::getClientId());
    }
}

class DTDCCourier 
{
    private string $apiKey;
    private string $clientId;
    private string $secret;
    private string $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = DTDCConfig::getApiKey();
        $this->clientId = DTDCConfig::getClientId();
        $this->secret = DTDCConfig::getSecret();
        $this->baseUrl = DTDCConfig::getBaseUrl();
    }
    
    public function createShipment(array $shipmentData): array
    {
        // Demo mode - return mock shipment
        if (!DTDCConfig::isConfigured()) {
            return [
                'success' => true,
                'awb_number' => 'DTDC' . date('Ymd') . rand(100000, 999999),
                'shipment_id' => 'demo_shipment_' . uniqid(),
                'status' => 'booked',
                'tracking_url' => 'https://demo.dtdc.com/track/demo',
                'estimated_delivery' => date('Y-m-d', strtotime('+5 days'))
            ];
        }
        
        // Validate required fields
        $requiredFields = ['consignee', 'origin', 'destination', 'commodity'];
        $errors = validateRequiredFields($shipmentData, $requiredFields);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $response = $this->makeRequest('POST', '/shipments', $shipmentData);
            
            if ($response && isset($response['awb_number'])) {
                return [
                    'success' => true,
                    'awb_number' => $response['awb_number'],
                    'shipment_id' => $response['shipment_id'] ?? '',
                    'status' => $response['status'] ?? 'booked',
                    'tracking_url' => $this->baseUrl . '/track/' . $response['awb_number'],
                    'estimated_delivery' => $response['estimated_delivery'] ?? ''
                ];
            } else {
                return ['success' => false, 'errors' => ['Failed to create DTDC shipment']];
            }
        } catch (Exception $e) {
            error_log("DTDC shipment creation error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Shipment creation failed']];
        }
    }
    
    public function trackShipment(string $awbNumber): array
    {
        // Demo mode - return mock tracking data
        if (!DTDCConfig::isConfigured()) {
            $statuses = ['booked', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered'];
            $currentStatusIndex = min(4, (time() % 5)); // Simulate progression
            
            return [
                'success' => true,
                'awb_number' => $awbNumber,
                'current_status' => $statuses[$currentStatusIndex],
                'delivery_status' => $currentStatusIndex === 4 ? 'delivered' : 'in_transit',
                'tracking_history' => [
                    [
                        'status' => 'booked',
                        'location' => 'Mumbai Hub',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-4 days')),
                        'remarks' => 'Shipment booked successfully'
                    ],
                    [
                        'status' => 'picked_up',
                        'location' => 'Mumbai Hub',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-3 days')),
                        'remarks' => 'Package picked up from origin'
                    ],
                    [
                        'status' => 'in_transit',
                        'location' => 'Delhi Hub',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-2 days')),
                        'remarks' => 'Package in transit to destination'
                    ]
                ],
                'estimated_delivery' => date('Y-m-d', strtotime('+1 day'))
            ];
        }
        
        try {
            $response = $this->makeRequest('GET', "/track/$awbNumber");
            
            if ($response && isset($response['tracking_data'])) {
                return [
                    'success' => true,
                    'awb_number' => $awbNumber,
                    'current_status' => $response['tracking_data']['current_status'],
                    'delivery_status' => $response['tracking_data']['delivery_status'],
                    'tracking_history' => $response['tracking_data']['history'] ?? [],
                    'estimated_delivery' => $response['tracking_data']['estimated_delivery'] ?? ''
                ];
            } else {
                return ['success' => false, 'errors' => ['AWB number not found']];
            }
        } catch (Exception $e) {
            error_log("DTDC tracking error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Tracking failed']];
        }
    }
    
    public function cancelShipment(string $awbNumber): array
    {
        // Demo mode
        if (!DTDCConfig::isConfigured()) {
            return [
                'success' => true,
                'awb_number' => $awbNumber,
                'status' => 'cancelled',
                'message' => 'Shipment cancelled successfully (Demo Mode)'
            ];
        }
        
        try {
            $response = $this->makeRequest('POST', "/shipments/$awbNumber/cancel");
            
            if ($response && $response['status'] === 'cancelled') {
                return [
                    'success' => true,
                    'awb_number' => $awbNumber,
                    'status' => 'cancelled',
                    'message' => 'Shipment cancelled successfully'
                ];
            } else {
                return ['success' => false, 'errors' => ['Failed to cancel shipment']];
            }
        } catch (Exception $e) {
            error_log("DTDC cancellation error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Cancellation failed']];
        }
    }
    
    public function getServiceability(string $originPincode, string $destinationPincode): array
    {
        // Demo mode - always return serviceable
        if (!DTDCConfig::isConfigured()) {
            return [
                'success' => true,
                'serviceable' => true,
                'estimated_days' => rand(3, 7),
                'service_types' => ['surface', 'express'],
                'charges' => [
                    'surface' => rand(100, 300),
                    'express' => rand(200, 500)
                ]
            ];
        }
        
        try {
            $response = $this->makeRequest('GET', "/serviceability?origin=$originPincode&destination=$destinationPincode");
            
            if ($response) {
                return [
                    'success' => true,
                    'serviceable' => $response['serviceable'] ?? false,
                    'estimated_days' => $response['estimated_days'] ?? 0,
                    'service_types' => $response['service_types'] ?? [],
                    'charges' => $response['charges'] ?? []
                ];
            } else {
                return ['success' => false, 'errors' => ['Service check failed']];
            }
        } catch (Exception $e) {
            error_log("DTDC serviceability error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Serviceability check failed']];
        }
    }
    
    private function makeRequest(string $method, string $endpoint, array $data = []): ?array
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
            'X-Client-ID: ' . $this->clientId
        ];
        
        // Add authentication signature if available
        if (!empty($this->secret)) {
            $timestamp = time();
            $signature = hash_hmac('sha256', $method . $endpoint . json_encode($data) . $timestamp, $this->secret);
            $headers[] = 'X-Signature: ' . $signature;
            $headers[] = 'X-Timestamp: ' . $timestamp;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: $error");
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }
        
        return null;
    }
}
?>