# Cosmetics E-commerce API

A Laravel-based REST API for a cosmetics e-commerce platform with admin dashboard and public store functionality.

## Features

- Laravel 12 with Sanctum authentication
- MySQL/SQLite database support
- CORS configuration for Next.js applications
- Admin and public API endpoints
- Role-based access control (admin/manager)

## Setup Instructions

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL (optional, SQLite is configured by default)

### Installation

1. Install dependencies:

```bash
composer install
```

2. Copy environment file:

```bash
cp .env.example .env
```

3. Generate application key:

```bash
php artisan key:generate
```

4. Run migrations:

```bash
php artisan migrate
```

5. Seed the database with initial admin user:

```bash
php artisan db:seed
```

6. Start the development server:

```bash
php artisan serve --port=8000
```

### Database Configuration

#### MySQL (Production)

The project is configured to use MySQL database hosted on srv1469.hstgr.io. The database connection is already configured in the `.env` file.

#### SQLite (Local Development - Optional)

For local development, you can switch to SQLite by updating the `.env` file:

```env
DB_CONNECTION=sqlite
# Comment out MySQL configuration
```

### Default Admin User

- Email: `admin@cosmetics.com`
- Password: `password`
- Role: `admin`

## API Endpoints

### Authentication

- `POST /api/login` - Admin login
- `POST /api/logout` - Admin logout (requires authentication)
- `GET /api/user` - Get authenticated user (requires authentication)

### Public API (No authentication required)

- `GET /api/public/products` - List products
- `GET /api/public/products/{id}` - Get product details
- `POST /api/public/clients` - Register client
- `POST /api/public/orders` - Create order

### Admin API (Authentication required)

- `GET /api/admin/users` - List admin users
- `GET /api/admin/products` - List all products
- `GET /api/admin/orders` - List all orders
- `GET /api/admin/clients` - List all clients
- `GET /api/admin/inventory` - List inventory levels

## CORS Configuration

The API is configured to accept requests from:

- `http://localhost:3000` (Admin Dashboard)
- `http://localhost:3001` (Public Store)
- `http://127.0.0.1:3000`
- `http://127.0.0.1:3001`

## Testing the API

### Login Example

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@cosmetics.com", "password": "password"}'
```

### Authenticated Request Example

```bash
curl -X GET http://127.0.0.1:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Next Steps

This is the foundation setup. The following tasks will implement:

1. Database models and migrations
2. Complete API endpoints
3. Business logic and validation
4. File upload handling
5. Next.js applications (Admin Dashboard and Public Store)

## Project Structure

```
cosmetics-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           ├── Admin/          # Admin-specific controllers
│   │           ├── Public/         # Public API controllers
│   │           ├── AuthController.php
│   │           └── BaseApiController.php
│   └── Models/
├── config/
│   ├── cors.php                    # CORS configuration
│   └── sanctum.php                 # Sanctum configuration
├── database/
│   ├── migrations/
│   └── seeders/
└── routes/
    └── api.php                     # API routes
```
