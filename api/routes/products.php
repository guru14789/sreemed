
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

function handleGetProducts() {
    global $db;
    
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? 12));
    $offset = ($page - 1) * $limit;
    
    $whereClause = "WHERE is_active = 1";
    $params = [];
    
    if ($category && $category !== 'all') {
        $whereClause .= " AND c.slug = :category";
        $params[':category'] = $category;
    }
    
    if ($search) {
        $whereClause .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.brand LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              $whereClause 
              ORDER BY p.created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   $whereClause";
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get categories
    $categoriesQuery = "SELECT * FROM categories ORDER BY name";
    $categoriesStmt = $db->prepare($categoriesQuery);
    $categoriesStmt->execute();
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'categories' => $categories,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function handleGetProduct($id) {
    global $db;
    
    $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = :id AND p.is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
}

function handleCreateProduct() {
    $user = validateJWT();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $query = "INSERT INTO products (name, description, price, category_id, brand, model, image_url, stock_quantity) 
              VALUES (:name, :description, :price, :category_id, :brand, :model, :image_url, :stock_quantity)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':brand', $data['brand']);
    $stmt->bindParam(':model', $data['model']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'product_id' => $db->lastInsertId()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Product creation failed']);
    }
}

function handleUpdateProduct($id) {
    $user = validateJWT();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $query = "UPDATE products SET name = :name, description = :description, price = :price, 
              category_id = :category_id, brand = :brand, model = :model, image_url = :image_url, 
              stock_quantity = :stock_quantity WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':brand', $data['brand']);
    $stmt->bindParam(':model', $data['model']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Product update failed']);
    }
}

function handleDeleteProduct($id) {
    $user = validateJWT();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $query = "UPDATE products SET is_active = 0 WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Product deletion failed']);
    }
}
?>

function handleGetProducts() {
    global $db;
    
    $category = $_GET['category'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'name';
    $order = $_GET['order'] ?? 'asc';
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_active = TRUE";
    
    $params = [];
    
    if ($category !== 'all') {
        $query .= " AND c.slug = :category";
        $params[':category'] = $category;
    }
    
    if (!empty($search)) {
        $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    $query .= " ORDER BY p.$sort $order LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.is_active = TRUE";
    
    if ($category !== 'all') {
        $countQuery .= " AND c.slug = '$category'";
    }
    
    if (!empty($search)) {
        $countQuery .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
    }
    
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'products' => $products,
        'total' => (int)$total,
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function handleGetProduct($id) {
    global $db;
    
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = :id AND p.is_active = TRUE";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
}

function handleCreateProduct() {
    $user = authenticateUser();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $query = "INSERT INTO products (name, description, price, category_id, brand, model, image_url, stock_quantity) 
              VALUES (:name, :description, :price, :category_id, :brand, :model, :image_url, :stock_quantity)";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':brand', $data['brand']);
    $stmt->bindParam(':model', $data['model']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
    
    if ($stmt->execute()) {
        $productId = $db->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $productId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Product creation failed']);
    }
}

function handleUpdateProduct($id) {
    $user = authenticateUser();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $query = "UPDATE products SET 
              name = :name, 
              description = :description, 
              price = :price, 
              category_id = :category_id, 
              brand = :brand, 
              model = :model, 
              image_url = :image_url, 
              stock_quantity = :stock_quantity,
              updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':brand', $data['brand']);
    $stmt->bindParam(':model', $data['model']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Product update failed']);
    }
}

function handleDeleteProduct($id) {
    $user = authenticateUser();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    global $db;
    
    $query = "UPDATE products SET is_active = FALSE WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Product deletion failed']);
    }
}
?>
