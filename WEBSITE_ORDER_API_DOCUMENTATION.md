# Website Order API Documentation - V1

## نظرة عامة
هذه الوثيقة تحتوي على جميع APIs الخاصة بالطلبات في الموقع للعملاء.

## Base URL
```
/api/v1/website
```

## الطلبات (Orders)

### POST /orders
إنشاء طلب جديد من الموقع.

**Request Body:**
```json
{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "01234567890",
    "address": "شارع النيل، المعادي",
    "city": "القاهرة",
    "note": "ملاحظة اختيارية",
    "items": [
        {
            "productId": 1,
            "quantity": 2
        },
        {
            "productId": 3,
            "quantity": 1
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم إنشاء الطلب بنجاح",
    "data": {
        "id": 1,
        "orderNumber": "ORD-23112025-1234",
        "status": "pending",
        "statusLabel": "في الانتظار",
        "totalAmount": 350.00,
        "totalAfterDiscount": 350.00,
        "discount": 0,
        "note": "ملاحظة اختيارية",
        "createdAt": "2025-11-23 14:30:00",
        "createdAtFormatted": "23 نوفمبر 2025 - 02:30 م",
        "client": {
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "01234567890",
            "address": "شارع النيل، المعادي",
            "city": "القاهرة"
        },
        "items": [
            {
                "id": 1,
                "quantity": 2,
                "unitPrice": 150.00,
                "totalPrice": 300.00,
                "product": {
                    "id": 1,
                    "name": "كريم مرطب للوجه",
                    "slug": "face-moisturizer",
                    "image": {
                        "url": "products/image.jpg",
                        "type": "image"
                    },
                    "brand": {
                        "id": 1,
                        "name": "براند التجميل"
                    },
                    "category": {
                        "id": 1,
                        "name": "مستحضرات التجميل",
                        "slug": "cosmetics"
                    }
                }
            }
        ],
        "itemsCount": 2
    }
}
```

