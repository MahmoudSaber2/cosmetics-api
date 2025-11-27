# Order API Testing Guide

## Overview
This document provides comprehensive testing scenarios for the Order Management API endpoints using various tools like Postman, cURL, and automated testing frameworks.

## Prerequisites
- Valid authentication token
- Test database with sample data
- API base URL: `http://localhost:8000/api/v1/admin/orders`

## Authentication Setup
All requests require Bearer token authentication:
```
Authorization: Bearer {your_token_here}
```

---

## Test Scenarios

### 1. Get Orders List Tests

#### Test 1.1: Get All Orders (Basic)
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Contains orders array with pagination
- Each order has required fields: orderId, number, totalAmount, status, client, createdAt

#### Test 1.2: Filter by Status
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders?filter[status]=0" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Only pending orders (status = 0)

#### Test 1.3: Filter by Client
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders?filter[client]=1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Test 1.4: Date Range Filter
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders?filter[date]=2024-01-01,2024-12-31" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Test 1.5: Search Filter
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders?filter[search]=أحمد" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Test 1.6: Sorting
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders?sort=-total_amount" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Test 1.7: Pagination
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders?perPage=5&page=2" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

### 2. Create Order Tests

#### Test 2.1: Create Order for Existing Client
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "clientId": 1,
    "note": "طلب تجريبي",
    "status": 0,
    "discount": 10.00,
    "discountType": 2,
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

#### Test 2.2: Create Order with New Client
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "clientId": null,
    "name": "عميل جديد",
    "email": "newclient@example.com",
    "phone": "+201234567890",
    "address": "العنوان الجديد",
    "city": "القاهرة",
    "note": "طلب لعميل جديد",
    "status": 0,
    "orderItems": [
      {
        "productId": 1,
        "quantity": 1
      }
    ]
  }'
```

#### Test 2.3: Create Order - Validation Errors
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "clientId": null,
    "status": 0
  }'
```

**Expected Response:**
- Status: 422
- Validation errors for missing fields

#### Test 2.4: Create Order - Insufficient Stock
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "clientId": 1,
    "status": 0,
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

---

### 3. Get Order Details Tests

#### Test 3.1: Get Existing Order
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Complete order details with client and orderItems

#### Test 3.2: Get Non-existent Order
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders/99999" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 404
- Order not found error

---

### 4. Update Order Tests

#### Test 4.1: Update Order with Mixed Actions
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/orders/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "clientId": 1,
    "note": "طلب محدث",
    "status": 1,
    "discount": 15.00,
    "discountType": 1,
    "orderItems": [
      {
        "orderItemId": 1,
        "productId": 1,
        "quantity": 3,
        "actionStatus": 2
      },
      {
        "productId": 2,
        "quantity": 2,
        "actionStatus": 1
      },
      {
        "orderItemId": 2,
        "productId": 3,
        "quantity": 1,
        "actionStatus": 3
      }
    ]
  }'
```

**Expected Response:**
- Status: 200
- Success message

#### Test 4.2: Update Order - Add New Items Only
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/orders/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "clientId": 1,
    "status": 0,
    "orderItems": [
      {
        "productId": 3,
        "quantity": 1,
        "actionStatus": 1
      }
    ]
  }'
```

#### Test 4.3: Update Order - Delete Items Only
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/orders/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "clientId": 1,
    "status": 0,
    "orderItems": [
      {
        "orderItemId": 1,
        "productId": 1,
        "quantity": 1,
        "actionStatus": 3
      }
    ]
  }'
```

---

### 5. Delete Order Tests

#### Test 5.1: Delete Pending Order
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/orders/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Success message

#### Test 5.2: Delete Approved Order (Should Fail)
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/orders/2" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 400
- Error message about order status

---

### 6. Order Status Management Tests

#### Test 6.1: Approve Pending Order
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders/1/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Empty data array with localized success message

#### Test 6.2: Approve Non-Pending Order (Should Fail)
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders/2/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 400
- Error message about order status

#### Test 6.3: Reject Pending Order
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders/1/reject" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "reason": "المنتج غير متوفر حالياً"
  }'
```

#### Test 6.4: Reject Order Without Reason
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders/1/reject" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

#### Test 6.5: Complete Approved Order
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders/1/complete" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Test 6.6: Complete Non-Approved Order (Should Fail)
```bash
curl -X POST "http://localhost:8000/api/v1/admin/orders/1/complete" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 400
- Error message about order status

---

### 7. Statistics Tests

#### Test 7.1: Get Order Statistics
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders/statistics" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Statistics object with all required fields

#### Test 7.2: Get Status Counts
```bash
curl -X GET "http://localhost:8000/api/v1/admin/orders/status-counts" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Status counts object

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

