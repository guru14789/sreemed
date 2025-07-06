
<?php
class Database {
    private $host = "localhost";
    private $db_name = "sreemeditec_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

// Create tables if they don't exist
$createTables = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    brand VARCHAR(100),
    model VARCHAR(100),
    image_url VARCHAR(500),
    stock_quantity INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

try {
    $db->exec($createTables);
} catch(PDOException $exception) {
    echo "Error creating tables: " . $exception->getMessage();
}

// Insert default categories
$insertCategories = "
INSERT IGNORE INTO categories (id, name, slug) VALUES 
(1, 'All products', 'all'),
(2, 'Cardiology Department', 'cardiology'),
(3, 'General Equipment', 'general'),
(4, 'General/Internal Medicine', 'internal'),
(5, 'Infection Prevention and Control Department', 'infection');
";

try {
    $db->exec($insertCategories);
} catch(PDOException $exception) {
    echo "Error inserting categories: " . $exception->getMessage();
}

// Insert sample products
$insertProducts = "
INSERT IGNORE INTO products (id, name, description, price, category_id, brand, model, image_url, stock_quantity) VALUES 
(1, 'Suction Machine', 'High-performance medical suction machine for surgical procedures', 2500.00, 3, 'MedTech', 'SM-2000', '/placeholder.svg', 10),
(2, 'ECG Machine', 'Digital electrocardiogram machine with advanced monitoring', 4500.00, 2, 'CardioMax', 'ECG-Pro', '/placeholder.svg', 5),
(3, 'Blood Pressure Monitor', 'Digital blood pressure monitor with memory function', 150.00, 4, 'HealthCare', 'BP-Digital', '/placeholder.svg', 25),
(4, 'Stethoscope', 'Professional grade stethoscope for medical examination', 89.99, 4, 'MedScope', 'Pro-Stetho', '/placeholder.svg', 50),
(5, 'Surgical Mask Box', 'Disposable surgical masks - Box of 50', 25.00, 5, 'SafeMed', 'Mask-Pro', '/placeholder.svg', 100);
";

try {
    $db->exec($insertProducts);
} catch(PDOException $exception) {
    echo "Error inserting products: " . $exception->getMessage();
}

// Insert admin user
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$insertAdmin = "
INSERT IGNORE INTO users (id, name, email, password, role, email_verified) VALUES 
(1, 'Admin User', 'admin@sreemeditec.com', '$adminPassword', 'admin', TRUE);
";

try {
    $db->exec($insertAdmin);
} catch(PDOException $exception) {
    echo "Error inserting admin user: " . $exception->getMessage();
}
?>
