# Sree Meditec E-commerce Local Setup Guide

This guide provides step-by-step instructions to set up and run the Sree Meditec e-commerce application locally.

## Project Overview

This is a full-stack e-commerce application built with:
- **Frontend**: React 18 with Vite, Tailwind CSS, Radix UI components
- **Backend**: PHP 8.2+ with MongoDB
- **Authentication**: JWT-based authentication
- **Payment Gateway**: Razorpay integration
- **Shipping**: DTDC courier integration

## Prerequisites

Before you begin, ensure you have the following installed on your system:

1. **Node.js** (version 18.0 or higher)
2. **PHP** (version 8.2 or higher)
3. **Composer** (PHP package manager)
4. **MongoDB** (or MongoDB Atlas account)

### For macOS:
```bash
# Install using Homebrew
brew install node php@8.2 composer mongodb-community
```

### For Ubuntu/Debian:
```bash
# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install PHP and MongoDB extension
sudo apt-get install php8.2 php8.2-cli php8.2-dev php8.2-mongodb composer

# Install MongoDB
sudo apt-get install mongodb
```

### For Windows:
1. Download and install Node.js from [nodejs.org](https://nodejs.org/)
2. Install PHP from [php.net](https://www.php.net/downloads)
3. Install Composer from [getcomposer.org](https://getcomposer.org/)
4. Install MongoDB from [mongodb.com](https://www.mongodb.com/try/download/community)

## Step-by-Step Setup Instructions

### 1. Clone and Navigate to Project
```bash
git clone <repository-url>
cd <project-directory>
```

### 2. Backend Setup (PHP API)

#### Install PHP Dependencies
```bash
cd php-backend
composer install --no-dev --ignore-platform-req=ext-mongodb
```

Note: If you have MongoDB PHP extension properly installed, you can omit the `--ignore-platform-req=ext-mongodb` flag.

#### Create Environment Configuration
Create a `.env` file in the `php-backend` directory:
```bash
cp .env.example .env  # If available, or create manually
```

Your `.env` file should contain:
```env
# Application Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:5000
APP_MODE=demo

# MongoDB Configuration
MONGODB_URI=mongodb://localhost:27017
MONGODB_DATABASE=sree_meditec_db

# JWT Configuration
JWT_SECRET=your-super-secret-jwt-key-change-in-production
JWT_EXPIRY=3600

# File Upload Configuration
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf,doc,docx

# Payment Configuration (Razorpay)
RAZORPAY_KEY_ID=your_razorpay_key_id
RAZORPAY_KEY_SECRET=your_razorpay_secret_key
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret

# Courier Configuration (DTDC)
DTDC_API_KEY=your_dtdc_api_key
DTDC_CLIENT_ID=your_dtdc_client_id
DTDC_SECRET=your_dtdc_secret
DTDC_BASE_URL=https://api.dtdc.com/v2
```

#### Start MongoDB (if running locally)
```bash
# macOS with Homebrew
brew services start mongodb-community

# Ubuntu/Debian
sudo systemctl start mongodb

# Windows (if installed as service)
net start MongoDB
```

#### Start Backend Server
```bash
# From php-backend directory
php -S localhost:8000 -t public/
```
The API will be available at `http://localhost:8000`

### 3. Frontend Setup (React)

#### Install Frontend Dependencies
```bash
cd client
npm install
```

#### Start Frontend Development Server
```bash
npm run dev
```
The frontend will be available at `http://localhost:5000`

## Required Terminal Commands Summary

Here are all the terminal commands you need to run to get the application working:

### Initial Setup (Run Once)
```bash
# 1. Install backend dependencies
cd php-backend
composer install --no-dev --ignore-platform-req=ext-mongodb

# 2. Install frontend dependencies  
cd ../client
npm install

# 3. Start MongoDB (if local installation)
# macOS: brew services start mongodb-community
# Ubuntu: sudo systemctl start mongodb
# Windows: net start MongoDB
```

### Daily Development (Run Every Time)
```bash
# Terminal 1: Start Backend API
cd php-backend
php -S localhost:8000 -t public/

# Terminal 2: Start Frontend (in new terminal)
cd client
npm run dev
```

## Demo Mode Credentials

When MongoDB is not available or properly configured, the application runs in demo mode with these credentials:

- **Email**: admin@sreemeditec.com
- **Password**: admin123

## API Endpoints

The backend provides the following main API endpoints:

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

### Products
- `GET /api/products` - Get all products
- `GET /api/products/{id}` - Get product by ID
- `POST /api/products` - Create product (admin only)

### Orders
- `GET /api/orders` - Get user orders
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order by ID

### Cart
- `GET /api/cart` - Get user cart
- `POST /api/cart/add` - Add item to cart
- `PUT /api/cart/update` - Update cart item
- `DELETE /api/cart/clear` - Clear cart

## Production Configuration

### Environment Variables for Production

Replace the following variables with actual production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_MODE=production
JWT_SECRET=generate-a-secure-random-secret-key
MONGODB_URI=your-production-mongodb-connection-string
RAZORPAY_KEY_ID=your-actual-razorpay-key
RAZORPAY_KEY_SECRET=your-actual-razorpay-secret
DTDC_API_KEY=your-actual-dtdc-api-key
```

### Building for Production

#### Frontend Build
```bash
cd client
npm run build
```

#### Backend Deployment
For production, use a proper web server like Apache or Nginx instead of PHP's built-in server.

## Troubleshooting

### Common Issues and Solutions

#### 1. "MongoDB extension not loaded"
- **Solution**: Install the MongoDB PHP extension or use demo mode
- The application will work in fallback mode without MongoDB for testing

#### 2. "Port already in use"
- **Solution**: Change the port numbers in the commands
```bash
# Backend on different port
php -S localhost:8001 -t public/

# Update vite.config.js proxy target to match new backend port
```

#### 3. "CORS errors"
- **Solution**: The backend is configured to handle CORS, but ensure frontend runs on port 5000
- Check `vite.config.js` proxy configuration

#### 4. "Proxy configuration issues" (Registration/Auth failed)
- **Solution**: Ensure the Vite proxy does NOT strip the `/api` prefix
- The backend expects routes to start with `/api/`, so the proxy must preserve this
- In `vite.config.js`, the proxy should NOT include a `rewrite` that removes `/api`
- Correct configuration: `proxy: { '/api': { target: 'http://localhost:8000', changeOrigin: true, secure: false } }`
- Incorrect: Any configuration that includes `rewrite: (path) => path.replace(/^\/api/, '')`

#### 5. "Authentication fails"
- **Solution**: Use demo credentials (admin@sreemeditec.com / admin123) in demo mode
- Check JWT_SECRET is set in .env file

#### 6. "Dependencies installation fails"
- **Solution**: Clear cache and reinstall
```bash
# PHP
cd php-backend
rm -rf vendor
composer clear-cache
composer install

# Node.js
cd client
rm -rf node_modules package-lock.json
npm install
```

## Development Workflow

1. **Start MongoDB** (if using local installation)
2. **Start Backend** in one terminal: `cd php-backend && php -S localhost:8000 -t public/`
3. **Start Frontend** in another terminal: `cd client && npm run dev`
4. **Access Application** at `http://localhost:5000`
5. **Test Authentication** using demo credentials

## Project Structure

```
├── client/                 # React frontend
│   ├── src/
│   │   ├── components/     # React components
│   │   ├── pages/          # Page components
│   │   ├── lib/            # API client and utilities
│   │   └── contexts/       # React contexts
│   └── vite.config.js      # Vite configuration
├── php-backend/            # PHP API backend
│   ├── public/             # Public entry point
│   ├── config/             # Configuration files
│   ├── models/             # Data models
│   ├── routes/             # API routes
│   └── includes/           # Helper functions
└── guide.md               # This setup guide
```

This guide should help you get the application running locally. If you encounter any issues not covered here, check the application logs for more detailed error messages.