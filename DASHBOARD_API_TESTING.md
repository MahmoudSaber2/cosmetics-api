# Dashboard API Testing Guide

## Overview
This document provides comprehensive testing scenarios for the Dashboard API endpoint using various tools like Postman, cURL, and automated testing frameworks.

## Prerequisites
- Valid authentication token
- Test database with sample data (products, orders, clients)
- API base URL: `http://localhost:8000/api/v1/admin/dashboard`

## Authentication Setup
All requests require Bearer token authentication:
```
Authorization: Bearer {your_token_here}
```

---

## Test Scenarios

### 1. Basic Dashboard Data Tests

#### Test 1.1: Get Dashboard Data (Success)
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Contains all required sections: statistics, latestOrders, orderStatusChart, lowStockProducts
- Statistics object has all required fields
- Latest orders array (max 5 items)
- Order status chart with percentages
- Low stock products array

#### Test 1.2: Dashboard Data Structure Validation
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

**Validation Points:**
- `statistics.totalProducts` is integer
- `statistics.totalRevenue` is float
- `latestOrders` is array with max 5 elements
- `orderStatusChart` percentages sum to ~100% (allowing for rounding)
- `lowStockProducts` contains only products with stock issues

#### Test 1.3: Empty Database Scenario
```bash
# Test with empty database (no orders/products)
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Statistics with zero values
- Empty arrays for orders and low stock products
- Order status chart with 0% for all statuses

---

### 2. Authentication Tests

#### Test 2.1: Unauthorized Access
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 401
- Error message about authentication

#### Test 2.2: Invalid Token
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer invalid_token_here" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 401
- Authentication error

#### Test 2.3: Expired Token
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {expired_token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 401
- Token expired error

---

### 3. Localization Tests

#### Test 3.1: Arabic Language (Default)
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status text in Arabic (معلق, موافق عليه, مرفوض, مكتمل)
- Stock status in Arabic (منخفض, نفد المخزون)

#### Test 3.2: English Language
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Accept-Language: en"
```

**Expected Response:**
- Status text in English (if implemented)
- Consistent language throughout response

#### Test 3.3: Unsupported Language (Fallback)
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Accept-Language: fr"
```

**Expected Response:**
- Falls back to default language (Arabic)

---

### 4. Data Accuracy Tests

#### Test 4.1: Statistics Accuracy
**Setup:**
1. Create known number of products
2. Create known number of orders with specific statuses
3. Create orders for today and previous days

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Validation:**
- Verify `totalProducts` matches database count
- Verify `totalOrders` matches database count
- Verify `totalOrdersToday` matches today's orders
- Verify `pendingOrders` matches pending status count
- Verify revenue calculations are correct

#### Test 4.2: Latest Orders Accuracy
**Setup:**
1. Create more than 5 orders with different timestamps
2. Ensure orders have associated clients

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Validation:**
- Returns exactly 5 orders (or less if fewer exist)
- Orders are sorted by creation date (newest first)
- Each order has all required fields
- Client information is properly loaded

#### Test 4.3: Order Status Chart Accuracy
**Setup:**
1. Create orders with known status distribution
2. Calculate expected percentages manually

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Validation:**
- Percentages match expected calculations
- All percentages are between 0 and 100
- Sum of percentages is approximately 100% (allowing for rounding)

#### Test 4.4: Low Stock Products Accuracy
**Setup:**
1. Create products with `has_stock = true`
2. Set inventory quantities at or below `min_stock`
3. Create some products with `has_stock = false` (should be excluded)

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Validation:**
- Only includes products with `has_stock = true`
- Only includes products where `current_stock <= min_stock`
- Includes products with 0 stock
- Excludes products with sufficient stock

---

### 5. Performance Tests

#### Test 5.1: Response Time
```bash
# Measure response time
time curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected:**
- Response time < 1 second for typical data volumes
- Consistent performance across multiple requests

#### Test 5.2: Large Dataset Performance
**Setup:**
1. Create large number of orders (1000+)
2. Create large number of products (500+)

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Validation:**
- Response time remains acceptable
- Memory usage is reasonable
- No timeout errors

#### Test 5.3: Concurrent Requests
```bash
# Run multiple concurrent requests
for i in {1..10}; do
  curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
    -H "Authorization: Bearer {token}" \
    -H "Accept: application/json" &
done
wait
```

**Expected:**
- All requests complete successfully
- No database connection issues
- Consistent response times

---

### 6. Edge Cases Tests

#### Test 6.1: No Orders Scenario
**Setup:**
1. Empty orders table
2. Keep products table populated

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected:**
- Statistics show 0 for order-related fields
- Empty latest orders array
- Order status chart shows 0% for all statuses
- Products count is accurate

