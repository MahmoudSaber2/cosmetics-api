# Website API Testing Guide

## Overview
This document provides comprehensive testing scenarios for the Website API endpoints using various tools like Postman, cURL, and automated testing frameworks. These APIs are public and designed for website visitors.

## Prerequisites
- Test database with sample data (products, categories, clients)
- API base URL: `http://localhost:8000/api/v1/website`
- **No authentication required** for website APIs

---

## Test Scenarios

### 1. Homepage Data Tests

#### Test 1.1: Get Homepage Data (Basic)
```bash
curl -X GET "http://localhost:8000/api/v1/website/home" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Contains categories array with active categories
- Contains products array with featured products (max 9)
- Each category has: categoryId, name, slug, description
- Each product has: productId, name, slug, price, brand, category, image, stock info

#### Test 1.2: Homepage Data Structure Validation
```bash
curl -X GET "http://localhost:8000/api/v1/website/home" \
  -H "Accept: application/json"
```

**Validation Points:**
- Response has `success: true`
- Data contains both `categories` and `products` arrays
- Categories are sorted by name
- Products are sorted by creation date (latest first)
- Only active categories and products are returned

#### Test 1.3: Homepage with Different Languages
```bash
# Arabic
curl -X GET "http://localhost:8000/api/v1/website/home" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"

# English
curl -X GET "http://localhost:8000/api/v1/website/home" \
  -H "Accept: application/json" \
  -H "Accept-Language: en"
```

**Expected Response:**
- Consistent data structure regardless of language
- Localized content if available

---

### 2. Products List Tests

#### Test 2.1: Get All Products (Basic)
```bash
curl -X GET "http://localhost:8000/api/v1/website/products" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Contains products array
- Only active products with available stock
- Default sorting by creation date (newest first)
- Default pagination (15 items per page)

#### Test 2.2: Products with Search Filter
```bash
curl -X GET "http://localhost:8000/api/v1/website/products?filter[search]=هاتف" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Only products matching search term in name, description, brand, or category
- Case-insensitive search

#### Test 2.3: Products with Category Filter
```bash
curl -X GET "http://localhost:8000/api/v1/website/products?filter[category]=1" \
  -H "Accept: application/json"
```

**Expected Response:**
- Only products from specified category
- Valid category ID required

#### Test 2.4: Products with Price Range Filter
```bash
curl -X GET "http://localhost:8000/api/v1/website/products?filter[price]=100,500" \
  -H "Accept: application/json"
```

**Expected Response:**
- Only products within specified price range (inclusive)
- Format: min,max

#### Test 2.5: Products with Sorting
```bash
# Price low to high
curl -X GET "http://localhost:8000/api/v1/website/products?sort=price_low" \
  -H "Accept: application/json"

# Price high to low
curl -X GET "http://localhost:8000/api/v1/website/products?sort=price_high" \
  -H "Accept: application/json"

# Alphabetical
curl -X GET "http://localhost:8000/api/v1/website/products?sort=name" \
  -H "Accept: application/json"

# Latest first
curl -X GET "http://localhost:8000/api/v1/website/products?sort=latest" \
  -H "Accept: application/json"
```

**Expected Response:**
- Products sorted according to specified criteria

#### Test 2.6: Products with Pagination
```bash
curl -X GET "http://localhost:8000/api/v1/website/products?perPage=5&page=2" \
  -H "Accept: application/json"
```

**Expected Response:**
- Maximum 5 products
- Second page of results

#### Test 2.7: Products with Combined Filters
```bash
curl -X GET "http://localhost:8000/api/v1/website/products?filter[search]=هاتف&filter[category]=1&filter[price]=500,2000&sort=price_low&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Products matching all specified criteria
- Proper combination of filters

#### Test 2.8: Invalid Filter Values
```bash
# Invalid category
curl -X GET "http://localhost:8000/api/v1/website/products?filter[category]=999" \
  -H "Accept: application/json"

# Invalid price format
curl -X GET "http://localhost:8000/api/v1/website/products?filter[price]=invalid" \
  -H "Accept: application/json"

# Invalid sort option
curl -X GET "http://localhost:8000/api/v1/website/products?sort=invalid_sort" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 422 for validation errors
- Appropriate error messages

---

### 3. Product Details Tests

#### Test 3.1: Get Product by Slug (Valid)
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/smartphone" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Complete product details
- Includes brand, category, image, stock information

#### Test 3.2: Get Product by Invalid Slug
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/non-existent-product" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 404
- Product not found error

#### Test 3.3: Get Inactive Product
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/inactive-product-slug" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 404 or filtered out
- Inactive products should not be accessible

---

### 4. Related Products Tests

#### Test 4.1: Get Related Products (Valid)
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/smartphone/related" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Products from same category
- Excludes the original product
- Default limit of 4 products

#### Test 4.2: Related Products with Custom Limit
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/smartphone/related?limit=6" \
  -H "Accept: application/json"
```

