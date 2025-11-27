# Website API Documentation

## Overview
This document provides comprehensive documentation for the Website API endpoints. These APIs are designed for website visitors and customers to browse products, view homepage content, and place orders without authentication.

## Base URL
```
/api/v1/website
```

## Authentication
Website APIs are **public** and do not require authentication. They are designed for anonymous website visitors and customers.

## Headers
- `Accept: application/json`
- `Content-Type: application/json` (for POST requests)
- `Accept-Language: ar|en` (optional, for localization)

---

## Endpoints

### 1. Homepage Data
**GET** `/api/v1/website/home`

Retrieve homepage data including active categories and featured products.

#### Query Parameters
None required.

#### Example Request
```bash
curl -X GET "http://localhost:8000/api/v1/website/home" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "categories": [
            {
                "categoryId": 1,
                "name": "إلكترونيات",
                "slug": "electronics",
                "description": "جميع المنتجات الإلكترونية"
            },
            {
                "categoryId": 2,
                "name": "ملابس",
                "slug": "clothing",
                "description": "ملابس رجالية ونسائية"
            }
        ],
        "products": [
            {
                "productId": 1,
                "name": "هاتف ذكي",
                "description": "هاتف ذكي بمواصفات عالية",
                "slug": "smartphone",
                "price": 1500.00,
                "brand": {
                    "id": 1,
                    "name": "سامسونج"
                },
                "category": {
                    "id": 1,
                    "name": "إلكترونيات",
                    "slug": "electronics"
                },
                "image": {
                    "url": "https://example.com/smartphone.jpg",
                    "type": "image"
                },
                "hasStock": true,
                "stockQuantity": 25,
                "stockStatus": 2
            }
        ]
    }
}
```

---

### 2. Products List
**GET** `/api/v1/website/products`

Retrieve a paginated list of active products with advanced filtering and sorting.

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `filter[search]` | string | No | Search in product name, description, brand, or category |
| `filter[category]` | integer | No | Filter by category ID |
| `filter[price]` | string | No | Filter by price range (format: min,max) |
| `sort` | string | No | Sort products (latest, oldest, price_low, price_high, name) |
| `perPage` | integer | No | Items per page (1-50, default: 15) |
| `page` | integer | No | Page number (default: 1) |

#### Sorting Options
- `latest` or `-latest`: Newest first (default)
- `oldest` or `-oldest`: Oldest first
- `price_low`: Price low to high
- `price_high` or `-price_low`: Price high to low
- `name` or `-name`: Alphabetical order

#### Example Request
```bash
curl -X GET "http://localhost:8000/api/v1/website/products?filter[search]=هاتف&filter[category]=1&sort=price_low&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "products": [
            {
                "productId": 1,
                "name": "هاتف ذكي",
                "description": "هاتف ذكي بمواصفات عالية",
                "slug": "smartphone",
                "price": 1500.00,
                "brand": {
                    "id": 1,
                    "name": "سامسونج"
                },
                "category": {
                    "id": 1,
                    "name": "إلكترونيات",
                    "slug": "electronics"
                },
                "image": {
                    "url": "https://example.com/smartphone.jpg",
                    "type": "image"
                },
                "hasStock": true,
                "stockQuantity": 25,
                "stockStatus": 2
            }
        ]
    }
}
```

---

### 3. Product Details
**GET** `/api/v1/website/products/{slug}`

Retrieve detailed information about a specific product using its slug.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Product slug |