### GET /orders/{orderNumber}
عرض تفاصيل طلب محدد برقم الطلب.

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "orderNumber": "ORD-23112025-1234",
        "status": "pending",
        "statusLabel": "في الانتظار",
        "totalAmount": 350.00,
        "totalAfterDiscount": 350.00,
        "discount": 0,
        "note": "ملاحظة اختيارية",
        "createdAt": "2025-11-23 14:30:00",
        "createdAtFormatted": "23 نوفمبر 2025 - 02:30 م",
        "client": {
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "01234567890",
            "address": "شارع النيل، المعادي",
            "city": "القاهرة"
        },
        "items": [...],
        "itemsCount": 2
    }
}
```

### POST /orders/track
تتبع طلب باستخدام البريد الإلكتروني ورقم الطلب.

**Request Body:**
```json
{
    "email": "ahmed@example.com",
    "orderNumber": "ORD-23112025-1234"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        // نفس بيانات الطلب
    }
}
```

### POST /orders/client-orders
عرض جميع طلبات عميل محدد باستخدام البريد الإلكتروني.

**Request Body:**
```json
{
    "email": "ahmed@example.com",
    "perPage": 10
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "orderNumber": "ORD-23112025-1234",
                "status": "pending",
                "statusLabel": "في الانتظار",
                "totalAmount": 350.00,
                "totalAfterDiscount": 350.00,
                "discount": 0,
                "createdAt": "2025-11-23 14:30:00",
                "createdAtFormatted": "23 نوفمبر 2025 - 02:30 م",
                "itemsCount": 2
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 10,
            "total": 1,
            "from": 1,
            "to": 1
        }
    }
}
```

### POST /orders/validate-cart
التحقق من صحة عناصر السلة قبل إتمام الطلب.

**Request Body:**
```json
{
    "items": [
        {
            "productId": 1,
            "quantity": 2
        },
        {
            "productId": 3,
            "quantity": 1
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "isValid": true,
        "totalAmount": 350.00,
        "items": [
            {
                "productId": 1,
                "requestedQuantity": 2,
                "isValid": true,
                "errors": [],
                "subtotal": 300.00,
                "unitPrice": 150.00,
                "product": {
                    "id": 1,
                    "name": "كريم مرطب للوجه",
                    "price": 150.00,
                    "image": "products/image.jpg",
                    "availableQuantity": 50
                }
            },
            {
                "productId": 3,
                "requestedQuantity": 1,
                "isValid": false,
                "errors": ["الكمية المطلوبة غير متوفرة"],
                "availableQuantity": 0,
                "product": {
                    "id": 3,
                    "name": "منتج غير متوفر",
                    "price": 50.00,
                    "image": null,
                    "availableQuantity": 0
                }
            }
        ]
    }
}
```

### POST /orders/{orderNumber}/cancel
إلغاء طلب (فقط للطلبات في حالة الانتظار).

**Request Body:**
```json
{
    "email": "ahmed@example.com",
    "reason": "تغيير في الطلب"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم إلغاء الطلب بنجاح"
}
```

## حالات الطلبات (Order Status)

| Status | Arabic Label | Description |
|--------|-------------|-------------|
| `pending` | في الانتظار | الطلب في انتظار المراجعة |
| `approved` | مؤكد | تم تأكيد الطلب |
| `rejected` | مرفوض | تم رفض الطلب |
| `completed` | مكتمل | تم إكمال الطلب |

## رسائل الخطأ الشائعة

### 400 Bad Request
```json
{
    "success": false,
    "message": "المنتج غير متاح حالياً: اسم المنتج"
}
```

```json
{
    "success": false,
    "message": "الكمية المطلوبة غير متوفرة للمنتج: اسم المنتج"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "لم يتم العثور على الطلب"
}
```

### 422 Validation Error
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": ["البريد الإلكتروني مطلوب"],
        "items": ["يجب إضافة منتج واحد على الأقل"]
    }
}
```

## ملاحظات مهمة

1. **لا تتطلب Authentication**: جميع APIs مفتوحة للعملاء
2. **التحقق من المخزون**: يتم التحقق من توفر المنتجات والكميات
3. **إنشاء العملاء**: يتم إنشاء عميل جديد تلقائياً إذا لم يكن موجوداً
4. **رقم الطلب**: يتم إنشاؤه تلقائياً بصيغة `ORD-DDMMYYYY-XXXX`
5. **الحالة الافتراضية**: جميع الطلبات تبدأ بحالة `pending`
6. **الإلغاء**: يمكن إلغاء الطلبات فقط في حالة `pending`

## أمثلة على الاستخدام

### إنشاء طلب جديد
```javascript
const orderData = {
    name: "أحمد محمد",
    email: "ahmed@example.com",
    phone: "01234567890",
    address: "شارع النيل، المعادي",
    city: "القاهرة",
    items: [
        { productId: 1, quantity: 2 },
        { productId: 3, quantity: 1 }
    ]
};

fetch('/api/v1/website/orders', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(orderData)
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Order created:', data.data.orderNumber);
    }
});
```

### تتبع طلب
```javascript
const trackData = {
    email: "ahmed@example.com",
    orderNumber: "ORD-23112025-1234"
};

fetch('/api/v1/website/orders/track', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(trackData)
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Order status:', data.data.statusLabel);
    }
});
```

### التحقق من السلة
```javascript
const cartData = {
    items: [
        { productId: 1, quantity: 2 },
        { productId: 3, quantity: 1 }
    ]
};

fetch('/api/v1/website/orders/validate-cart', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(cartData)
})
.then(response => response.json())
.then(data => {
    if (data.success && data.data.isValid) {
        console.log('Cart is valid, total:', data.data.totalAmount);
    } else {
        console.log('Cart has errors:', data.data.items);
    }
});
```