**Expected Response:**
- Maximum 6 related products
- Same category as original product

#### Test 4.3: Related Products for Invalid Slug
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/non-existent/related" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 404
- Product not found error

#### Test 4.4: Related Products with No Related Items
```bash
curl -X GET "http://localhost:8000/api/v1/website/products/unique-category-product/related" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Empty products array

---

### 5. Order Creation Tests

#### Test 5.1: Create Order (Valid Data)
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
  }'
```

**Expected Response:**
- Status: 201
- Success message in Arabic
- Order number returned
- Client created or updated

#### Test 5.2: Create Order for Existing Client
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "عميل موجود",
    "email": "existing@example.com",
    "phone": "+201234567891",
    "address": "عنوان جديد",
    "orderItems": [
      {
        "productId": 1,
        "quantity": 1
      }
    ]
  }'
```

**Expected Response:**
- Status: 201
- Existing client information updated
- Order created successfully

#### Test 5.3: Create Order - Missing Required Fields
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "orderItems": [
      {
        "productId": 1,
        "quantity": 1
      }
    ]
  }'
```

**Expected Response:**
- Status: 422
- Validation errors for missing fields (name, phone, address)

#### Test 5.4: Create Order - Invalid Product
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "+201234567890",
    "address": "شارع النيل",
    "orderItems": [
      {
        "productId": 99999,
        "quantity": 1
      }
    ]
  }'
```

**Expected Response:**
- Status: 422
- Validation error for non-existent product

#### Test 5.5: Create Order - Insufficient Stock
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "+201234567890",
    "address": "شارع النيل",
    "orderItems": [
      {
        "productId": 1,
        "quantity": 1000
      }
    ]
  }'
```

**Expected Response:**
- Status: 400
- Error message about insufficient stock

#### Test 5.6: Create Order - Inactive Product
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "+201234567890",
    "address": "شارع النيل",
    "orderItems": [
      {
        "productId": 5,
        "quantity": 1
      }
    ]
  }'
```

**Expected Response:**
- Status: 400
- Error message about product not being available

---

### 6. Cart Validation Tests

#### Test 6.1: Validate Cart (Valid Items)
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

**Expected Response:**
- Status: 200
- `isValid: true`
- Calculated total amount
- Individual item validation results
- Product details for each item

#### Test 6.2: Validate Cart (Mixed Valid/Invalid)
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/validate-cart" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {
        "productId": 1,
        "quantity": 2
      },
      {
        "productId": 999,
        "quantity": 1
      },
      {
        "productId": 3,
        "quantity": 100
      }
    ]
  }'
```

**Expected Response:**
- Status: 200
- `isValid: false`
- Individual validation results with errors
- Available quantities for out-of-stock items

#### Test 6.3: Validate Empty Cart
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/validate-cart" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": []
  }'
```

**Expected Response:**
- Status: 422
- Validation error for empty items array

#### Test 6.4: Validate Cart - Invalid Request Format
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/validate-cart" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {
        "productId": "invalid",
        "quantity": -1
      }
    ]
  }'
```

**Expected Response:**
- Status: 422
- Validation errors for invalid data types and values

---

## Postman Collection

### Environment Variables
```json
{
  "base_url": "http://localhost:8000/api/v1/website",
  "sample_product_slug": "smartphone",
  "sample_category_id": "1"
}
```

### Pre-request Script (Global)
```javascript
pm.request.headers.add({
    key: 'Accept',
    value: 'application/json'
});

pm.request.headers.add({
    key: 'Accept-Language',
    value: 'ar'
});

// For POST requests
if (pm.request.method === 'POST') {
    pm.request.headers.add({
        key: 'Content-Type',
        value: 'application/json'
    });
}
```

### Test Script Examples

#### For Homepage Endpoint
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Response has categories and products", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('categories');
    pm.expect(jsonData.data).to.have.property('products');
    pm.expect(jsonData.data.categories).to.be.an('array');
    pm.expect(jsonData.data.products).to.be.an('array');
});