#### Example Request
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/smartphone" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "productId": 1,
        "name": "هاتف ذكي",
        "description": "هاتف ذكي بمواصفات عالية مع كاميرا متطورة وبطارية طويلة المدى",
        "slug": "smartphone",
        "price": 1500.00,
        "brand": {
            "id": 1,
            "name": "سامسونج"
        },
        "category": {
            "id": 1,
            "name": "إلكترونيات",
            "slug": "electronics"
        },
        "image": {
            "url": "https://example.com/smartphone.jpg",
            "type": "image"
        },
        "hasStock": true,
        "stockQuantity": 25,
        "stockStatus": 2
    }
}
```

---

### 4. Related Products
**GET** `/api/v1/website/products/{slug}/related`

Retrieve products related to the specified product based on category.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Product slug |

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `limit` | integer | No | Number of related products (1-20, default: 4) |

#### Example Request
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/smartphone/related?limit=6" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "products": [
            {
                "productId": 2,
                "name": "هاتف آخر",
                "slug": "another-phone",
                "price": 1200.00,
                "image": {
                    "url": "https://example.com/phone2.jpg"
                },
                "stockQuantity": 15
            },
            {
                "productId": 3,
                "name": "تابلت",
                "slug": "tablet",
                "price": 800.00,
                "image": {
                    "url": "https://example.com/tablet.jpg"
                },
                "stockQuantity": 8
            }
        ]
    }
}
```

---

### 5. Create Order
**POST** `/api/v1/website/orders`

Create a new order for website customers with automatic client creation if needed.

#### Request Body
```json
{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "+201234567890",
    "address": "شارع النيل، المعادي",
    "city": "القاهرة",
    "note": "طلب عاجل",
    "orderItems": [
        {
            "productId": 1,
            "quantity": 2
        },
        {
            "productId": 2,
            "quantity": 1
        }
    ]
}
```

#### Validation Rules
- `name`: Required, string, max 255 characters
- `email`: Required, valid email, max 255 characters
- `phone`: Required, string, max 20 characters
- `address`: Required, string, max 500 characters
- `city`: Optional, string, max 100 characters
- `note`: Optional, string, max 1000 characters
- `orderItems`: Required array, minimum 1 item
- `orderItems.*.productId`: Required, integer, must exist in products table
- `orderItems.*.quantity`: Required, integer, minimum 1, maximum 100

#### Example Request
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "+201234567890",
    "address": "شارع النيل، المعادي",
    "city": "القاهرة",
    "orderItems": [
      {
        "productId": 1,
        "quantity": 2
      }
    ]
  }'
```

#### Example Response
```json
{
    "success": true,
    "message": "تم إنشاء الطلب بنجاح",
    "data": {
        "orderNumber": "ORD-11202025-1234"
    }
}
```

---

### 6. Validate Cart
**POST** `/api/v1/website/orders/validate-cart`

Validate cart items availability, stock, and calculate totals before placing an order.

#### Request Body
```json
{
    "items": [
        {
            "productId": 1,
            "quantity": 2
        },
        {
            "productId": 2,
            "quantity": 1
        }
    ]
}
```

#### Validation Rules
- `items`: Required array, minimum 1 item
- `items.*.productId`: Required, integer, must exist in products table
- `items.*.quantity`: Required, integer, minimum 1

#### Example Request
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/validate-cart" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -d '{
    "items": [
      {
        "productId": 1,
        "quantity": 2
      },
      {
        "productId": 2,
        "quantity": 1
      }
    ]
  }'
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "isValid": true,
        "totalAmount": 3200.00,
        "items": [
            {
                "productId": 1,
                "requestedQuantity": 2,
                "isValid": true,
                "errors": [],
                "subtotal": 3000.00,
                "unitPrice": 1500.00,
                "product": {
                    "id": 1,
                    "name": "هاتف ذكي",
                    "price": 1500.00,
                    "image": "https://example.com/smartphone.jpg",
                    "availableQuantity": 25
                }
            },
            {
                "productId": 2,
                "requestedQuantity": 1,
                "isValid": true,
                "errors": [],
                "subtotal": 200.00,
                "unitPrice": 200.00,
                "product": {
                    "id": 2,
                    "name": "سماعات",
                    "price": 200.00,
                    "image": "https://example.com/headphones.jpg",
                    "availableQuantity": 10
                }
            }
        ]
    }
}
```

