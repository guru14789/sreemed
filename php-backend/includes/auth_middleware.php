<?php
require_once __DIR__ . '/jwt.php';

class AuthMiddleware 
{
    public static function authenticate(): ?array 
    {
        // Check for JWT token in Authorization header first
        $token = JWTHandler::getTokenFromHeader();
        
        if ($token) {
            $validation = JWTHandler::validateToken($token);
            if ($validation['valid']) {
                return $validation['data'];
            }
        }
        
        // Fallback to session-based authentication
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        
        return null;
    }
    
    public static function requireAuth(): array 
    {
        $user = self::authenticate();
        
        if (!$user) {
            sendJsonResponse(['error' => 'Authentication required'], 401);
        }
        
        return $user;
    }
    
    public static function requireAdmin(): array 
    {
        $user = self::requireAuth();
        
        if ($user['role'] !== 'admin') {
            sendJsonResponse(['error' => 'Admin access required'], 403);
        }
        
        return $user;
    }
    
    public static function requireRole(string $role): array 
    {
        $user = self::requireAuth();
        
        if ($user['role'] !== $role) {
            sendJsonResponse(['error' => "Role '$role' required"], 403);
        }
        
        return $user;
    }
    
    public static function setUserContext(array $user): void 
    {
        $_SESSION['user'] = $user;
        
        // Set global user context for request
        $GLOBALS['current_user'] = $user;
    }
}
?>