pm.test("Products array has max 9 items", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data.products.length).to.be.at.most(9);
});
```

#### For Products List Endpoint
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has products array", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('products');
    pm.expect(jsonData.data.products).to.be.an('array');
});

pm.test("Each product has required fields", function () {
    const jsonData = pm.response.json();
    if (jsonData.data.products.length > 0) {
        const product = jsonData.data.products[0];
        pm.expect(product).to.have.property('productId');
        pm.expect(product).to.have.property('name');
        pm.expect(product).to.have.property('slug');
        pm.expect(product).to.have.property('price');
        pm.expect(product).to.have.property('stockQuantity');
    }
});
```

#### For Order Creation Endpoint
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Response indicates success", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Order number is returned", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('orderNumber');
    pm.expect(jsonData.data.orderNumber).to.be.a('string');
    pm.expect(jsonData.data.orderNumber).to.match(/^ORD-\d{8}-\d{4}$/);
});

// Store order number for future tests
pm.test("Store order number", function () {
    const jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.orderNumber) {
        pm.environment.set("last_order_number", jsonData.data.orderNumber);
    }
});
```

#### For Cart Validation Endpoint
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has validation results", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('isValid');
    pm.expect(jsonData.data).to.have.property('totalAmount');
    pm.expect(jsonData.data).to.have.property('items');
    pm.expect(jsonData.data.items).to.be.an('array');
});

pm.test("Each item has validation result", function () {
    const jsonData = pm.response.json();
    jsonData.data.items.forEach(item => {
        pm.expect(item).to.have.property('productId');
        pm.expect(item).to.have.property('isValid');
        pm.expect(item).to.have.property('errors');
        pm.expect(item.errors).to.be.an('array');
    });
});
```

---

## Automated Testing with PHPUnit

### Test Class Structure
```php
<?php

namespace Tests\Feature\Api\V1\Website;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Client;
use App\Models\Inventory;
use App\Enums\ProductStatusEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebsiteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    public function test_can_get_homepage_data()
    {
        $response = $this->getJson('/api/v1/website/home');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'categories' => [
                            '*' => [
                                'categoryId',
                                'name',
                                'slug',
                                'description'
                            ]
                        ],
                        'products' => [
                            '*' => [
                                'productId',
                                'name',
                                'slug',
                                'price',
                                'brand',
                                'category',
                                'stockQuantity'
                            ]
                        ]
                    ]
                ]);
    }

    public function test_can_get_products_list()
    {
        $response = $this->getJson('/api/v1/website/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'products' => [
                            '*' => [
                                'productId',
                                'name',
                                'price',
                                'stockQuantity'
                            ]
                        ]
                    ]
                ]);
    }

    public function test_can_filter_products_by_category()
    {
        $category = Category::factory()->create(['status' => StatusEnum::ACTIVE]);
        Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'status' => ProductStatusEnum::ACTIVE
        ]);

        $response = $this->getJson("/api/v1/website/products?filter[category]={$category->id}");

        $response->assertStatus(200);
        $products = $response->json('data.products');
        
        foreach ($products as $product) {
            $this->assertEquals($category->id, $product['category']['id']);
        }
    }

    public function test_can_create_order()
    {
        $product = Product::factory()->create([
            'status' => ProductStatusEnum::ACTIVE,
            'price' => 100.00
        ]);
        
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $orderData = [
            'name' => 'أحمد محمد',
            'email' => 'ahmed@example.com',
            'phone' => '+201234567890',
            'address' => 'شارع النيل',
            'orderItems' => [
                [
                    'productId' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/website/orders', $orderData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'orderNumber'
                    ]
                ]);
    }

    public function test_can_validate_cart()
    {
        $product = Product::factory()->create([
            'status' => ProductStatusEnum::ACTIVE,
            'price' => 100.00
        ]);
        
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $cartData = [
            'items' => [
                [
                    'productId' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/website/orders/validate-cart', $cartData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'isValid',
                        'totalAmount',
                        'items' => [
                            '*' => [
                                'productId',
                                'isValid',
                                'errors',
                                'product'
                            ]
                        ]
                    ]
                ]);
    }

    private function createTestData()
    {
        // Create categories
        Category::factory()->count(3)->create(['status' => StatusEnum::ACTIVE]);
        
        // Create brands
        Brand::factory()->count(2)->create(['status' => StatusEnum::ACTIVE]);
        
        // Create products with inventory
        Product::factory()->count(10)->create(['status' => ProductStatusEnum::ACTIVE])
            ->each(function ($product) {
                Inventory::factory()->create([
                    'product_id' => $product->id,
                    'quantity' => rand(5, 50)
                ]);
            });
    }
}
```

