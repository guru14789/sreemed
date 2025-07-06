
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

function handleCreatePaymentOrder() {
    $user = validateJWT();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['amount'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Amount is required']);
        return;
    }
    
    // Create payment order (for Razorpay/Stripe integration)
    $paymentOrderId = 'order_' . uniqid();
    
    // Store payment order in database
    $query = "INSERT INTO payment_orders (id, user_id, amount, status) VALUES (:id, :user_id, :amount, 'created')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $paymentOrderId);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->bindParam(':amount', $data['amount']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'payment_order_id' => $paymentOrderId,
            'amount' => $data['amount'],
            'currency' => 'INR'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create payment order']);
    }
}

function handlePaymentSuccess() {
    $user = validateJWT();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['payment_id']) || !isset($data['order_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Payment ID and Order ID are required']);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Update payment order status
        $query = "UPDATE payment_orders SET status = 'completed', payment_id = :payment_id WHERE id = :order_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':payment_id', $data['payment_id']);
        $stmt->bindParam(':order_id', $data['order_id']);
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->execute();
        
        // Create the actual order
        $cartQuery = "SELECT ci.*, p.price 
                      FROM cart_items ci 
                      JOIN products p ON ci.product_id = p.id 
                      WHERE ci.user_id = :user_id AND p.is_active = 1";
        $cartStmt = $db->prepare($cartQuery);
        $cartStmt->bindParam(':user_id', $user['user_id']);
        $cartStmt->execute();
        
        $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($cartItems)) {
            // Calculate total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }
            
            // Create order
            $orderQuery = "INSERT INTO orders (user_id, total_amount, status, payment_id, payment_method) 
                           VALUES (:user_id, :total_amount, 'confirmed', :payment_id, :payment_method)";
            $orderStmt = $db->prepare($orderQuery);
            $orderStmt->bindParam(':user_id', $user['user_id']);
            $orderStmt->bindParam(':total_amount', $totalAmount);
            $orderStmt->bindParam(':payment_id', $data['payment_id']);
            $orderStmt->bindParam(':payment_method', $data['payment_method'] ?? 'card');
            $orderStmt->execute();
            
            $orderId = $db->lastInsertId();
            
            // Create order items
            $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $itemStmt = $db->prepare($itemQuery);
            
            foreach ($cartItems as $item) {
                $itemStmt->bindParam(':order_id', $orderId);
                $itemStmt->bindParam(':product_id', $item['product_id']);
                $itemStmt->bindParam(':quantity', $item['quantity']);
                $itemStmt->bindParam(':price', $item['price']);
                $itemStmt->execute();
            }
            
            // Clear cart
            $clearCartQuery = "DELETE FROM cart_items WHERE user_id = :user_id";
            $clearCartStmt = $db->prepare($clearCartQuery);
            $clearCartStmt->bindParam(':user_id', $user['user_id']);
            $clearCartStmt->execute();
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Payment successful and order created'
            ]);
        } else {
            throw new Exception('Cart is empty');
        }
        
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Payment processing failed: ' . $e->getMessage()]);
    }
}

function handlePaymentFailure() {
    $user = validateJWT();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['order_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Order ID is required']);
        return;
    }
    
    // Update payment order status to failed
    $query = "UPDATE payment_orders SET status = 'failed' WHERE id = :order_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $data['order_id']);
    $stmt->bindParam(':user_id', $user['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Payment failure recorded'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to record payment failure']);
    }
}
?>
