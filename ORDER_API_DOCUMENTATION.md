# Order Management API Documentation

## Overview
This document provides comprehensive documentation for the Order Management API endpoints. The API allows administrators to manage orders, including creating, updating, approving, rejecting, and tracking order statistics.

## Base URL
```
/api/v1/admin/orders
```

## Authentication
All endpoints require authentication using Sanctum tokens:
```
Authorization: Bearer {token}
```

## Headers
- `Accept: application/json`
- `Content-Type: application/json`
- `Accept-Language: ar|en` (optional, for localization)

## Order Status Enum
- `0` - Pending
- `1` - Approved  
- `2` - Rejected
- `3` - Completed

## Discount Type Enum
- `0` - No Discount
- `1` - Fixed Amount
- `2` - Percentage

## Action Status (for Order Items)
- `1` - New Item
- `2` - Update Item
- `3` - Delete Item

---

## Endpoints

### 1. Get Orders List
**GET** `/api/v1/admin/orders`

Retrieve a paginated list of orders with optional filtering.

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `filter[status]` | integer | No | Filter by order status (0-3) |
| `filter[client]` | integer | No | Filter by client ID |
| `filter[date]` | string | No | Filter by date range (YYYY-MM-DD,YYYY-MM-DD) |
| `filter[search]` | string | No | Search in order number, client name, email, phone |
| `sort` | string | No | Sort field (created_at, total_amount, status). Prefix with - for desc |
| `perPage` | integer | No | Items per page (1-100, default: 15) |

#### Example Request
```bash
GET /api/v1/admin/orders?filter[status]=0&sort=-created_at&perPage=20
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "orders": [
            {
                "orderId": 1,
                "number": "ORD-2024-001",
                "totalCost": 150.00,
                "totalAmount": 200.00,
                "status": 0,
                "totalAmountAfterDiscount": 180.00,
                "client": {
                    "name": "أحمد محمد",
                    "email": "ahmed@example.com"
                },
                "createdAt": "01/12/24 02:30 م"
            }
        ],
        "pagination": {
            "total": 50,
            "count": 15,
            "perPage": 15,
            "currentPage": 1,
            "totalPages": 4
        }
    }
}
```

---

### 2. Create Order
**POST** `/api/v1/admin/orders`

Create a new order for existing or new client.

#### Request Body
```json
{
    "clientId": 1,
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "+201234567890",
    "address": "شارع النيل، المعادي",
    "city": "القاهرة",
    "note": "طلب عاجل",
    "status": 0,
    "discount": 20.00,
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
}
```

#### Validation Rules
- `clientId`: Required if creating order for existing client
- `name`: Required if clientId is null
- `email`: Required if clientId is null, must be unique
- `phone`: Optional, valid phone format
- `address`: Optional string
- `city`: Optional string
- `note`: Optional string
- `status`: Required, valid OrderStatusEnum
- `discount`: Optional numeric, minimum 0
- `discountType`: Optional, valid DiscountTypeEnum
- `orderItems`: Required array, minimum 1 item

#### Example Response
```json
{
    "success": true,
    "message": "تم الإنشاء بنجاح",
    "data": []
}
```

---

### 3. Get Order Details
**GET** `/api/v1/admin/orders/{order}`