---

## Performance Testing

### Load Testing with Apache Bench
```bash
# Test homepage endpoint
ab -n 100 -c 10 http://localhost:8000/api/v1/website/home

# Test products list endpoint
ab -n 200 -c 20 http://localhost:8000/api/v1/website/products

# Test product details endpoint
ab -n 100 -c 10 http://localhost:8000/api/v1/website/products/smartphone
```

### Expected Performance Metrics
- **Homepage**: < 200ms average response time
- **Products List**: < 300ms average response time
- **Product Details**: < 150ms average response time
- **Order Creation**: < 500ms average response time
- **Cart Validation**: < 200ms average response time

---

## Security Testing

### Input Validation Tests
```bash
# Test XSS prevention
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<script>alert(\"XSS\")</script>",
    "email": "test@example.com",
    "phone": "+201234567890",
    "address": "test address",
    "orderItems": [{"productId": 1, "quantity": 1}]
  }'

# Test SQL injection attempts
curl -X GET "http://localhost:8000/api/v1/website/products?filter[search]='; DROP TABLE products; --" \
  -H "Accept: application/json"

# Test large payload
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "'$(python -c "print('A' * 10000)")'",
    "email": "test@example.com",
    "phone": "+201234567890",
    "address": "test address",
    "orderItems": [{"productId": 1, "quantity": 1}]
  }'
```

### Rate Limiting Tests
```bash
# Send rapid requests to test rate limiting
for i in {1..100}; do
  curl -X GET "http://localhost:8000/api/v1/website/products" \
    -H "Accept: application/json" &
done
```

---

## Test Data Setup

### Sample Data Creation Script
```sql
-- Create test categories
INSERT INTO categories (name, slug, description, status) VALUES
('إلكترونيات', 'electronics', 'جميع المنتجات الإلكترونية', 1),
('ملابس', 'clothing', 'ملابس رجالية ونسائية', 1),
('كتب', 'books', 'كتب متنوعة', 1);

-- Create test brands
INSERT INTO brands (name, slug, status) VALUES
('سامسونج', 'samsung', 1),
('آبل', 'apple', 1),
('نايك', 'nike', 1);

-- Create test products
INSERT INTO products (name, slug, description, price, cost, category_id, brand_id, status, has_stock, min_stock) VALUES
('هاتف ذكي', 'smartphone', 'هاتف ذكي بمواصفات عالية', 1500.00, 1000.00, 1, 1, 1, 1, 5),
('لابتوب', 'laptop', 'لابتوب للألعاب', 3000.00, 2000.00, 1, 1, 1, 1, 3),
('قميص', 'shirt', 'قميص قطني', 150.00, 100.00, 2, 3, 1, 1, 10);

-- Create inventory records
INSERT INTO inventory (product_id, quantity) VALUES
(1, 25),
(2, 10),
(3, 50);

-- Create test clients
INSERT INTO clients (name, email, phone, address, city) VALUES
('أحمد محمد', 'ahmed@example.com', '+201234567890', 'شارع النيل', 'القاهرة'),
('فاطمة علي', 'fatima@example.com', '+201234567891', 'شارع التحرير', 'الإسكندرية');
```

---

## Troubleshooting

### Common Issues
1. **Empty Response Data**: Check if test data exists and products are active
2. **404 Errors**: Verify product slugs and routes are correct
3. **Validation Errors**: Check request body format and required fields
4. **Stock Issues**: Ensure inventory records exist for products

### Debug Commands
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check routes
php artisan route:list --path=website

# Clear cache
php artisan cache:clear
php artisan config:clear
```

---

## Test Checklist

### Functional Tests
- [ ] Homepage data retrieval
- [ ] Products list with all filters
- [ ] Product details by slug
- [ ] Related products
- [ ] Order creation (valid and invalid cases)
- [ ] Cart validation (valid and invalid cases)

### Validation Tests
- [ ] Required field validation
- [ ] Data type validation
- [ ] Business rule validation (stock, product status)
- [ ] Input sanitization

### Performance Tests
- [ ] Response time under normal load
- [ ] Response time under heavy load
- [ ] Memory usage
- [ ] Database query optimization

### Security Tests
- [ ] XSS prevention
- [ ] SQL injection prevention
- [ ] Input validation
- [ ] Rate limiting

### Edge Case Tests
- [ ] Empty database scenarios
- [ ] Large dataset handling
- [ ] Invalid product slugs
- [ ] Out of stock scenarios
- [ ] Network timeout handling