#### Test 6.2: No Products Scenario
**Setup:**
1. Empty products table
2. Keep orders table populated

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected:**
- `totalProducts` is 0
- Empty low stock products array
- Order statistics remain accurate

#### Test 6.3: All Products In Stock
**Setup:**
1. Set all products with stock above minimum levels

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected:**
- Empty low stock products array
- Other data remains accurate

#### Test 6.4: Orders Without Clients
**Setup:**
1. Create orders with missing client relationships (if possible)

```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected:**
- Handles missing client data gracefully
- No fatal errors
- Appropriate default values for missing client info

---

## Postman Collection

### Environment Variables
```json
{
  "base_url": "http://localhost:8000/api/v1/admin",
  "token": "your_bearer_token_here"
}
```

### Pre-request Script (Global)
```javascript
pm.request.headers.add({
    key: 'Authorization',
    value: 'Bearer ' + pm.environment.get('token')
});

pm.request.headers.add({
    key: 'Accept',
    value: 'application/json'
});

pm.request.headers.add({
    key: 'Accept-Language',
    value: 'ar'
});
```

### Test Script for Dashboard Endpoint
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Response has all required sections", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('statistics');
    pm.expect(jsonData.data).to.have.property('latestOrders');
    pm.expect(jsonData.data).to.have.property('orderStatusChart');
    pm.expect(jsonData.data).to.have.property('lowStockProducts');
});

pm.test("Statistics has all required fields", function () {
    const stats = pm.response.json().data.statistics;
    pm.expect(stats).to.have.property('totalProducts');
    pm.expect(stats).to.have.property('totalOrders');
    pm.expect(stats).to.have.property('totalOrdersToday');
    pm.expect(stats).to.have.property('pendingOrders');
    pm.expect(stats).to.have.property('totalRevenue');
    pm.expect(stats).to.have.property('totalRevenueToday');
});

pm.test("Latest orders is array with max 5 items", function () {
    const orders = pm.response.json().data.latestOrders;
    pm.expect(orders).to.be.an('array');
    pm.expect(orders.length).to.be.at.most(5);
});

pm.test("Order status chart percentages are valid", function () {
    const chart = pm.response.json().data.orderStatusChart;
    pm.expect(chart.pending).to.be.at.least(0).and.at.most(100);
    pm.expect(chart.approved).to.be.at.least(0).and.at.most(100);
    pm.expect(chart.rejected).to.be.at.least(0).and.at.most(100);
    pm.expect(chart.completed).to.be.at.least(0).and.at.most(100);
});

pm.test("Low stock products is array", function () {
    const products = pm.response.json().data.lowStockProducts;
    pm.expect(products).to.be.an('array');
});

pm.test("Response time is acceptable", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});
```

---

## Automated Testing with PHPUnit

### Test Class Structure
```php
<?php

namespace Tests\Feature\Api\V1\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\Inventory;
use App\Enums\OrderStatusEnum;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_get_dashboard_data()
    {
        // Create test data
        Product::factory()->count(10)->create();
        Order::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'statistics' => [
                            'totalProducts',
                            'totalOrders',
                            'totalOrdersToday',
                            'pendingOrders',
                            'totalRevenue',
                            'totalRevenueToday'
                        ],
                        'latestOrders',
                        'orderStatusChart' => [
                            'pending',
                            'approved',
                            'rejected',
                            'completed'
                        ],
                        'lowStockProducts'
                    ]
                ]);
    }

    public function test_statistics_accuracy()
    {
        // Create known test data
        Product::factory()->count(15)->create();
        Order::factory()->count(10)->create(['status' => OrderStatusEnum::PENDING]);
        Order::factory()->count(5)->create(['status' => OrderStatusEnum::APPROVED]);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $data = $response->json('data.statistics');
        
        $this->assertEquals(15, $data['totalProducts']);
        $this->assertEquals(15, $data['totalOrders']);
        $this->assertEquals(10, $data['pendingOrders']);
    }

    public function test_latest_orders_limit()
    {
        // Create more than 5 orders
        Order::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/admin/dashboard');

        $orders = $response->json('data.latestOrders');
        $this->assertCount(5, $orders);
    }

    public function test_order_status_chart_percentages()
    {
        // Create orders with known distribution
        Order::factory()->count(40)->create(['status' => OrderStatusEnum::PENDING]);
        Order::factory()->count(30)->create(['status' => OrderStatusEnum::APPROVED]);
        Order::factory()->count(20)->create(['status' => OrderStatusEnum::REJECTED]);
        Order::factory()->count(10)->create(['status' => OrderStatusEnum::COMPLETED]);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $chart = $response->json('data.orderStatusChart');
        
        $this->assertEquals(40.0, $chart['pending']);
        $this->assertEquals(30.0, $chart['approved']);
        $this->assertEquals(20.0, $chart['rejected']);
        $this->assertEquals(10.0, $chart['completed']);
    }

    public function test_low_stock_products_filtering()
    {
        // Create products with different stock levels
        $lowStockProduct = Product::factory()->create([
            'has_stock' => true,
            'min_stock' => 10
        ]);
        Inventory::factory()->create([
            'product_id' => $lowStockProduct->id,
            'quantity' => 5
        ]);

        $goodStockProduct = Product::factory()->create([
            'has_stock' => true,
            'min_stock' => 10
        ]);
        Inventory::factory()->create([
            'product_id' => $goodStockProduct->id,
            'quantity' => 20
        ]);

        $noStockProduct = Product::factory()->create([
            'has_stock' => false
        ]);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $lowStockProducts = $response->json('data.lowStockProducts');
        
        $this->assertCount(1, $lowStockProducts);
        $this->assertEquals($lowStockProduct->id, $lowStockProducts[0]['productId']);
    }

    public function test_unauthorized_access()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(401);
    }
}
```

