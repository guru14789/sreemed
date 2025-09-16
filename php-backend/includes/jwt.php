<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler 
{
    private static string $secretKey;
    private static int $expiry;
    
    public static function init(): void
    {
        self::$secretKey = $_ENV['JWT_SECRET'] ?? 'default-secret-change-this';
        self::$expiry = (int)($_ENV['JWT_EXPIRY'] ?? 3600);
    }
    
    public static function generateToken(array $payload): string
    {
        self::init();
        
        $issuedAt = time();
        $expirationTime = $issuedAt + self::$expiry;
        
        $token = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $payload
        ];
        
        return JWT::encode($token, self::$secretKey, 'HS256');
    }
    
    public static function validateToken(string $token): array
    {
        self::init();
        
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            return ['valid' => true, 'data' => (array)$decoded->data];
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
    
    public static function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    public static function refreshToken(string $token): ?string
    {
        $validation = self::validateToken($token);
        
        if ($validation['valid']) {
            return self::generateToken($validation['data']);
        }
        
        return null;
    }
}
?>