### Test Script Examples

#### For List Endpoints
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Response has data field", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});

pm.test("Pagination exists", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('pagination');
});
```

#### For Create/Update Endpoints
```javascript
pm.test("Status code is 201 or 200", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 201]);
});

pm.test("Response indicates success", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});
```

#### For Error Cases
```javascript
pm.test("Status code is 400", function () {
    pm.response.to.have.status(400);
});

pm.test("Response indicates failure", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.false;
});

pm.test("Error message exists", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('message');
    pm.expect(jsonData.message).to.not.be.empty;
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
use App\Models\Client;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_get_orders_list()
    {
        Order::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/admin/orders');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'orders' => [
                            '*' => [
                                'orderId',
                                'number',
                                'totalAmount',
                                'status',
                                'client',
                                'createdAt'
                            ]
                        ],
                        'pagination'
                    ]
                ]);
    }

    public function test_can_create_order()
    {
        $client = Client::factory()->create();
        $product = Product::factory()->create();

        $orderData = [
            'clientId' => $client->id,
            'status' => 0,
            'orderItems' => [
                [
                    'productId' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/admin/orders', $orderData);

        $response->assertStatus(201)
                ->assertJson(['success' => true]);
    }

    // Add more test methods...
}
```

---

## Performance Testing

### Load Testing with Apache Bench
```bash
# Test orders list endpoint
ab -n 100 -c 10 -H "Authorization: Bearer {token}" \
   http://localhost:8000/api/v1/admin/orders

# Test order creation
ab -n 50 -c 5 -p order_data.json -T application/json \
   -H "Authorization: Bearer {token}" \
   http://localhost:8000/api/v1/admin/orders
```

### order_data.json
```json
{
    "clientId": 1,
    "status": 0,
    "orderItems": [
        {
            "productId": 1,
            "quantity": 1
        }
    ]
}
```

---

## Security Testing

### Authentication Tests
1. Test without token (should return 401)
2. Test with invalid token (should return 401)
3. Test with expired token (should return 401)

### Authorization Tests
1. Test with user without proper permissions
2. Test accessing other user's orders (if applicable)

### Input Validation Tests
1. SQL injection attempts
2. XSS attempts
3. Invalid data types
4. Boundary value testing

---

## Test Data Setup

### Sample Client Data
```sql
INSERT INTO clients (name, email, phone, address, city) VALUES
('أحمد محمد', 'ahmed@example.com', '+201234567890', 'شارع النيل', 'القاهرة'),
('فاطمة علي', 'fatima@example.com', '+201234567891', 'شارع التحرير', 'الإسكندرية'),
('محمد حسن', 'mohamed@example.com', '+201234567892', 'شارع الجامعة', 'الجيزة');
```

### Sample Product Data
```sql
INSERT INTO products (name, price, cost) VALUES
('منتج تجريبي 1', 100.00, 70.00),
('منتج تجريبي 2', 200.00, 150.00),
('منتج تجريبي 3', 50.00, 30.00);
```

---

## Troubleshooting

### Common Issues
1. **401 Unauthorized**: Check token validity and format
2. **422 Validation Error**: Review request body structure
3. **400 Bad Request**: Check business logic constraints
4. **500 Internal Error**: Check server logs and database connection

### Debug Tips
1. Enable Laravel debug mode for detailed error messages
2. Check Laravel logs in `storage/logs/laravel.log`
3. Use database query logging to debug SQL issues
4. Verify middleware configuration

---

## Test Checklist

### Functional Tests
- [ ] Get orders list with all filters
- [ ] Create order for existing client
- [ ] Create order for new client
- [ ] Get order details
- [ ] Update order with all action types
- [ ] Delete order (valid cases)
- [ ] Approve order
- [ ] Reject order
- [ ] Complete order
- [ ] Get statistics
- [ ] Get status counts

### Error Handling Tests
- [ ] Invalid authentication
- [ ] Validation errors
- [ ] Business logic violations
- [ ] Not found errors
- [ ] Server errors

### Performance Tests
- [ ] Response time under load
- [ ] Memory usage
- [ ] Database query optimization
- [ ] Concurrent request handling

### Security Tests
- [ ] Authentication bypass attempts
- [ ] Input validation
- [ ] SQL injection prevention
- [ ] XSS prevention