#### Cart Validation with Errors
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "isValid": false,
        "totalAmount": 1500.00,
        "items": [
            {
                "productId": 1,
                "requestedQuantity": 2,
                "isValid": true,
                "errors": [],
                "subtotal": 3000.00,
                "unitPrice": 1500.00,
                "product": {
                    "id": 1,
                    "name": "هاتف ذكي",
                    "price": 1500.00,
                    "image": "https://example.com/smartphone.jpg",
                    "availableQuantity": 25
                }
            },
            {
                "productId": 3,
                "requestedQuantity": 10,
                "isValid": false,
                "errors": ["الكمية المطلوبة غير متوفرة"],
                "availableQuantity": 3,
                "product": {
                    "id": 3,
                    "name": "منتج محدود",
                    "price": 100.00,
                    "image": null,
                    "availableQuantity": 3
                }
            }
        ]
    }
}
```

---

## Data Structures

### Product Object
```json
{
    "productId": 1,
    "name": "منتج تجريبي",
    "description": "وصف المنتج",
    "slug": "sample-product",
    "price": 100.00,
    "brand": {
        "id": 1,
        "name": "براند تجريبي"
    },
    "category": {
        "id": 1,
        "name": "فئة تجريبية",
        "slug": "sample-category"
    },
    "image": {
        "url": "https://example.com/image.jpg",
        "type": "image"
    },
    "hasStock": true,
    "stockQuantity": 10,
    "stockStatus": 2
}
```

### Category Object
```json
{
    "categoryId": 1,
    "name": "فئة تجريبية",
    "slug": "sample-category",
    "description": "وصف الفئة"
}
```

### Stock Status Values
- `0`: Out of stock (نفد المخزون)
- `1`: Low stock (مخزون منخفض)
- `2`: In stock (متوفر)
- `3`: No inventory tracking (لا يوجد تتبع مخزون)

---

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "data": {
        "name": [
            "الاسم مطلوب"
        ],
        "email": [
            "البريد الإلكتروني غير صحيح"
        ]
    }
}
```

### Product Not Available (400)
```json
{
    "success": false,
    "message": "المنتج غير متوفر: Product Name",
    "data": []
}
```

### Product Out of Stock (400)
```json
{
    "success": false,
    "message": "المنتج غير متوفر في المخزون: Product Name",
    "data": []
}
```

### Product Not Found (404)
```json
{
    "success": false,
    "message": "Product not found"
}
```

### Internal Server Error (500)
```json
{
    "success": false,
    "message": "حدث خطأ أثناء إنشاء الطلب",
    "data": {
        "error": "Database connection failed"
    }
}
```

---

## Business Logic

### Product Filtering
- Only active products are shown (`status = active`)
- Only products with available stock are displayed
- Products without stock tracking (`has_stock = false`) are always shown

### Order Creation
- Clients are automatically created if they don't exist (based on email)
- Existing clients are updated with new information if provided
- Stock availability is checked before order creation
- Orders are created with `PENDING` status
- No discounts are applied to website orders initially

### Stock Management
- Stock is not reduced when orders are created from website
- Stock reduction happens when admin approves the order
- Real-time stock validation during cart validation

### Price Filtering
- Price range format: `min,max` (e.g., `100,500`)
- Both min and max values are inclusive
- Invalid format returns validation error

---

## Frontend Integration Examples

### Product Listing with Filters
```javascript
// Fetch products with filters
const fetchProducts = async (filters = {}) => {
    const params = new URLSearchParams();
    
    if (filters.search) params.append('filter[search]', filters.search);
    if (filters.category) params.append('filter[category]', filters.category);
    if (filters.priceRange) params.append('filter[price]', filters.priceRange);
    if (filters.sort) params.append('sort', filters.sort);
    if (filters.perPage) params.append('perPage', filters.perPage);
    
    const response = await fetch(`/api/v1/website/products?${params}`, {
        headers: {
            'Accept': 'application/json',
            'Accept-Language': 'ar'
        }
    });
    
    return await response.json();
};

// Usage
const products = await fetchProducts({
    search: 'هاتف',
    category: 1,
    sort: 'price_low',
    perPage: 12
});
```

