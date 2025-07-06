
<?php
require_once 'config/database.php';
require_once 'utils/jwt.php';

function handleGetOrders() {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $query = "SELECT o.*, 
              (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
              FROM orders o 
              WHERE o.user_id = :user_id 
              ORDER BY o.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($orders);
}

function handleGetOrder($order_id) {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $query = "SELECT o.* FROM orders o WHERE o.id = :order_id AND o.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->execute();
    
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    // Get order items
    $query = "SELECT oi.*, p.name, p.image_url 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = :order_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($order);
}

function handleCreateOrder() {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['shipping_address']) || !isset($data['phone'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Shipping address and phone are required']);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Get cart items
        $query = "SELECT ci.*, p.price, p.stock_quantity 
                  FROM cart_items ci 
                  JOIN products p ON ci.product_id = p.id 
                  WHERE ci.user_id = :user_id AND p.is_active = TRUE";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->execute();
        
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['error' => 'Cart is empty']);
            $db->rollback();
            return;
        }
        
        // Calculate total and check stock
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                http_response_code(400);
                echo json_encode(['error' => 'Insufficient stock for some items']);
                $db->rollback();
                return;
            }
            $totalAmount += $item['price'] * $item['quantity'];
        }
        
        // Create order
        $query = "INSERT INTO orders (user_id, total_amount, shipping_address, billing_address, phone, notes) 
                  VALUES (:user_id, :total_amount, :shipping_address, :billing_address, :phone, :notes)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->bindParam(':total_amount', $totalAmount);
        $stmt->bindParam(':shipping_address', $data['shipping_address']);
        $stmt->bindParam(':billing_address', $data['billing_address'] ?? null);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':notes', $data['notes'] ?? null);
        
        $stmt->execute();
        $orderId = $db->lastInsertId();
        
        // Create order items
        foreach ($cartItems as $item) {
            $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES (:order_id, :product_id, :quantity, :price)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
            
            // Update product stock
            $query = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->execute();
        }
        
        // Clear cart
        $query = "DELETE FROM cart_items WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->execute();
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $orderId,
            'total_amount' => $totalAmount
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Order creation failed']);
    }
}

function handleUpdateOrder($order_id) {
    $user = authenticateUser();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Status is required']);
        return;
    }
    
    $query = "UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :order_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $data['status']);
    $stmt->bindParam(':order_id', $order_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Order update failed']);
    }
}
?>
