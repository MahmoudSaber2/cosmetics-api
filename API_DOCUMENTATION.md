# Cosmetics E-commerce API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

The API uses Laravel Sanctum for authentication. Include the token in the Authorization header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Response Format

All API responses follow this format:

```json
{
    "success": true|false,
    "data": {},
    "message": "Response message",
    "locale": "en",
    "direction": "ltr"
}
```

## Error Response Format

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Error details"]
    }
}
```

---

## Authentication Endpoints

### Login (Admin)

**POST** `/public/auth/login`

**Request Body:**

```json
{
    "email": "admin@admin.com",
    "password": "mans123456"
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@admin.com",
            "role": "admin"
        },
        "token": "6|excl7gSdU5wZCRTohijyEqpBfDhxF43QTzXBdefNc5aa53f2"
    },
    "message": "Success"
}
```

### Logout (Admin)

**POST** `/public/auth/logout`

- Requires Authentication

### Get Current User

**GET** `/public/auth/user`

- Requires Authentication

---

## Public API Endpoints (No Authentication Required)

### Products

#### Get All Products

**GET** `/public/products`

**Query Parameters:**

- `search` - Search in name, brand, type, description
- `brand` - Filter by brand
- `type` - Filter by type
- `gender` - Filter by gender (men, women, unisex)
- `color` - Filter by color
- `size` - Filter by size
- `brands` - Multiple brands (comma-separated)
- `types` - Multiple types (comma-separated)
- `colors` - Multiple colors (comma-separated)
- `sizes` - Multiple sizes (comma-separated)
- `min_price` - Minimum price
- `max_price` - Maximum price
- `in_stock` - Filter by stock availability (true/false)
- `sort_by` - Sort field (name, brand, type, price, created_at)
- `sort_order` - Sort direction (asc, desc)
- `per_page` - Items per page (max 50)

**Example:**

```
GET /public/products?search=lipstick&brand=ClassicBeauty&in_stock=true&per_page=12
```

#### Get Single Product

**GET** `/public/products/{id}`

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 6,
        "name": "Classic Red Lipstick",
        "description": "Timeless red lipstick with creamy texture and rich pigmentation.",
        "brand": "ClassicBeauty",
        "type": "lipstick",
        "color": "Classic Red",
        "size": "3.8g",
        "gender": "women",
        "price": "29.50",
        "image_url": null,
        "status": "active",
        "inventory": {
            "stock_quantity": 100,
            "min_stock_level": 10
        },
        "in_stock": true,
        "is_low_stock": false,
        "created_at": "2025-10-29T07:34:12.000000Z",
        "updated_at": "2025-10-29T07:34:12.000000Z"
    }
}
```

#### Check Product Availability

**POST** `/public/products/{id}/availability`

**Request Body:**

```json
{
    "quantity": 5
}
```

#### Get Product Stock

**GET** `/public/products/{id}/stock`

#### Search Products

**GET** `/public/products-search`

**Query Parameters:**

- `q` - Search term (required, min 2 chars)
- `limit` - Max results (default 10, max 20)
- `gender` - Filter by gender
- `type` - Filter by type
- `brand` - Filter by brand

#### Get Featured Products

**GET** `/public/products-featured`

**Query Parameters:**

- `limit` - Max results (default 8, max 20)

#### Get Filter Options

**GET** `/public/products-filters`

**Response:**

```json
{
    "success": true,
    "data": {
        "brands": ["ClassicBeauty", "LashPerfect", "EyeArt"],
        "types": ["lipstick", "mascara", "eyeshadow"],
        "colors": ["Classic Red", "Black", "Brown"],
        "sizes": ["3.8g", "10ml", "15g"],
        "genders": ["men", "women", "unisex"],
        "price_range": {
            "min": 15.99,
            "max": 55.00
        }
    }
}
```

### Orders

#### Create Order

**POST** `/public/orders`

**Request Body:**

