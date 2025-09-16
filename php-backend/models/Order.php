<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Order 
{
    private $collection;
    
    public function __construct()
    {
        if (extension_loaded('mongodb')) {
            $database = DatabaseConnection::getDatabase();
            $this->collection = $database->selectCollection('orders');
        }
    }
    
    public function createOrder(string $userId, array $orderData): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            $requiredFields = ['items', 'total_amount', 'shipping_address'];
            $errors = validateRequiredFields($orderData, $requiredFields);
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            $orderId = 'demo-order-' . uniqid();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'razorpay_order_id' => 'order_demo_' . uniqid(),
                'amount' => $orderData['total_amount'],
                'currency' => 'INR',
                'message' => 'Order created successfully (Demo Mode)'
            ];
        }
        
        // Validate required fields
        $requiredFields = ['items', 'total_amount', 'shipping_address'];
        $errors = validateRequiredFields($orderData, $requiredFields);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Create Razorpay order (placeholder - will be implemented with actual integration)
        $razorpayOrderId = 'order_' . uniqid();
        
        $order = [
            'user_id' => $userId,
            'items' => $orderData['items'],
            'total_amount' => (float)$orderData['total_amount'],
            'shipping_address' => $orderData['shipping_address'],
            'billing_address' => $orderData['billing_address'] ?? $orderData['shipping_address'],
            'payment_method' => $orderData['payment_method'] ?? 'razorpay',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => null,
            'courier_partner' => null,
            'awb_number' => null,
            'tracking_status' => null,
            'notes' => $orderData['notes'] ?? '',
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $result = $this->collection->insertOne($order);
            
            return [
                'success' => true,
                'order_id' => (string)$result->getInsertedId(),
                'razorpay_order_id' => $razorpayOrderId,
                'amount' => $order['total_amount'],
                'currency' => 'INR',
                'message' => 'Order created successfully'
            ];
        } catch (Exception $e) {
            error_log("Create order error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Order creation failed']];
        }
    }
    
    public function getOrderById(string $orderId, string $userId = null): ?array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            if (str_starts_with($orderId, 'demo-order-')) {
                return [
                    'id' => $orderId,
                    'user_id' => $userId ?? 'demo-user-id',
                    'items' => [
                        [
                            'product_id' => 'demo-product-1',
                            'name' => 'Ultrasound Machine Pro',
                            'quantity' => 1,
                            'price' => 45000.00,
                            'subtotal' => 45000.00
                        ]
                    ],
                    'total_amount' => 45000.00,
                    'payment_status' => 'completed',
                    'order_status' => 'confirmed',
                    'razorpay_order_id' => 'order_demo_' . substr($orderId, -8),
                    'created_at' => date('Y-m-d H:i:s'),
                    'tracking_status' => 'order_placed'
                ];
            }
            return null;
        }
        
        try {
            $query = ['_id' => new ObjectId($orderId)];
            if ($userId) {
                $query['user_id'] = $userId;
            }
            
            $order = $this->collection->findOne($query);
            
            if (!$order) {
                return null;
            }
            
            return [
                'id' => (string)$order->_id,
                'user_id' => $order->user_id,
                'items' => $order->items->toArray(),
                'total_amount' => $order->total_amount,
                'shipping_address' => $order->shipping_address,
                'billing_address' => $order->billing_address,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'razorpay_order_id' => $order->razorpay_order_id,
                'razorpay_payment_id' => $order->razorpay_payment_id,
                'courier_partner' => $order->courier_partner,
                'awb_number' => $order->awb_number,
                'tracking_status' => $order->tracking_status,
                'notes' => $order->notes ?? '',
                'created_at' => $order->created_at->toDateTime()->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Get order error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getUserOrders(string $userId, array $filters = []): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            return [
                [
                    'id' => 'demo-order-1',
                    'total_amount' => 45000.00,
                    'payment_status' => 'completed',
                    'order_status' => 'shipped',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                    'item_count' => 1,
                    'tracking_status' => 'in_transit'
                ],
                [
                    'id' => 'demo-order-2',
                    'total_amount' => 12000.00,
                    'payment_status' => 'completed',
                    'order_status' => 'delivered',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                    'item_count' => 1,
                    'tracking_status' => 'delivered'
                ]
            ];
        }
        
        try {
            $query = ['user_id' => $userId];
            $options = ['sort' => ['created_at' => -1]];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query['order_status'] = $filters['status'];
            }
            
            if (!empty($filters['payment_status'])) {
                $query['payment_status'] = $filters['payment_status'];
            }
            
            $orders = $this->collection->find($query, $options)->toArray();
            
            return array_map(function($order) {
                return [
                    'id' => (string)$order->_id,
                    'total_amount' => $order->total_amount,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                    'created_at' => $order->created_at->toDateTime()->format('Y-m-d H:i:s'),
                    'item_count' => count($order->items),
                    'tracking_status' => $order->tracking_status
                ];
            }, $orders);
        } catch (Exception $e) {
            error_log("Get user orders error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateOrderStatus(string $orderId, string $status, array $additionalData = []): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            return [
                'success' => true,
                'message' => "Order status updated to $status (Demo Mode)"
            ];
        }
        
        try {
            $updateData = [
                'order_status' => $status,
                'updated_at' => new UTCDateTime()
            ];
            
            // Add additional data
            foreach ($additionalData as $key => $value) {
                if (in_array($key, ['courier_partner', 'awb_number', 'tracking_status', 'notes'])) {
                    $updateData[$key] = $value;
                }
            }
            
            $result = $this->collection->updateOne(
                ['_id' => new ObjectId($orderId)],
                ['$set' => $updateData]
            );
            
            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Order status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Order not found or no changes made']
                ];
            }
        } catch (Exception $e) {
            error_log("Update order status error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to update order status']];
        }
    }
    
    public function updatePaymentStatus(string $orderId, string $paymentId, string $status): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            return [
                'success' => true,
                'message' => "Payment status updated to $status (Demo Mode)"
            ];
        }
        
        try {
            $updateData = [
                'payment_status' => $status,
                'razorpay_payment_id' => $paymentId,
                'updated_at' => new UTCDateTime()
            ];
            
            // If payment is successful, update order status to confirmed
            if ($status === 'completed') {
                $updateData['order_status'] = 'confirmed';
            }
            
            $result = $this->collection->updateOne(
                ['_id' => new ObjectId($orderId)],
                ['$set' => $updateData]
            );
            
            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Payment status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Order not found or no changes made']
                ];
            }
        } catch (Exception $e) {
            error_log("Update payment status error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to update payment status']];
        }
    }
    
    public function getAllOrders(array $filters = []): array
    {
        // Demo mode  
        if (!extension_loaded('mongodb')) {
            return [
                [
                    'id' => 'demo-order-1',
                    'user_id' => 'demo-user-1',
                    'username' => 'John Doe',
                    'total_amount' => 45000.00,
                    'payment_status' => 'completed',
                    'order_status' => 'shipped',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'item_count' => 1
                ],
                [
                    'id' => 'demo-order-2', 
                    'user_id' => 'demo-user-2',
                    'username' => 'Jane Smith',
                    'total_amount' => 12000.00,
                    'payment_status' => 'completed',
                    'order_status' => 'confirmed',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'item_count' => 1
                ]
            ];
        }
        
        try {
            $query = [];
            $options = ['sort' => ['created_at' => -1]];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query['order_status'] = $filters['status'];
            }
            
            if (!empty($filters['payment_status'])) {
                $query['payment_status'] = $filters['payment_status'];
            }
            
            $orders = $this->collection->find($query, $options)->toArray();
            
            // Get user details for each order
            require_once __DIR__ . '/User.php';
            $userModel = new User();
            
            return array_map(function($order) use ($userModel) {
                $user = $userModel->getUserById($order->user_id);
                
                return [
                    'id' => (string)$order->_id,
                    'user_id' => $order->user_id,
                    'username' => $user ? $user['username'] : 'Unknown User',
                    'total_amount' => $order->total_amount,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                    'created_at' => $order->created_at->toDateTime()->format('Y-m-d H:i:s'),
                    'item_count' => count($order->items)
                ];
            }, $orders);
        } catch (Exception $e) {
            error_log("Get all orders error: " . $e->getMessage());
            return [];
        }
    }
}
?>