
<?php
require_once 'config/database.php';
require_once 'utils/jwt.php';

function handleLogin() {
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }
    
    $email = $data['email'];
    $password = $data['password'];
    
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        if (!$user['email_verified']) {
            http_response_code(401);
            echo json_encode(['error' => 'Email not confirmed']);
            return;
        }
        
        $token = generateJWT($user['id'], $user['role']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'role' => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
}

function handleRegister() {
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, email and password are required']);
        return;
    }
    
    // Check if user already exists
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'User already exists']);
        return;
    }
    
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (name, email, password, phone, address, email_verified) VALUES (:name, :email, :password, :phone, :address, TRUE)";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':phone', $data['phone'] ?? null);
    $stmt->bindParam(':address', $data['address'] ?? null);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed']);
    }
}

function handleLogout() {
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
}

function handleForgotPassword() {
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email is required']);
        return;
    }
    
    $email = $data['email'];
    
    // Check if user exists
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        // Don't reveal if email exists for security
        echo json_encode([
            'success' => true,
            'message' => 'If the email exists, reset instructions have been sent'
        ]);
        return;
    }
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $query = "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':expires_at', $expires);
    $stmt->execute();
    
    // In a real application, send email here
    
    echo json_encode([
        'success' => true,
        'message' => 'Reset instructions sent to your email'
    ]);
}

function handleGetProfile() {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $query = "SELECT id, name, email, phone, address, role FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user['user_id']);
    $stmt->execute();
    
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profile) {
        echo json_encode($profile);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
}

function handleUpdateProfile() {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $query = "UPDATE users SET name = :name, phone = :phone, address = :address, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':id', $user['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Profile update failed']);
    }
}
?>