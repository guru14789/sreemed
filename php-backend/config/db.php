<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Database;

class DatabaseConnection 
{
    private static ?Client $client = null;
    private static ?Database $database = null;
    
    public static function getClient(): Client
    {
        if (self::$client === null) {
            $uri = $_ENV['MONGODB_URI'] ?? 'mongodb://localhost:27017';
            
            self::$client = new Client($uri, [
                'maxPoolSize' => 10,
                'connectTimeoutMS' => 3000,
                'socketTimeoutMS' => 30000,
                'retryWrites' => true,
                'w' => 'majority'
            ]);
        }
        
        return self::$client;
    }
    
    public static function getDatabase(): Database
    {
        if (self::$database === null) {
            $client = self::getClient();
            $dbName = $_ENV['MONGODB_DATABASE'] ?? 'sree_meditec_db';
            self::$database = $client->selectDatabase($dbName);
        }
        
        return self::$database;
    }
    
    public static function testConnection(): bool
    {
        try {
            // Check if MongoDB extension is loaded
            if (!extension_loaded('mongodb')) {
                error_log("MongoDB extension not loaded - using fallback mode");
                return false;
            }
            
            $client = self::getClient();
            $client->selectDatabase('admin')->command(['ping' => 1]);
            return true;
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return false;
        }
    }
}
?>