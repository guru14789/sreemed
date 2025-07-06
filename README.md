# Sreemeditec E-commerce Backend (PHP)

This is a complete PHP backend API for the Sreemeditec e-commerce frontend built with React.

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for additional packages)

### Local Development Setup

1. **Database Setup:**
   - Create a MySQL database named `sreemeditec_db`
   - Import the database schema: `mysql -u root -p sreemeditec_db < setup.sql`
   - Or run the SQL commands in `setup.sql` manually

2. **Configure Database Connection:**
   - Open `api/config/database.php`
   - Update the database credentials:
     ```php
     private $host = "localhost";
     private $db_name = "sreemeditec_db";
     private $username = "root";
     private $password = "your_password";
     ```

3. **Web Server Setup:**
   - Place the project in your web server document root (e.g., `htdocs`, `www`, or `public_html`)
   - Ensure the `api` folder is accessible via web browser
   - Make sure URL rewriting is enabled (for Apache, ensure `mod_rewrite` is enabled)

4. **Test the API:**
   - Visit `http://localhost/your-project/api/products` to test if the API is working
   - You should see a JSON response with products

### Frontend Integration

Update your React frontend to use the PHP backend:

1. **Update API Base URL:**
   - In your React app, change the API base URL to point to your PHP backend
   - Example: `http://localhost/sreemeditec/api`

2. **Authentication:**
   - The backend uses JWT tokens for authentication
   - Login endpoint: `POST /api/auth/login`
   - Register endpoint: `POST /api/auth/register`

### Default Admin Account
- Email: `admin@sreemeditec.com`
- Password: `admin123`

### API Endpoints

#### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `POST /api/auth/forgot-password` - Password reset
- `GET /api/auth/profile` - Get user profile
- `PUT /api/auth/profile` - Update user profile

#### Products
- `GET /api/products` - Get all products (with filtering)
- `GET /api/products/{id}` - Get single product
- `POST /api/products` - Create product (admin only)
- `PUT /api/products/{id}` - Update product (admin only)
- `DELETE /api/products/{id}` - Delete product (admin only)

#### Cart
- `GET /api/cart` - Get user cart
- `POST /api/cart` - Add item to cart
- `PUT /api/cart/{id}` - Update cart item
- `DELETE /api/cart/{id}` - Remove cart item

#### Orders
- `GET /api/orders` - Get user orders
- `GET /api/orders/{id}` - Get single order
- `POST /api/orders` - Create new order
- `PUT /api/orders/{id}` - Update order status (admin only)

#### Admin
- `GET /api/admin/users` - Get all users
- `GET /api/admin/stats` - Get dashboard statistics

### Security Notes
- Change the JWT secret key in `api/utils/jwt.php`
- Use HTTPS in production
- Implement rate limiting for API endpoints
- Validate and sanitize all input data
- Use prepared statements (already implemented)

### Troubleshooting
- Ensure PHP extensions `pdo_mysql` and `json` are enabled
- Check file permissions for the API directory
- Verify database connection credentials
- Check Apache/Nginx error logs for detailed error messages
