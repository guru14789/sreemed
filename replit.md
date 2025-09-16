# Overview

This is a comprehensive e-commerce backend system for Sree Meditec, a medical equipment company. The project consists of a PHP 8+ backend with MongoDB as the primary database and a React frontend client. The backend is designed to be production-ready, modular, secure, and scalable, providing a complete e-commerce solution with features like user authentication, product management, order processing, payment integration, and admin dashboard capabilities.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Backend Architecture (PHP 8+)
- **Framework**: Plain PHP with Composer dependency management
- **Structure**: MVC pattern with PSR-4 autoloading
- **Entry Point**: Single entry point through `public/` directory
- **Dependencies**: Core libraries include MongoDB driver, JWT authentication, PHPMailer, and environment configuration management

## Database Design
- **Primary Database**: MongoDB for flexible document storage
- **Collections**: Designed to support users, products, orders, categories, and admin data
- **Connection**: Centralized database connection through configuration layer
- **Data Modeling**: Document-based approach suitable for product catalogs and user profiles

## Authentication & Security
- **Dual Authentication**: Session-based + JWT token authentication
- **Password Security**: Bcrypt/Argon2 hashing for user passwords
- **Role-Based Access**: User and Admin role differentiation
- **Input Validation**: Server-side validation and sanitization

## API Structure
- **RESTful Design**: Standard HTTP methods for CRUD operations
- **Response Format**: JSON-based API responses
- **Error Handling**: Structured error responses with appropriate HTTP status codes
- **Rate Limiting**: Built-in protection against abuse

## Frontend Architecture (React)
- **Framework**: React 18 with modern hooks
- **Build Tool**: Vite for fast development and optimized builds
- **Styling**: Tailwind CSS with custom design system
- **State Management**: React hooks for local state management
- **Routing**: React Router for client-side navigation

## File Upload & Storage
- **Image Handling**: Local file storage in `/uploads/` directory
- **Profile Pictures**: User avatar upload and management
- **Product Images**: Multiple image support for products
- **File Validation**: Type and size restrictions for security

## E-commerce Features
- **Product Management**: Full CRUD operations for products and categories
- **Shopping Cart**: Persistent cart system linked to user accounts
- **Order Processing**: Complete order lifecycle management
- **Inventory Tracking**: Stock management and availability checking

# External Dependencies

## Payment Processing
- **Razorpay Integration**: Complete payment gateway integration for Indian market
- **Webhook Handling**: Automatic order status updates via Razorpay webhooks
- **Payment Security**: Server-side payment verification and capture

## Shipping & Logistics
- **DTDC Integration**: Courier service integration for order fulfillment
- **Tracking System**: Order tracking and status updates
- **Shipping Calculations**: Dynamic shipping cost calculation

## Email Services
- **PHPMailer**: SMTP-based email sending
- **Notification System**: Order confirmations, password reset, and user communications
- **Template System**: Email template management for consistent branding

## Development Tools
- **Composer**: PHP dependency management
- **Environment Configuration**: `.env` file for secure configuration management
- **Database Abstraction**: MongoDB PHP driver for database operations
- **Session Management**: PHP sessions with secure configuration

## Third-Party Libraries
- **JWT Library**: Firebase JWT for token-based authentication
- **UUID Generation**: Ramsey UUID for unique identifier generation
- **Testing Framework**: PHPUnit for backend testing
- **Code Quality**: Various development dependencies for code analysis and formatting