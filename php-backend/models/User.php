<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class User 
{
    private $collection;
    
    public function __construct()
    {
        $database = DatabaseConnection::getDatabase();
        $this->collection = $database->selectCollection('users');
    }
    
    public function register(array $userData): array
    {
        // Validate required fields
        $requiredFields = ['username', 'email', 'password', 'phone'];
        $errors = validateRequiredFields($userData, $requiredFields);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if email already exists
        if ($this->emailExists($userData['email'])) {
            return ['success' => false, 'errors' => ['Email already exists']];
        }
        
        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_ARGON2ID);
        
        $user = [
            'username' => sanitizeInput($userData['username']),
            'email' => strtolower(trim($userData['email'])),
            'password' => $hashedPassword,
            'phone' => sanitizeInput($userData['phone']),
            'address' => $userData['address'] ?? '',
            'profile_picture' => null,
            'role' => 'user',
            'email_verified' => false,
            'is_active' => true,
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $result = $this->collection->insertOne($user);
            return [
                'success' => true,
                'user_id' => (string)$result->getInsertedId(),
                'message' => 'User registered successfully'
            ];
        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Registration failed']];
        }
    }
    
    public function login(string $email, string $password): array
    {
        try {
            $user = $this->collection->findOne(['email' => strtolower(trim($email))]);
            
            if (!$user) {
                return ['success' => false, 'errors' => ['Invalid credentials']];
            }
            
            if (!$user->is_active) {
                return ['success' => false, 'errors' => ['Account is deactivated']];
            }
            
            if (!password_verify($password, $user->password)) {
                return ['success' => false, 'errors' => ['Invalid credentials']];
            }
            
            // Update last login
            $this->collection->updateOne(
                ['_id' => $user->_id],
                ['$set' => ['last_login' => new UTCDateTime()]]
            );
            
            return [
                'success' => true,
                'user' => [
                    'id' => (string)$user->_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'profile_picture' => $user->profile_picture
                ]
            ];
        } catch (Exception $e) {
            error_log("User login error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Login failed']];
        }
    }
    
    public function getUserById(string $userId): ?array
    {
        try {
            $user = $this->collection->findOne(['_id' => new ObjectId($userId)]);
            
            if (!$user) {
                return null;
            }
            
            return [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address ?? '',
                'role' => $user->role,
                'profile_picture' => $user->profile_picture,
                'email_verified' => $user->email_verified ?? false,
                'created_at' => $user->created_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProfile(string $userId, array $updateData): array
    {
        try {
            $allowedFields = ['username', 'phone', 'address', 'profile_picture'];
            $updateFields = [];
            
            foreach ($updateData as $field => $value) {
                if (in_array($field, $allowedFields) && !empty($value)) {
                    $updateFields[$field] = sanitizeInput($value);
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'errors' => ['No valid fields to update']];
            }
            
            $updateFields['updated_at'] = new UTCDateTime();
            
            $result = $this->collection->updateOne(
                ['_id' => new ObjectId($userId)],
                ['$set' => $updateFields]
            );
            
            if ($result->getModifiedCount() > 0) {
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'errors' => ['No changes made']];
            }
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Profile update failed']];
        }
    }
    
    public function changePassword(string $userId, string $currentPassword, string $newPassword): array
    {
        try {
            $user = $this->collection->findOne(['_id' => new ObjectId($userId)]);
            
            if (!$user) {
                return ['success' => false, 'errors' => ['User not found']];
            }
            
            if (!password_verify($currentPassword, $user->password)) {
                return ['success' => false, 'errors' => ['Current password is incorrect']];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
            
            $result = $this->collection->updateOne(
                ['_id' => new ObjectId($userId)],
                ['$set' => ['password' => $hashedPassword, 'updated_at' => new UTCDateTime()]]
            );
            
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Password change failed']];
        }
    }
    
    public function emailExists(string $email): bool
    {
        try {
            $user = $this->collection->findOne(['email' => strtolower(trim($email))]);
            return $user !== null;
        } catch (Exception $e) {
            error_log("Email check error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllUsers(): array
    {
        try {
            $users = $this->collection->find([], [
                'projection' => ['password' => 0],
                'sort' => ['created_at' => -1]
            ])->toArray();
            
            return array_map(function($user) {
                return [
                    'id' => (string)$user->_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'is_active' => $user->is_active ?? true,
                    'created_at' => $user->created_at->toDateTime()->format('Y-m-d H:i:s')
                ];
            }, $users);
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }
}
?>