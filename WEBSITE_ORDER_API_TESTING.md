# اختبار Website Order APIs

## قائمة بجميع الـ Routes الجديدة للطلبات

### 1. إنشاء طلب جديد
```bash
POST /api/v1/website/orders
```

### 2. عرض طلب محدد
```bash
GET /api/v1/website/orders/{orderNumber}
```

### 3. تتبع طلب
```bash
POST /api/v1/website/orders/track
```

### 4. طلبات العميل
```bash
POST /api/v1/website/orders/client-orders
```

### 5. التحقق من السلة
```bash
POST /api/v1/website/orders/validate-cart
```

### 6. إلغاء طلب
```bash
POST /api/v1/website/orders/{orderNumber}/cancel
```

## أمثلة للاختبار باستخدام cURL

### 1. إنشاء طلب جديد
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "01234567890",
    "address": "شارع النيل، المعادي",
    "city": "القاهرة",
    "note": "توصيل سريع من فضلك",
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

### 2. عرض طلب محدد
```bash
curl -X GET "http://localhost:8000/api/v1/website/orders/ORD-23112025-1234" \
  -H "Accept: application/json"
```

### 3. تتبع طلب
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/track" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ahmed@example.com",
    "orderNumber": "ORD-23112025-1234"
  }'
```

### 4. عرض طلبات العميل
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/client-orders" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ahmed@example.com",
    "perPage": 10
  }'
```

### 5. التحقق من السلة
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/validate-cart" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
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

### 6. إلغاء طلب
```bash
curl -X POST "http://localhost:8000/api/v1/website/orders/ORD-23112025-1234/cancel" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ahmed@example.com",
    "reason": "تغيير في الطلب"
  }'
```

## بيانات تجريبية للاختبار

### إنشاء عملاء تجريبيين
```sql
INSERT INTO clients (name, email, phone, address, city, created_at, updated_at) VALUES
('أحمد محمد', 'ahmed@example.com', '01234567890', 'شارع النيل، المعادي', 'القاهرة', NOW(), NOW()),
('فاطمة علي', 'fatima@example.com', '01987654321', 'شارع الجامعة، الجيزة', 'الجيزة', NOW(), NOW()),
('محمد حسن', 'mohamed@example.com', '01122334455', 'شارع الحرية، الإسكندرية', 'الإسكندرية', NOW(), NOW());
```

### إنشاء منتجات تجريبية (إذا لم تكن موجودة)
```sql
INSERT INTO products (name, slug, description, price, cost, status, category_id, has_stock, min_stock, created_at, updated_at) VALUES
('كريم مرطب للوجه', 'face-moisturizer', 'كريم مرطب طبيعي للعناية بالبشرة الجافة', 150.00, 100.00, 'active', 1, 1, 10, NOW(), NOW()),
('شامبو للشعر الدهني', 'oily-hair-shampoo', 'شامبو خاص للشعر الدهني بخلاصة الليمون', 80.00, 50.00, 'active', 2, 1, 15, NOW(), NOW()),
('عطر زهور الياسمين', 'jasmine-perfume', 'عطر نسائي برائحة الياسمين الطبيعي', 300.00, 200.00, 'active', 3, 1, 5, NOW(), NOW());
```

### إنشاء مخزون للمنتجات
```sql
INSERT INTO inventories (product_id, quantity, created_at, updated_at) VALUES
(1, 50, NOW(), NOW()),
(2, 30, NOW(), NOW()),
(3, 25, NOW(), NOW());
```

## سيناريوهات الاختبار

### 1. اختبار إنشاء طلب ناجح
- استخدم منتجات متوفرة في المخزون
- تأكد من صحة بيانات العميل
- تحقق من إنشاء رقم الطلب تلقائياً

### 2. اختبار إنشاء طلب مع منتج غير متوفر
- استخدم منتج بكمية أكبر من المتوفر
- تحقق من رسالة الخطأ المناسبة

### 3. اختبار التحقق من السلة
- اختبر مع منتجات متوفرة وغير متوفرة
- تحقق من حساب المجموع الصحيح

### 4. اختبار تتبع الطلب
- استخدم بريد إلكتروني ورقم طلب صحيحين
- اختبر مع بيانات خاطئة

### 5. اختبار إلغاء الطلب
- اختبر إلغاء طلب في حالة pending
- اختبر إلغاء طلب في حالة أخرى (يجب أن يفشل)

## التحقق من النتائج

### بعد إنشاء طلب
```sql
-- تحقق من إنشاء الطلب
SELECT * FROM orders WHERE client_id = (SELECT id FROM clients WHERE email = 'ahmed@example.com');

-- تحقق من عناصر الطلب
SELECT oi.*, p.name as product_name 
FROM order_items oi 
JOIN products p ON oi.product_id = p.id 
WHERE oi.order_id = [ORDER_ID];

-- تحقق من تحديث المخزون (إذا كان الطلب مؤكد)
SELECT * FROM inventories WHERE product_id IN (1, 2, 3);
```

### بعد إلغاء طلب
```sql
-- تحقق من تحديث حالة الطلب
SELECT status, note FROM orders WHERE number = 'ORD-23112025-1234';
```

## ملاحظات للاختبار

1. **تأكد من وجود البيانات**: categories, products, inventories
2. **حالة المنتجات**: يجب أن تكون `status = 'active'`
3. **المخزون**: يجب أن تحتوي على `quantity > 0`
4. **البريد الإلكتروني**: استخدم بريد إلكتروني صحيح للاختبار
5. **أرقام الطلبات**: يتم إنشاؤها تلقائياً بصيغة `ORD-DDMMYYYY-XXXX`

## أخطاء شائعة وحلولها

### خطأ: "المنتج غير موجود"
- تأكد من وجود المنتج في قاعدة البيانات
- تحقق من صحة `productId`

### خطأ: "الكمية المطلوبة غير متوفرة"
- تحقق من المخزون في جدول `inventories`
- تأكد من أن `quantity >= requested_quantity`

### خطأ: "لم يتم العثور على الطلب"
- تحقق من صحة رقم الطلب
- تأكد من تطابق البريد الإلكتروني

### خطأ: "لا يمكن إلغاء هذا الطلب"
- تحقق من حالة الطلب (يجب أن تكون `pending`)

## مثال على استجابة ناجحة

```json
{
    "success": true,
    "message": "تم إنشاء الطلب بنجاح",
    "data": {
        "id": 1,
        "orderNumber": "ORD-23112025-1234",
        "status": "pending",
        "statusLabel": "في الانتظار",
        "totalAmount": 230.00,
        "totalAfterDiscount": 230.00,
        "discount": 0,
        "note": "توصيل سريع من فضلك",
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
                    "slug": "face-moisturizer"
                }
            }
        ],
        "itemsCount": 2
    }
}
```