Retrieve detailed information about a specific order.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "orderId": 1,
        "number": "ORD-2024-001",
        "totalCost": 150.00,
        "totalAmount": 200.00,
        "status": 0,
        "totalAmountAfterDiscount": 180.00,
        "discount": 20.00,
        "discountType": 2,
        "client": {
            "clientId": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "+201234567890",
            "address": "شارع النيل، المعادي",
            "city": "القاهرة"
        },
        "orderItems": [
            {
                "orderItemId": 1,
                "quantity": 2,
                "price": 100.00,
                "totalPrice": 200.00,
                "product": {
                    "product": 1,
                    "productName": "منتج تجريبي"
                }
            }
        ],
        "createdAt": "01/12/24 02:30 م"
    }
}
```

---

### 4. Update Order
**PUT** `/api/v1/admin/orders/{order}`

Update an existing order including order items with action status.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Request Body
```json
{
    "clientId": 1,
    "note": "طلب محدث",
    "status": 1,
    "discount": 30.00,
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
            "quantity": 1,
            "actionStatus": 1
        },
        {
            "orderItemId": 3,
            "productId": 3,
            "quantity": 1,
            "actionStatus": 3
        }
    ]
}
```

#### Action Status Explanation
- `1` (New): Add new item to order
- `2` (Update): Update existing item (requires orderItemId)
- `3` (Delete): Remove item from order (requires orderItemId)

#### Example Response
```json
{
    "success": true,
    "message": "Order updated successfully",
    "data": []
}
```

---

### 5. Delete Order
**DELETE** `/api/v1/admin/orders/{order}`

Delete an order (only pending or rejected orders can be deleted).

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Example Response
```json
{
    "success": true,
    "message": "Order deleted successfully",
    "data": []
}
```

---

### 6. Approve Order
**POST** `/api/v1/admin/orders/{order}/approve`

Approve a pending order and reduce stock for all order items.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Example Response
```json
{
    "success": true,
    "message": "تم الموافقة على الطلب بنجاح",
    "data": []
}
```

---

### 7. Reject Order
**POST** `/api/v1/admin/orders/{order}/reject`

Reject a pending order with optional reason.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Request Body
```json
{
    "reason": "المنتج غير متوفر"
}
```

#### Example Response
```json
{
    "success": true,
    "message": "Order rejected successfully",
    "data": {
        "orderId": 1,
        "number": "ORD-2024-001",
        "totalCost": 150.00,
        "totalAmount": 200.00,
        "status": 2,
        "totalAmountAfterDiscount": 180.00,
        "discount": 20.00,
        "discountType": 2,
        "client": {
            "clientId": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "+201234567890",
            "address": "شارع النيل، المعادي",
            "city": "القاهرة"
        },
        "orderItems": [
            {
                "orderItemId": 1,
                "quantity": 2,
                "price": 100.00,
                "totalPrice": 200.00,
                "product": {
                    "product": 1,
                    "productName": "منتج تجريبي"
                }
            }
        ],
        "createdAt": "01/12/24 02:30 م"
    }
}
```

---

### 8. Complete Order
**POST** `/api/v1/admin/orders/{order}/complete`

Mark an approved order as completed.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Example Response
```json
{
    "success": true,
    "message": "تم تسليم الطلب بنجاح",
    "data": {
        "orderId": 1,
        "number": "ORD-2024-001",
        "totalCost": 150.00,
        "totalAmount": 200.00,
        "status": 3,
        "totalAmountAfterDiscount": 180.00,
        "discount": 20.00,
        "discountType": 2,
        "client": {
            "clientId": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "+201234567890",
            "address": "شارع النيل، المعادي",
            "city": "القاهرة"
        },
        "orderItems": [
            {
                "orderItemId": 1,
                "quantity": 2,
                "price": 100.00,
                "totalPrice": 200.00,
                "product": {
                    "product": 1,
                    "productName": "منتج تجريبي"
                }
            }
        ],
        "createdAt": "01/12/24 02:30 م"
    }
}
```

---

### 9. Get Order Statistics
**GET** `/api/v1/admin/orders/statistics`

Retrieve comprehensive order statistics.

#### Example Response
```json
{
    "success": true,
    "message": "Order statistics retrieved successfully",
    "data": {
        "totalOrders": 150,
        "pendingOrders": 25,
        "approvedOrders": 80,
        "rejectedOrders": 15,
        "completedOrders": 30,
        "totalRevenue": 45000.00,
        "averageOrderValue": 300.00,
        "ordersToday": 5,
        "revenueToday": 1500.00
    }
}
```

---

### 10. Get Order Status Counts
**GET** `/api/v1/admin/orders/status-counts`

Retrieve the count of orders grouped by their status.

#### Example Response
```json
{
    "success": true,
    "message": "Order status counts retrieved successfully",
    "data": {
        "pending": 25,
        "approved": 80,
        "rejected": 15,
        "completed": 30
    }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "",
    "data": {
        "orderItems": [
            "The order items field is required."
        ]
    }
}
```

### Bad Request (400)
```json
{
    "success": false,
    "message": "Insufficient stock for product: Product Name",
    "data": []
}
```

### Unauthorized (401)
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Order not found"
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

## Business Logic Notes

### Stock Management
- Stock is automatically reduced when orders are approved or completed
- Stock availability is checked before creating or updating orders
- Insufficient stock will result in a 400 error

### Order Status Flow
1. **Pending** → Can be approved, rejected, updated, or deleted
2. **Approved** → Can be completed or updated
3. **Rejected** → Can be deleted
4. **Completed** → Final status, cannot be changed

### Discount Calculation
- **Fixed**: Direct amount subtraction
- **Percentage**: Calculated as (total_amount * discount) / 100
- Total after discount cannot be negative

### Client Management
- Orders can be created for existing clients using `clientId`
- New clients can be created on-the-fly by providing client details
- Client information is validated and must be unique (email)

---

## Rate Limiting
API endpoints may be subject to rate limiting. Check response headers for rate limit information.

## Pagination
List endpoints support pagination with the following parameters:
- `perPage`: Number of items per page (1-100, default: 15)
- Response includes pagination metadata

## Localization
Use the `Accept-Language` header to receive localized messages:
- `ar`: Arabic
- `en`: English (default)
