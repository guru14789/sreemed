<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Cart 
{
    private $collection;
    
    public function __construct()
    {
        if (extension_loaded('mongodb')) {
            $database = DatabaseConnection::getDatabase();
            $this->collection = $database->selectCollection('carts');
        }
    }
    
    public function addToCart(string $userId, string $productId, int $quantity = 1): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'added_at' => date('Y-m-d H:i:s')
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Product added to cart successfully (Demo Mode)',
                'cart_items' => count($_SESSION['cart'])
            ];
        }
        
        try {
            // Check if user already has a cart
            $cart = $this->collection->findOne(['user_id' => $userId]);
            
            if (!$cart) {
                // Create new cart
                $cartData = [
                    'user_id' => $userId,
                    'items' => [
                        [
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'added_at' => new UTCDateTime()
                        ]
                    ],
                    'created_at' => new UTCDateTime(),
                    'updated_at' => new UTCDateTime()
                ];
                
                $result = $this->collection->insertOne($cartData);
                
                return [
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'cart_id' => (string)$result->getInsertedId()
                ];
            } else {
                // Update existing cart
                $existingItemIndex = -1;
                $items = $cart->items->toArray();
                
                foreach ($items as $index => $item) {
                    if ($item->product_id === $productId) {
                        $existingItemIndex = $index;
                        break;
                    }
                }
                
                if ($existingItemIndex >= 0) {
                    // Update quantity of existing item
                    $items[$existingItemIndex]->quantity += $quantity;
                } else {
                    // Add new item
                    $items[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'added_at' => new UTCDateTime()
                    ];
                }
                
                $result = $this->collection->updateOne(
                    ['user_id' => $userId],
                    [
                        '$set' => [
                            'items' => $items,
                            'updated_at' => new UTCDateTime()
                        ]
                    ]
                );
                
                return [
                    'success' => true,
                    'message' => 'Cart updated successfully'
                ];
            }
        } catch (Exception $e) {
            error_log("Add to cart error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to add product to cart']];
        }
    }
    
    public function getCart(string $userId): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            $cart = $_SESSION['cart'] ?? [];
            $cartItems = [];
            
            require_once __DIR__ . '/Product.php';
            $productModel = new Product();
            
            foreach ($cart as $item) {
                $product = $productModel->getProductById($item['product_id']);
                if ($product) {
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'subtotal' => $product['price'] * $item['quantity'],
                        'added_at' => $item['added_at']
                    ];
                }
            }
            
            $total = array_sum(array_column($cartItems, 'subtotal'));
            
            return [
                'success' => true,
                'items' => $cartItems,
                'total_items' => array_sum(array_column($cartItems, 'quantity')),
                'total_amount' => $total
            ];
        }
        
        try {
            $cart = $this->collection->findOne(['user_id' => $userId]);
            
            if (!$cart) {
                return [
                    'success' => true,
                    'items' => [],
                    'total_items' => 0,
                    'total_amount' => 0
                ];
            }
            
            // Get product details for each cart item
            require_once __DIR__ . '/Product.php';
            $productModel = new Product();
            
            $cartItems = [];
            $totalAmount = 0;
            $totalItems = 0;
            
            foreach ($cart->items as $item) {
                $product = $productModel->getProductById($item->product_id);
                if ($product) {
                    $subtotal = $product['price'] * $item->quantity;
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $item->quantity,
                        'subtotal' => $subtotal,
                        'added_at' => $item->added_at->toDateTime()->format('Y-m-d H:i:s')
                    ];
                    $totalAmount += $subtotal;
                    $totalItems += $item->quantity;
                }
            }
            
            return [
                'success' => true,
                'items' => $cartItems,
                'total_items' => $totalItems,
                'total_amount' => $totalAmount
            ];
        } catch (Exception $e) {
            error_log("Get cart error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to retrieve cart']];
        }
    }
    
    public function updateCartItem(string $userId, string $productId, int $quantity): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            if (!isset($_SESSION['cart'][$productId])) {
                return ['success' => false, 'errors' => ['Product not found in cart']];
            }
            
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
            }
            
            return [
                'success' => true,
                'message' => 'Cart updated successfully (Demo Mode)'
            ];
        }
        
        try {
            if ($quantity <= 0) {
                // Remove item from cart
                $result = $this->collection->updateOne(
                    ['user_id' => $userId],
                    [
                        '$pull' => ['items' => ['product_id' => $productId]],
                        '$set' => ['updated_at' => new UTCDateTime()]
                    ]
                );
            } else {
                // Update quantity
                $result = $this->collection->updateOne(
                    [
                        'user_id' => $userId,
                        'items.product_id' => $productId
                    ],
                    [
                        '$set' => [
                            'items.$.quantity' => $quantity,
                            'updated_at' => new UTCDateTime()
                        ]
                    ]
                );
            }
            
            return [
                'success' => true,
                'message' => 'Cart updated successfully'
            ];
        } catch (Exception $e) {
            error_log("Update cart error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to update cart']];
        }
    }
    
    public function clearCart(string $userId): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            $_SESSION['cart'] = [];
            return [
                'success' => true,
                'message' => 'Cart cleared successfully (Demo Mode)'
            ];
        }
        
        try {
            $result = $this->collection->deleteOne(['user_id' => $userId]);
            
            return [
                'success' => true,
                'message' => 'Cart cleared successfully'
            ];
        } catch (Exception $e) {
            error_log("Clear cart error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to clear cart']];
        }
    }
}
?>