# Dashboard API Documentation

## Overview
This document provides comprehensive documentation for the Dashboard API endpoint. The API provides a complete overview of the system including statistics, latest orders, order status charts, and low stock alerts.

## Base URL
```
/api/v1/admin/dashboard
```

## Authentication
The endpoint requires authentication using Sanctum tokens:
```
Authorization: Bearer {token}
```

## Headers
- `Accept: application/json`
- `Content-Type: application/json`
- `Accept-Language: ar|en` (optional, for localization)

---

## Endpoint

### Get Dashboard Data
**GET** `/api/v1/admin/dashboard`

Retrieve comprehensive dashboard data including statistics, latest orders, order status chart, and low stock products.

#### Query Parameters
None required.

#### Example Request
```bash
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "statistics": {
            "totalProducts": 150,
            "totalOrders": 320,
            "totalOrdersToday": 12,
            "pendingOrders": 25,
            "totalRevenue": 45000.00,
            "totalRevenueToday": 2500.00
        },
        "latestOrders": [
            {
                "orderNumber": "ORD-11202025-1234",
                "clientName": "أحمد محمد",
                "clientPhone": "+201234567890",
                "totalPrice": 250.00,
                "status": 0,
                "statusText": "معلق",
                "createdAt": "01/12/24 02:30 م"
            },
            {
                "orderNumber": "ORD-11202025-1235",
                "clientName": "فاطمة علي",
                "clientPhone": "+201234567891",
                "totalPrice": 180.00,
                "status": 1,
                "statusText": "موافق عليه",
                "createdAt": "01/12/24 01:15 م"
            },
            {
                "orderNumber": "ORD-11202025-1236",
                "clientName": "محمد حسن",
                "clientPhone": "+201234567892",
                "totalPrice": 320.00,
                "status": 3,
                "statusText": "مكتمل",
                "createdAt": "01/12/24 12:45 م"
            },
            {
                "orderNumber": "ORD-11202025-1237",
                "clientName": "سارة أحمد",
                "clientPhone": "+201234567893",
                "totalPrice": 95.00,
                "status": 2,
                "statusText": "مرفوض",
                "createdAt": "01/12/24 11:30 ص"
            },
            {
                "orderNumber": "ORD-11202025-1238",
                "clientName": "خالد محمود",
                "clientPhone": "+201234567894",
                "totalPrice": 420.00,
                "status": 0,
                "statusText": "معلق",
                "createdAt": "01/12/24 10:20 ص"
            }
        ],
        "orderStatusChart": {
            "pending": 25.5,
            "approved": 40.2,
            "rejected": 10.1,
            "completed": 24.2
        },
        "lowStockProducts": [
            {
                "productId": 1,
                "productName": "منتج تجريبي 1",
                "currentStock": 5,
                "minStock": 10,
                "stockStatus": "منخفض",
                "brandName": "براند تجريبي",
                "categoryName": "فئة تجريبية"
            },
            {
                "productId": 2,
                "productName": "منتج تجريبي 2",
                "currentStock": 0,
                "minStock": 15,
                "stockStatus": "نفد المخزون",
                "brandName": "براند آخر",
                "categoryName": "فئة أخرى"
            },
            {
                "productId": 3,
                "productName": "منتج تجريبي 3",
                "currentStock": 3,
                "minStock": 8,
                "stockStatus": "منخفض",
                "brandName": "براند ثالث",
                "categoryName": "فئة ثالثة"
            }
        ]
    }
}
```

---

## Response Structure

### Statistics Object
| Field | Type | Description |
|-------|------|-------------|
| `totalProducts` | integer | Total number of products in the system |
| `totalOrders` | integer | Total number of orders |
| `totalOrdersToday` | integer | Number of orders created today |
| `pendingOrders` | integer | Number of pending orders |
| `totalRevenue` | float | Total revenue from approved and completed orders |
| `totalRevenueToday` | float | Revenue from orders created today |

### Latest Orders Array
Each order object contains:
| Field | Type | Description |
|-------|------|-------------|
| `orderNumber` | string | Unique order number |
| `clientName` | string | Client's full name |
| `clientPhone` | string | Client's phone number |
| `totalPrice` | float | Order total price after discount |
| `status` | integer | Order status (0=pending, 1=approved, 2=rejected, 3=completed) |
| `statusText` | string | Localized status text in Arabic |
| `createdAt` | string | Formatted creation date |

### Order Status Chart Object
Percentages of orders by status:
| Field | Type | Description |
|-------|------|-------------|
| `pending` | float | Percentage of pending orders |
| `approved` | float | Percentage of approved orders |
| `rejected` | float | Percentage of rejected orders |
| `completed` | float | Percentage of completed orders |

