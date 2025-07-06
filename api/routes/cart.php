
<?php
require_once 'config/database.php';
require_once 'utils/jwt.php';

function handleGetCart() {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $query = "SELECT ci.*, p.name, p.price, p.image_url, p.stock_quantity 
              FROM cart_items ci 
              JOIN products p ON ci.product_id = p.id 
              WHERE ci.user_id = :user_id AND p.is_active = TRUE";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->execute();
    
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'cart_items' => $cartItems,
        'total_items' => array_sum(array_column($cartItems, 'quantity')),
        'total_amount' => array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cartItems))
    ]);
}

function handleAddToCart() {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID and quantity are required']);
        return;
    }
    
    // Check if product exists and is active
    $query = "SELECT stock_quantity FROM products WHERE id = :product_id AND is_active = TRUE";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':product_id', $data['product_id']);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        return;
    }
    
    if ($product['stock_quantity'] < $data['quantity']) {
        http_response_code(400);
        echo json_encode(['error' => 'Insufficient stock']);
        return;
    }
    
    // Check if item already exists in cart
    $query = "SELECT id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->bindParam(':product_id', $data['product_id']);
    $stmt->execute();
    
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        // Update existing item
        $newQuantity = $existingItem['quantity'] + $data['quantity'];
        
        if ($newQuantity > $product['stock_quantity']) {
            http_response_code(400);
            echo json_encode(['error' => 'Insufficient stock']);
            return;
        }
        
        $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':quantity', $newQuantity);
        $stmt->bindParam(':id', $existingItem['id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update cart']);
        }
    } else {
        // Add new item
        $query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->bindParam(':product_id', $data['product_id']);
        $stmt->bindParam(':quantity', $data['quantity']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Item added to cart successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add item to cart']);
        }
    }
}

function handleUpdateCartItem($item_id) {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Quantity is required']);
        return;
    }
    
    if ($data['quantity'] <= 0) {
        handleRemoveFromCart($item_id);
        return;
    }
    
    // Check if item belongs to user
    $query = "SELECT ci.product_id, p.stock_quantity 
              FROM cart_items ci 
              JOIN products p ON ci.product_id = p.id 
              WHERE ci.id = :item_id AND ci.user_id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->execute();
    
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Cart item not found']);
        return;
    }
    
    if ($data['quantity'] > $item['stock_quantity']) {
        http_response_code(400);
        echo json_encode(['error' => 'Insufficient stock']);
        return;
    }
    
    $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :item_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':quantity', $data['quantity']);
    $stmt->bindParam(':item_id', $item_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart item updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update cart item']);
    }
}

function handleRemoveFromCart($item_id) {
    $user = authenticateUser();
    if (!$user) return;
    
    global $db;
    
    $query = "DELETE FROM cart_items WHERE id = :item_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->bindParam(':user_id', $user['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove item from cart']);
    }
}
?>
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

function handleGetCart() {
    $user = validateJWT();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    global $db;
    
    $query = "SELECT ci.*, p.name, p.price, p.image_url, p.stock_quantity
              FROM cart_items ci
              JOIN products p ON ci.product_id = p.id
              WHERE ci.user_id = :user_id AND p.is_active = 1
              ORDER BY ci.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->execute();
    
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'items' => $items]);
}

function handleAddToCart() {
    $user = validateJWT();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID and quantity are required']);
        return;
    }
    
    // Check if item already exists in cart
    $checkQuery = "SELECT id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':user_id', $user['user_id']);
    $checkStmt->bindParam(':product_id', $data['product_id']);
    $checkStmt->execute();
    
    $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + $data['quantity'];
        $updateQuery = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':quantity', $newQuantity);
        $updateStmt->bindParam(':id', $existingItem['id']);
        $updateStmt->execute();
    } else {
        // Insert new item
        $insertQuery = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':user_id', $user['user_id']);
        $insertStmt->bindParam(':product_id', $data['product_id']);
        $insertStmt->bindParam(':quantity', $data['quantity']);
        $insertStmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
}

function handleUpdateCartItem($id) {
    $user = validateJWT();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':quantity', $data['quantity']);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $user['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cart item updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update cart item']);
    }
}

function handleRemoveFromCart($id) {
    $user = validateJWT();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    global $db;
    
    $query = "DELETE FROM cart_items WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $user['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove item from cart']);
    }
}
?>