### Cart Validation
```javascript
// Validate cart before checkout
const validateCart = async (cartItems) => {
    const response = await fetch('/api/v1/website/orders/validate-cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Accept-Language': 'ar'
        },
        body: JSON.stringify({
            items: cartItems.map(item => ({
                productId: item.productId,
                quantity: item.quantity
            }))
        })
    });
    
    return await response.json();
};

// Usage
const cartItems = [
    { productId: 1, quantity: 2 },
    { productId: 2, quantity: 1 }
];

const validation = await validateCart(cartItems);
if (validation.data.isValid) {
    // Proceed to checkout
    console.log('Total:', validation.data.totalAmount);
} else {
    // Show validation errors
    validation.data.items.forEach(item => {
        if (!item.isValid) {
            console.log('Errors for product', item.productId, ':', item.errors);
        }
    });
}
```

### Order Creation
```javascript
// Create order
const createOrder = async (orderData) => {
    const response = await fetch('/api/v1/website/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Accept-Language': 'ar'
        },
        body: JSON.stringify(orderData)
    });
    
    return await response.json();
};

// Usage
const orderData = {
    name: 'أحمد محمد',
    email: 'ahmed@example.com',
    phone: '+201234567890',
    address: 'شارع النيل، المعادي',
    city: 'القاهرة',
    note: 'طلب عاجل',
    orderItems: [
        { productId: 1, quantity: 2 },
        { productId: 2, quantity: 1 }
    ]
};

const result = await createOrder(orderData);
if (result.success) {
    console.log('Order created:', result.data.orderNumber);
    // Redirect to success page or show confirmation
} else {
    // Handle validation errors
    console.log('Validation errors:', result.data);
}
```

---

## SEO Considerations

### URL Structure
- Products use SEO-friendly slugs: `/products/smartphone`
- Categories use slugs: `/categories/electronics`
- Clean, readable URLs for better SEO

### Meta Data
Consider adding meta data endpoints for:
- Product meta titles and descriptions
- Category meta information
- Homepage meta data

### Pagination
- Implement proper pagination for better crawling
- Use consistent page numbering
- Consider implementing infinite scroll with proper URL updates

---

## Performance Optimization

### Caching Strategy
```javascript
// Client-side caching example
const cache = new Map();

const fetchWithCache = async (url, cacheKey, ttl = 300000) => { // 5 minutes TTL
    const cached = cache.get(cacheKey);
    if (cached && Date.now() - cached.timestamp < ttl) {
        return cached.data;
    }
    
    const response = await fetch(url);
    const data = await response.json();
    
    cache.set(cacheKey, {
        data,
        timestamp: Date.now()
    });
    
    return data;
};

// Usage
const homepage = await fetchWithCache('/api/v1/website/home', 'homepage');
```

### Image Optimization
- Use appropriate image sizes for different contexts
- Implement lazy loading for product images
- Consider WebP format for better compression

### Database Optimization
- Proper indexing on frequently queried fields
- Eager loading for relationships
- Query optimization for filtering and sorting

---

## Rate Limiting
Website APIs may be subject to rate limiting to prevent abuse:
- Homepage: 60 requests per minute
- Products: 120 requests per minute
- Orders: 10 requests per minute
- Cart validation: 30 requests per minute

## CORS Configuration
Ensure proper CORS configuration for frontend domains:
```php
// In config/cors.php
'allowed_origins' => [
    'https://yourwebsite.com',
    'https://www.yourwebsite.com'
],
```

## Security Notes
- Input validation on all endpoints
- SQL injection prevention through Eloquent ORM
- XSS protection through proper output encoding
- Rate limiting to prevent abuse
- Proper error handling without exposing sensitive information