---

## Load Testing with Apache Bench

### Basic Load Test
```bash
# Test with 100 requests, 10 concurrent
ab -n 100 -c 10 -H "Authorization: Bearer {token}" \
   http://localhost:8000/api/v1/admin/dashboard
```

### Extended Load Test
```bash
# Test with 1000 requests, 50 concurrent
ab -n 1000 -c 50 -H "Authorization: Bearer {token}" \
   -H "Accept: application/json" \
   http://localhost:8000/api/v1/admin/dashboard
```

**Expected Results:**
- No failed requests
- Average response time < 500ms
- 95th percentile < 1000ms

---

## Security Testing

### Authentication Bypass Tests
```bash
# Test without Authorization header
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Accept: application/json"

# Test with malformed token
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer malformed.token.here" \
  -H "Accept: application/json"

# Test with SQL injection in headers (should be handled by framework)
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept-Language: ar'; DROP TABLE orders; --" \
  -H "Accept: application/json"
```

### Rate Limiting Tests
```bash
# Send rapid requests to test rate limiting
for i in {1..100}; do
  curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
    -H "Authorization: Bearer {token}" \
    -H "Accept: application/json" &
done
```

---

## Test Data Setup

### Sample Data Creation Script
```sql
-- Create test products
INSERT INTO products (name, has_stock, min_stock, price, cost) VALUES
('منتج تجريبي 1', true, 10, 100.00, 70.00),
('منتج تجريبي 2', true, 15, 200.00, 150.00),
('منتج تجريبي 3', true, 8, 50.00, 30.00);

-- Create inventory records
INSERT INTO inventory (product_id, quantity) VALUES
(1, 5),   -- Low stock
(2, 0),   -- Out of stock
(3, 20);  -- Good stock

-- Create test clients
INSERT INTO clients (name, email, phone) VALUES
('أحمد محمد', 'ahmed@example.com', '+201234567890'),
('فاطمة علي', 'fatima@example.com', '+201234567891'),
('محمد حسن', 'mohamed@example.com', '+201234567892');

-- Create test orders
INSERT INTO orders (client_id, total_amount, total_after_discount, status, created_at) VALUES
(1, 250.00, 225.00, 0, NOW()),
(2, 180.00, 180.00, 1, NOW() - INTERVAL 1 HOUR),
(3, 320.00, 300.00, 3, NOW() - INTERVAL 2 HOURS),
(1, 95.00, 95.00, 2, NOW() - INTERVAL 3 HOURS),
(2, 420.00, 400.00, 0, NOW() - INTERVAL 4 HOURS);
```

---

## Troubleshooting

### Common Issues
1. **Empty Response Data**: Check if test data exists in database
2. **Authentication Errors**: Verify token validity and format
3. **Performance Issues**: Check database indexes and query optimization
4. **Incorrect Calculations**: Verify business logic and database relationships

### Debug Commands
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Enable query logging in Laravel
DB::enableQueryLog();
// ... run dashboard endpoint
dd(DB::getQueryLog());

# Check database connections
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## Test Checklist

### Functional Tests
- [ ] Dashboard data retrieval
- [ ] Statistics accuracy
- [ ] Latest orders (correct count and order)
- [ ] Order status chart percentages
- [ ] Low stock products filtering
- [ ] Localization support

### Security Tests
- [ ] Authentication required
- [ ] Invalid token handling
- [ ] SQL injection prevention
- [ ] XSS prevention

### Performance Tests
- [ ] Response time under normal load
- [ ] Response time under heavy load
- [ ] Memory usage
- [ ] Database query optimization

### Edge Case Tests
- [ ] Empty database scenarios
- [ ] Large dataset handling
- [ ] Missing relationships
- [ ] Invalid data handling
