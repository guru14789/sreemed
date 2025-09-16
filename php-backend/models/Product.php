<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Product 
{
    private $collection;
    
    public function __construct()
    {
        if (extension_loaded('mongodb')) {
            $database = DatabaseConnection::getDatabase();
            $this->collection = $database->selectCollection('products');
        }
    }
    
    public function getAllProducts(array $filters = []): array
    {
        // Demo data for when MongoDB is not available
        if (!extension_loaded('mongodb')) {
            return $this->getDemoProducts($filters);
        }
        
        try {
            $query = [];
            $options = ['sort' => ['created_at' => -1]];
            
            // Apply filters
            if (!empty($filters['category'])) {
                $query['category'] = $filters['category'];
            }
            
            if (!empty($filters['search'])) {
                $query['$or'] = [
                    ['name' => new \MongoDB\BSON\Regex($filters['search'], 'i')],
                    ['description' => new \MongoDB\BSON\Regex($filters['search'], 'i')]
                ];
            }
            
            if (isset($filters['price_min']) || isset($filters['price_max'])) {
                $priceQuery = [];
                if (isset($filters['price_min'])) {
                    $priceQuery['$gte'] = (float)$filters['price_min'];
                }
                if (isset($filters['price_max'])) {
                    $priceQuery['$lte'] = (float)$filters['price_max'];
                }
                $query['price'] = $priceQuery;
            }
            
            $products = $this->collection->find($query, $options)->toArray();
            
            return array_map(function($product) {
                return [
                    'id' => (string)$product->_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'tags' => $product->tags ?? [],
                    'price' => $product->price,
                    'image' => $product->image,
                    'stock' => $product->stock ?? 0,
                    'created_at' => $product->created_at->toDateTime()->format('Y-m-d H:i:s')
                ];
            }, $products);
        } catch (Exception $e) {
            error_log("Get products error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getProductById(string $productId): ?array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            $demoProducts = $this->getDemoProducts();
            foreach ($demoProducts as $product) {
                if ($product['id'] === $productId) {
                    return $product;
                }
            }
            return null;
        }
        
        try {
            $product = $this->collection->findOne(['_id' => new ObjectId($productId)]);
            
            if (!$product) {
                return null;
            }
            
            return [
                'id' => (string)$product->_id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'tags' => $product->tags ?? [],
                'price' => $product->price,
                'image' => $product->image,
                'stock' => $product->stock ?? 0,
                'specifications' => $product->specifications ?? [],
                'created_at' => $product->created_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Get product error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createProduct(array $productData): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            $requiredFields = ['name', 'description', 'category', 'price'];
            $errors = validateRequiredFields($productData, $requiredFields);
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            return [
                'success' => true,
                'product_id' => 'demo-' . uniqid(),
                'message' => 'Product created successfully (Demo Mode)'
            ];
        }
        
        // Validate required fields
        $requiredFields = ['name', 'description', 'category', 'price'];
        $errors = validateRequiredFields($productData, $requiredFields);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $product = [
            'name' => sanitizeInput($productData['name']),
            'description' => sanitizeInput($productData['description']),
            'category' => sanitizeInput($productData['category']),
            'tags' => $productData['tags'] ?? [],
            'price' => (float)$productData['price'],
            'image' => $productData['image'] ?? null,
            'stock' => (int)($productData['stock'] ?? 0),
            'specifications' => $productData['specifications'] ?? [],
            'is_active' => true,
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $result = $this->collection->insertOne($product);
            return [
                'success' => true,
                'product_id' => (string)$result->getInsertedId(),
                'message' => 'Product created successfully'
            ];
        } catch (Exception $e) {
            error_log("Create product error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Product creation failed']];
        }
    }
    
    public function updateProduct(string $productId, array $updateData): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            return [
                'success' => true,
                'message' => 'Product updated successfully (Demo Mode)'
            ];
        }
        
        try {
            $allowedFields = ['name', 'description', 'category', 'tags', 'price', 'image', 'stock', 'specifications'];
            $updateFields = [];
            
            foreach ($updateData as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    if (in_array($field, ['name', 'description', 'category'])) {
                        $updateFields[$field] = sanitizeInput($value);
                    } else {
                        $updateFields[$field] = $value;
                    }
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'errors' => ['No valid fields to update']];
            }
            
            $updateFields['updated_at'] = new UTCDateTime();
            
            $result = $this->collection->updateOne(
                ['_id' => new ObjectId($productId)],
                ['$set' => $updateFields]
            );
            
            if ($result->getModifiedCount() > 0) {
                return ['success' => true, 'message' => 'Product updated successfully'];
            } else {
                return ['success' => false, 'errors' => ['No changes made or product not found']];
            }
        } catch (Exception $e) {
            error_log("Update product error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Product update failed']];
        }
    }
    
    public function deleteProduct(string $productId): array
    {
        // Demo mode
        if (!extension_loaded('mongodb')) {
            return [
                'success' => true,
                'message' => 'Product deleted successfully (Demo Mode)'
            ];
        }
        
        try {
            $result = $this->collection->deleteOne(['_id' => new ObjectId($productId)]);
            
            if ($result->getDeletedCount() > 0) {
                return ['success' => true, 'message' => 'Product deleted successfully'];
            } else {
                return ['success' => false, 'errors' => ['Product not found']];
            }
        } catch (Exception $e) {
            error_log("Delete product error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Product deletion failed']];
        }
    }
    
    private function getDemoProducts(array $filters = []): array
    {
        $demoProducts = [
            [
                'id' => 'demo-product-1',
                'name' => 'Ultrasound Machine Pro',
                'description' => 'High-resolution ultrasound machine with advanced imaging capabilities',
                'category' => 'diagnostic-equipment',
                'tags' => ['ultrasound', 'imaging', 'diagnostic'],
                'price' => 45000.00,
                'image' => '/uploads/demo-ultrasound.jpg',
                'stock' => 5,
                'created_at' => '2025-01-15 10:30:00'
            ],
            [
                'id' => 'demo-product-2',
                'name' => 'Digital X-Ray System',
                'description' => 'State-of-the-art digital X-ray system for precise imaging',
                'category' => 'diagnostic-equipment',
                'tags' => ['x-ray', 'digital', 'imaging'],
                'price' => 75000.00,
                'image' => '/uploads/demo-xray.jpg',
                'stock' => 3,
                'created_at' => '2025-01-14 14:20:00'
            ],
            [
                'id' => 'demo-product-3',
                'name' => 'Patient Monitor Advanced',
                'description' => 'Multi-parameter patient monitoring system with wireless connectivity',
                'category' => 'monitoring-equipment',
                'tags' => ['monitor', 'patient', 'wireless'],
                'price' => 12000.00,
                'image' => '/uploads/demo-monitor.jpg',
                'stock' => 10,
                'created_at' => '2025-01-13 09:15:00'
            ]
        ];
        
        // Apply demo filters
        if (!empty($filters['category'])) {
            $demoProducts = array_filter($demoProducts, function($product) use ($filters) {
                return $product['category'] === $filters['category'];
            });
        }
        
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $demoProducts = array_filter($demoProducts, function($product) use ($search) {
                return strpos(strtolower($product['name']), $search) !== false ||
                       strpos(strtolower($product['description']), $search) !== false;
            });
        }
        
        return array_values($demoProducts);
    }
}
?>