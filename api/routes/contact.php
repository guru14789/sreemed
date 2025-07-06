
<?php
require_once __DIR__ . '/../config/database.php';

function handleContactForm() {
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['message'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, email and message are required']);
        return;
    }
    
    $query = "INSERT INTO contact_submissions (name, email, phone, subject, message) 
              VALUES (:name, :email, :phone, :subject, :message)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':phone', $data['phone'] ?? null);
    $stmt->bindParam(':subject', $data['subject'] ?? 'General Inquiry');
    $stmt->bindParam(':message', $data['message']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for contacting us. We will get back to you soon!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to submit contact form']);
    }
}

function handleGetContactSubmissions() {
    global $db;
    
    // Admin only
    $user = validateJWT();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    $query = "SELECT * FROM contact_submissions ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'submissions' => $submissions]);
}

function handleQuoteRequest() {
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['equipment_type'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, email and equipment type are required']);
        return;
    }
    
    $query = "INSERT INTO quote_requests (name, email, phone, company, equipment_type, quantity, description, budget) 
              VALUES (:name, :email, :phone, :company, :equipment_type, :quantity, :description, :budget)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':phone', $data['phone'] ?? null);
    $stmt->bindParam(':company', $data['company'] ?? null);
    $stmt->bindParam(':equipment_type', $data['equipment_type']);
    $stmt->bindParam(':quantity', $data['quantity'] ?? 1);
    $stmt->bindParam(':description', $data['description'] ?? null);
    $stmt->bindParam(':budget', $data['budget'] ?? null);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Quote request submitted successfully. We will contact you soon!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to submit quote request']);
    }
}
?>