```json
{
    "client": {
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "address": "123 Main St",
        "city": "New York"
    },
    "items": [
        {
            "product_id": 6,
            "quantity": 2
        },
        {
            "product_id": 10,
            "quantity": 1
        }
    ],
    "notes": "Please handle with care"
}
```

#### Get Order Details

**GET** `/public/orders/{id}`

#### Track Orders by Email

**GET** `/public/orders-track?email=john@example.com`

#### Validate Cart

**POST** `/public/orders-validate-cart`

**Request Body:**

```json
{
    "items": [
        {
            "product_id": 6,
            "quantity": 2
        }
    ]
}
```

### Clients

#### Register Client

**POST** `/public/clients`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St",
    "city": "New York"
}
```

#### Get Client by Email

**GET** `/public/clients/by-email?email=john@example.com`

---

## Admin API Endpoints (Authentication Required)

### Products Management

#### Get All Products (Admin)

**GET** `/admin/products`

**Query Parameters:** (Same as public + additional admin filters)

- `stock_status` - Filter by stock status (in_stock, out_of_stock, low_stock)
- `status` - Filter by product status (active, inactive)

#### Create Product

**POST** `/admin/products`

**Request Body (multipart/form-data):**

```json
{
    "name": "New Product",
    "description": "Product description",
    "brand": "Brand Name",
    "type": "product_type",
    "color": "Red",
    "size": "50ml",
    "gender": "unisex",
    "price": 29.99,
    "status": "active",
    "stock_quantity": 50,
    "min_stock_level": 10,
    "image": "file_upload"
}
```

#### Update Product

**PUT/PATCH** `/admin/products/{id}`

#### Delete Product

**DELETE** `/admin/products/{id}`

#### Get Product Categories

**GET** `/admin/products-categories`

#### Get Product Brands

**GET** `/admin/products-brands`

#### Get Product Colors

**GET** `/admin/products-colors`

#### Bulk Update Product Status

**PATCH** `/admin/products-bulk-status`

**Request Body:**

```json
{
    "product_ids": [1, 2, 3],
    "status": "inactive"
}
```

### Inventory Management

#### Get All Inventory

**GET** `/admin/inventory`

**Query Parameters:**

- `stock_status` - Filter by stock status (in_stock, out_of_stock, low_stock)
- `search` - Search in product name or brand
- `brand` - Filter by product brand
- `type` - Filter by product type
- `product_status` - Filter by product status
- `min_stock` - Minimum stock quantity
- `max_stock` - Maximum stock quantity
- `sort_by` - Sort field (stock_quantity, min_stock_level, updated_at, product_name)
- `sort_order` - Sort direction (asc, desc)
- `per_page` - Items per page (max 100)

#### Get Single Inventory Item

**GET** `/admin/inventory/{inventory_id}`

#### Update Inventory Item

**PATCH** `/admin/inventory/{inventory_id}`

**Request Body:**

```json
{
    "stock_quantity": 50,
    "min_stock_level": 10
}
```

#### Update Product Stock

**PATCH** `/admin/inventory/products/{product_id}/stock`

**Request Body:**

```json
{
    "stock_quantity": 100,
    "operation": "set"
}
```

**Operations:**

- `set` - Set exact quantity
- `add` - Add to current quantity
- `subtract` - Subtract from current quantity

#### Get Low Stock Alerts

**GET** `/admin/inventory-low-stock`

#### Get Out of Stock Items

**GET** `/admin/inventory-out-of-stock`

#### Get Inventory Statistics

**GET** `/admin/inventory-statistics`

**Response:**

```json
{
    "success": true,
    "data": {
        "total_products": 20,
        "in_stock_products": 18,
        "out_of_stock_products": 0,
        "low_stock_products": 2,
        "total_stock_value": 15420.50,
        "average_stock_level": 42.5,
        "products_needing_restock": 2
    }
}
```

#### Bulk Update Stock

**PATCH** `/admin/inventory-bulk-update`

**Request Body:**

```json
{
    "updates": [
        {
            "product_id": 1,
            "stock_quantity": 50,
            "operation": "set"
        },
        {
            "product_id": 2,
            "stock_quantity": 10,
            "operation": "add"
        }
    ]
}
```

#### Get Stock Movements

**GET** `/admin/inventory-movements`

#### Generate Inventory Report

**GET** `/admin/inventory-report`

**Query Parameters:**

- `format` - Report format (summary, detailed)
- `include_inactive` - Include inactive products (true/false)

### Orders Management

#### Get All Orders

**GET** `/admin/orders`

#### Create Order (Admin)

**POST** `/admin/orders`

#### Get Single Order

**GET** `/admin/orders/{id}`

#### Update Order

**PUT/PATCH** `/admin/orders/{id}`

#### Delete Order

**DELETE** `/admin/orders/{id}`

#### Approve Order

**PATCH** `/admin/orders/{id}/approve`

#### Reject Order

**PATCH** `/admin/orders/{id}/reject`

**Request Body:**

```json
{
    "reason": "Out of stock"
}
```

#### Complete Order

**PATCH** `/admin/orders/{id}/complete`

#### Get Order Statistics

**GET** `/admin/orders-statistics`

#### Get Order Status Counts

**GET** `/admin/orders-status-counts`

### Clients Management

#### Get All Clients

**GET** `/admin/clients`

#### Create Client

**POST** `/admin/clients`

#### Get Single Client

**GET** `/admin/clients/{id}`

#### Update Client

**PUT/PATCH** `/admin/clients/{id}`

#### Delete Client

**DELETE** `/admin/clients/{id}`

#### Get Client Orders

**GET** `/admin/clients/{id}/orders`

#### Get Client Statistics

**GET** `/admin/clients/{id}/statistics`

### Users Management

#### Get All Users

**GET** `/admin/users`

#### Create User

**POST** `/admin/users`

#### Get Single User

**GET** `/admin/users/{id}`

#### Update User

**PUT/PATCH** `/admin/users/{id}`

#### Delete User

**DELETE** `/admin/users/{id}`

---

## File Upload

#### Upload File

**POST** `/upload`

- Requires Authentication
- Content-Type: multipart/form-data

**Request Body:**

```json
{
    "file": "file_upload",
    "type": "image",
    "folder": "products"
}
```

#### Delete File

**DELETE** `/upload`

- Requires Authentication

#### Get Upload Info

**GET** `/upload/info`

- Requires Authentication

---

## Localization

#### Get Available Locales

**GET** `/locale`

#### Get Translations

**GET** `/locale/translations`

#### Get Validation Messages

**GET** `/locale/validation`

#### Get Auth Messages

**GET** `/locale/auth`

---

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

---

## Rate Limiting

- Public API: 60 requests per minute
- Admin API: No limit (authenticated users)

---

## CORS Configuration

The API accepts requests from:

- `http://localhost:3000` (Admin Dashboard)
- `http://localhost:3001` (Public Store)
- `http://127.0.0.1:3000`
- `http://127.0.0.1:3001`

---

## Default Admin Users

1. **Main Admin**
   - Email: `admin@admin.com`
   - Password: `mans123456`

2. **Store Manager**
   - Email: `manager@cosmetics.com`
   - Password: `password`

3. **Inventory Manager**
   - Email: `inventory@cosmetics.com`
   - Password: `password`

---

## Testing Examples

### Test Product Creation

```bash
curl -X POST "http://localhost:8000/api/admin/products" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "brand": "Test Brand",
    "type": "lipstick",
    "gender": "women",
    "price": 25.99,
    "status": "active",
    "stock_quantity": 50,
    "min_stock_level": 10
  }'
```

### Test Stock Update

```bash
curl -X PATCH "http://localhost:8000/api/admin/inventory/products/1/stock" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "stock_quantity": 100,
    "operation": "set"
  }'
```

### Test Order Creation

```bash
curl -X POST "http://localhost:8000/api/public/orders" \
  -H "Content-Type: application/json" \
  -d '{
    "client": {
      "name": "Test Customer",
      "email": "test@example.com",
      "phone": "+1234567890"
    },
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      }
    ]
  }'
```