### Low Stock Products Array
Each product object contains:
| Field | Type | Description |
|-------|------|-------------|
| `productId` | integer | Product ID |
| `productName` | string | Product name |
| `currentStock` | integer | Current stock quantity |
| `minStock` | integer | Minimum stock threshold |
| `stockStatus` | string | Stock status in Arabic (منخفض/نفد المخزون) |
| `brandName` | string | Brand name |
| `categoryName` | string | Category name |

---

## Business Logic

### Statistics Calculation
- **Total Revenue**: Sum of `total_after_discount` for approved and completed orders
- **Today's Revenue**: Same calculation but filtered by today's date
- **Pending Orders**: Count of orders with status = 0

### Latest Orders
- Returns the 5 most recent orders ordered by creation date
- Includes client information and formatted dates
- Status text is localized in Arabic

### Order Status Chart
- Calculates percentages based on total order count
- Returns 0% for all statuses if no orders exist
- Percentages are rounded to 1 decimal place

### Low Stock Products
- Includes only products with `has_stock = true`
- Filters products where `current_stock <= min_stock`
- Includes products with 0 stock (out of stock)
- Ordered by creation date (newest first)

---

## Error Responses

### Unauthorized (401)
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### Internal Server Error (500)
```json
{
    "success": false,
    "message": "خطأ في النظام",
    "data": {
        "error": "Database connection failed"
    }
}
```

---

## Usage Examples

### Frontend Dashboard Implementation
```javascript
// Fetch dashboard data
const response = await fetch('/api/v1/admin/dashboard', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Accept-Language': 'ar'
    }
});

const data = await response.json();

if (data.success) {
    // Update statistics cards
    updateStatisticsCards(data.data.statistics);
    
    // Populate latest orders table
    populateOrdersTable(data.data.latestOrders);
    
    // Create order status chart
    createStatusChart(data.data.orderStatusChart);
    
    // Show low stock alerts
    showLowStockAlerts(data.data.lowStockProducts);
}
```

### Chart.js Integration Example
```javascript
// Order Status Pie Chart
const ctx = document.getElementById('orderStatusChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['معلق', 'موافق عليه', 'مرفوض', 'مكتمل'],
        datasets: [{
            data: [
                data.data.orderStatusChart.pending,
                data.data.orderStatusChart.approved,
                data.data.orderStatusChart.rejected,
                data.data.orderStatusChart.completed
            ],
            backgroundColor: [
                '#fbbf24', // pending - yellow
                '#10b981', // approved - green
                '#ef4444', // rejected - red
                '#3b82f6'  // completed - blue
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
```

### Low Stock Alert Component
```javascript
function showLowStockAlerts(products) {
    const alertContainer = document.getElementById('lowStockAlerts');
    
    if (products.length === 0) {
        alertContainer.innerHTML = '<p>جميع المنتجات متوفرة في المخزون</p>';
        return;
    }
    
    const alertsHTML = products.map(product => `
        <div class="alert alert-warning">
            <strong>${product.productName}</strong>
            <br>المخزون الحالي: ${product.currentStock}
            <br>الحد الأدنى: ${product.minStock}
            <br>الحالة: ${product.stockStatus}
        </div>
    `).join('');
    
    alertContainer.innerHTML = alertsHTML;
}
```

---

## Performance Considerations

### Caching Strategy
Consider implementing caching for dashboard data:
```php
// In DashboardController
public function index(Request $request)
{
    $cacheKey = 'dashboard_data_' . auth()->id();
    
    return Cache::remember($cacheKey, 300, function () {
        // Dashboard data calculation
        return $this->getDashboardData();
    });
}
```

### Database Optimization
- Ensure proper indexing on frequently queried fields
- Use eager loading for relationships
- Consider database views for complex statistics

### Real-time Updates
For real-time dashboard updates, consider:
- WebSocket connections
- Server-Sent Events (SSE)
- Periodic AJAX polling

---

## Security Notes

### Access Control
- Endpoint requires authentication
- Consider adding role-based permissions
- Audit dashboard access for security monitoring

### Data Sensitivity
- Revenue data is sensitive - ensure proper access controls
- Client information should be handled according to privacy policies
- Consider data masking for non-admin users

---

## Testing

### Unit Tests
```php
public function test_dashboard_returns_correct_structure()
{
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
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
                    'orderStatusChart',
                    'lowStockProducts'
                ]
            ]);
}
```

### Integration Tests
```bash
# Test dashboard endpoint
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

## Localization

### Supported Languages
- Arabic (ar) - Default
- English (en)

### Localized Fields
- Status text (`statusText`)
- Stock status (`stockStatus`)
- Error messages
- Success messages

### Language Header
Use `Accept-Language` header to specify language preference:
```
Accept-Language: ar
```

---

## Rate Limiting
The dashboard endpoint may be subject to rate limiting to prevent abuse. Monitor response headers for rate limit information.

## Monitoring
Consider monitoring dashboard endpoint usage for:
- Performance optimization
- User behavior analysis
- System health monitoring
