# Client & Product Management API Documentation - V1

## نظرة عامة
هذه الوثيقة تحتوي على جميع APIs الخاصة بإدارة العملاء والمنتجات في لوحة التحكم.

## Base URLs
```
/api/v1/admin/clients
/api/v1/admin/products
```

## المعاملات العامة

### Accept-Language Header
يمكن إضافة header للغة المفضلة لجميع الـ endpoints:
```
Accept-Language: ar
Accept-Language: en
```
- `ar` - العربية (افتراضي)
- `en` - الإنجليزية

## Authentication & Authorization
جميع APIs تتطلب:
- **Authentication**: Bearer token (Sanctum)
- **Permissions**: صلاحيات محددة لكل عملية

---

# إدارة العملاء (Clients)

## GET /admin/clients
استرجاع قائمة العملاء مع إمكانية البحث.

**Required Permission**: `all_clients`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)
- `perPage` (query, optional): عدد السجلات في الصفحة (افتراضي: 15)
- `filter[search]` (query, optional): البحث في اسم العميل، البريد الإلكتروني، أو الهاتف
- `page` (query, optional): رقم الصفحة (افتراضي: 1)

**Response Example:**
```json
{
    "success": true,
    "message": "Clients retrieved successfully.",
    "data": {
        "clients": [
            {
                "clientId": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "1234567890",
                "address": "123 Main St",
                "city": "New York",
                "createdAt": "2023-01-01T00:00:00Z"
            }
        ],
        "pagination": {
            "total": 100,
            "count": 15,
            "perPage": 15,
            "currentPage": 1,
            "totalPages": 7
        }
    }
}
```

## GET /admin/clients/{id}
استرجاع تفاصيل عميل محدد.

**Required Permission**: `edit_client`

**Parameters:**
- `id` (path, required): معرف العميل
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Client retrieved successfully.",
    "data": {
        "clientId": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "address": "123 Main St",
        "city": "New York"
    }
}
```

## PUT /admin/clients/{id}
تحديث معلومات عميل موجود.

**Required Permission**: `update_client`

**Parameters:**
- `id` (path, required): معرف العميل المراد تحديثه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (JSON):**
```json
{
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "0987654321",
    "address": "456 Updated St",
    "city": "Los Angeles"
}
```

**Required Fields:**
- `name` (string, max: 255): اسم العميل
- `email` (string, email, unique, max: 255): البريد الإلكتروني

**Optional Fields:**
- `phone` (string, max: 20): رقم الهاتف
- `address` (string, max: 500): العنوان
- `city` (string, max: 100): المدينة

**Response Example:**
```json
{
    "success": true,
    "message": "Client updated successfully.",
    "data": {}
}
```

## DELETE /admin/clients/{id}
حذف عميل من النظام.

**Required Permission**: `delete_client`

**Parameters:**
- `id` (path, required): معرف العميل المراد حذفه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Client deleted successfully.",
    "data": {}
}
```

**Note**: لا يمكن حذف العملاء الذين لديهم طلبات موجودة.

---

# إدارة المنتجات (Products)

## GET /admin/products
استرجاع قائمة المنتجات مع فلاتر متقدمة.

**Required Permission**: `all_products`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)
- `perPage` (query, optional): عدد السجلات في الصفحة (افتراضي: 15)
- `filter[search]` (query, optional): البحث في اسم أو وصف المنتج
- `filter[status]` (query, optional): فلترة حسب الحالة (`active`, `inactive`, `draft`)
- `filter[brand]` (query, optional): فلترة حسب معرف البراند
- `filter[category]` (query, optional): فلترة حسب معرف التصنيف
- `filter[stockStatus]` (query, optional): فلترة حسب حالة المخزون (0=نفد، 1=قليل، 2=متوفر، 3=لا يوجد مخزون)
- `filter[price]` (query, optional): فلترة حسب نطاق السعر (صيغة: min,max)
- `sort` (query, optional): ترتيب النتائج (`created_at`, `-created_at`, `price`, `-price`, `cost`, `-cost`)
- `page` (query, optional): رقم الصفحة (افتراضي: 1)

**Response Example:**
```json
{
    "success": true,
    "message": "Products retrieved successfully.",
    "data": {
        "products": [
            {
                "productId": 1,
                "name": "iPhone 14",
                "description": "Latest iPhone model",
                "slug": "iphone-14",
                "price": 999.99,
                "cost": 700.00,
                "status": "active",
                "hasStock": true,
                "minStock": 10,
                "brand": {
                    "id": 1,
                    "name": "Apple"
                },
                "category": {
                    "id": 1,
                    "name": "Electronics"
                },
                "media": {
                    "url": "products/iphone14.jpg",
                    "mediaType": "image"
                },
                "inventory": {
                    "quantity": 50,
                    "stockStatus": 2
                }
            }
        ],
        "pagination": {
            "total": 200,
            "count": 15,
            "perPage": 15,
            "currentPage": 1,
            "totalPages": 14
        }
    }
}
```

## POST /admin/products
إنشاء منتج جديد.

**Required Permission**: `create_product`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (multipart/form-data):**
```json
{
    "name": "iPhone 14",
    "description": "Latest iPhone model with advanced features",
    "slug": "iphone-14",
    "price": 999.99,
    "cost": 700.00,
    "status": "active",
    "brandId": 1,
    "categoryId": 1,
    "hasStock": true,
    "minStock": 10,
    "media": "file"
}
```

**Required Fields:**
- `name` (string, max: 255, unique): اسم المنتج
- `slug` (string, max: 255, unique): رابط المنتج
- `price` (number, min: 0): سعر البيع
- `status` (enum: active, inactive, draft): حالة المنتج
- `hasStock` (boolean): هل يدير المنتج المخزون

**Optional Fields:**
- `description` (string): وصف المنتج
- `cost` (number, min: 0): سعر التكلفة
- `brandId` (integer, exists in brands): معرف البراند
- `categoryId` (integer, exists in categories): معرف التصنيف
- `minStock` (integer, min: 0): الحد الأدنى للمخزون
- `media` (file, image, max: 5MB): صورة المنتج

**Response Example:**
```json
{
    "success": true,
    "message": "Product created successfully.",
    "data": {}
}
```

## GET /admin/products/{id}
استرجاع تفاصيل منتج محدد.

**Required Permission**: `edit_product`

**Parameters:**
- `id` (path, required): معرف المنتج
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Product retrieved successfully.",
    "data": {
        "productId": 1,
        "name": "iPhone 14",
        "description": "Latest iPhone model",
        "slug": "iphone-14",
        "price": 999.99,
        "cost": 700.00,
        "status": "active",
        "hasStock": true,
        "minStock": 10,
        "brand": {
            "id": 1,
            "name": "Apple"
        },
        "category": {
            "id": 1,
            "name": "Electronics"
        },
        "media": {
            "url": "products/iphone14.jpg",
            "mediaType": "image"
        },
        "inventory": {
            "quantity": 50,
            "stockStatus": 2
        }
    }
}
```

## PUT /admin/products/{id}
تحديث معلومات منتج موجود.

**Required Permission**: `update_product`

**Parameters:**
- `id` (path, required): معرف المنتج المراد تحديثه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (multipart/form-data):**
```json
{
    "name": "iPhone 14 Pro",
    "description": "Updated iPhone model with pro features",
    "slug": "iphone-14-pro",
    "price": 1199.99,
    "cost": 800.00,
    "status": "active",
    "brandId": 1,
    "categoryId": 1,
    "hasStock": true,
    "minStock": 15,
    "media": "file"
}
```

**Response Example:**
```json
{
    "success": true,
    "message": "Product updated successfully.",
    "data": {}
}
```

## DELETE /admin/products/{id}
حذف منتج من النظام.

**Required Permission**: `delete_product`

**Parameters:**
- `id` (path, required): معرف المنتج المراد حذفه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Product deleted successfully.",
    "data": {}
}
```

**Note**: لا يمكن حذف المنتجات التي لديها طلبات موجودة.

---

# حالات المنتجات (Product Status)

| Value | Description |
|-------|-------------|
| `active` | نشط - متاح للعرض والشراء |
| `inactive` | غير نشط - غير متاح للعرض |
| `draft` | مسودة - قيد التطوير |

# حالات المخزون (Stock Status)

| Value | Description |
|-------|-------------|
| `0` | نفد المخزون (Out of Stock) |
| `1` | مخزون قليل (Low Stock) |
| `2` | متوفر في المخزون (In Stock) |
| `3` | لا يوجد إدارة مخزون (No Inventory) |

---

# رسائل الخطأ الشائعة

## 401 Unauthorized - غير مصرح
```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": {}
}
```
**الأسباب**: عدم وجود Bearer token، token غير صحيح، أو token منتهي الصلاحية

## 403 Forbidden - محظور
```json
{
    "success": false,
    "message": "This action is unauthorized.",
    "data": {}
}
```
**الأسباب**: عدم وجود الصلاحية المطلوبة للعملية

## 404 Not Found - غير موجود
```json
{
    "success": false,
    "message": "Client not found.",
    "data": {}
}
```
```json
{
    "success": false,
    "message": "Product not found.",
    "data": {}
}
```
**الأسباب**: العميل أو المنتج المطلوب غير موجود في قاعدة البيانات

## 422 Validation Error - خطأ تحقق
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name has already been taken."],
        "email": ["The email has already been taken."],
        "slug": ["The slug has already been taken."]
    }
}
```
**الأسباب**: البيانات المرسلة لا تتوافق مع قواعد التحقق

## 422 Business Logic Error - خطأ منطق العمل
```json
{
    "success": false,
    "message": "Cannot delete client with existing orders.",
    "data": {}
}
```
```json
{
    "success": false,
    "message": "Cannot delete product with existing orders.",
    "data": {}
}
```
**الأسباب**: محاولة حذف عميل أو منتج له طلبات موجودة

## 500 Internal Server Error - خطأ خادم داخلي
```json
{
    "success": false,
    "message": "An error occurred while processing your request.",
    "data": {}
}
```
**الأسباب**: خطأ في قاعدة البيانات، فشل المعاملة، أو خطأ في النظام

---

# قواعد التحقق (Validation Rules)

## العملاء (Clients)

### تحديث عميل
- `name`: مطلوب، نص، حد أقصى 255 حرف
- `email`: مطلوب، بريد إلكتروني صحيح، فريد (باستثناء العميل الحالي)، حد أقصى 255 حرف
- `phone`: اختياري، نص، تنسيق هاتف صحيح، حد أقصى 20 حرف
- `address`: اختياري، نص، حد أقصى 500 حرف
- `city`: اختياري، نص، حد أقصى 100 حرف

## المنتجات (Products)

### إنشاء منتج جديد
- `name`: مطلوب، نص، حد أقصى 255 حرف، فريد
- `description`: اختياري، نص
- `slug`: مطلوب، نص، حد أقصى 255 حرف، فريد
- `status`: مطلوب، enum (active, inactive, draft)
- `brandId`: اختياري، رقم صحيح، يجب أن يكون براند موجود
- `categoryId`: اختياري، رقم صحيح، يجب أن يكون تصنيف موجود
- `cost`: اختياري، رقم، حد أدنى 0
- `price`: مطلوب، رقم، حد أدنى 0
- `minStock`: اختياري، رقم صحيح، حد أدنى 0
- `hasStock`: مطلوب، boolean
- `media`: اختياري، ملف صورة، أنواع مدعومة: jpg,jpeg,png,webp، حد أقصى 5MB

### تحديث منتج
- نفس قواعد الإنشاء مع استثناء المنتج الحالي من فحص الفرادة

---

# الصلاحيات المطلوبة (Required Permissions)

## العملاء (Clients)
| Endpoint | Permission | Description |
|----------|------------|-------------|
| GET /clients | `all_clients` | عرض قائمة العملاء |
| GET /clients/{id} | `edit_client` | عرض تفاصيل عميل |
| PUT /clients/{id} | `update_client` | تحديث عميل |
| DELETE /clients/{id} | `delete_client` | حذف عميل |

## المنتجات (Products)
| Endpoint | Permission | Description |
|----------|------------|-------------|
| GET /products | `all_products` | عرض قائمة المنتجات |
| POST /products | `create_product` | إنشاء منتج جديد |
| GET /products/{id} | `edit_product` | عرض تفاصيل منتج |
| PUT /products/{id} | `update_product` | تحديث منتج |
| DELETE /products/{id} | `delete_product` | حذف منتج |

---

# أمثلة على الاستخدام

## العملاء (Clients)

### استرجاع العملاء مع بحث
```bash
curl -X GET "http://localhost:8000/api/v1/admin/clients?filter[search]=john&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### تحديث عميل
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/clients/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "0987654321",
    "address": "456 Updated St",
    "city": "Los Angeles"
  }'
```

## المنتجات (Products)

### استرجاع المنتجات مع فلاتر
```bash
curl -X GET "http://localhost:8000/api/v1/admin/products?filter[search]=iPhone&filter[status]=active&filter[brand]=1&sort=-created_at&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### إنشاء منتج جديد
```bash
curl -X POST "http://localhost:8000/api/v1/admin/products" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=iPhone 14" \
  -F "description=Latest iPhone model" \
  -F "slug=iphone-14" \
  -F "price=999.99" \
  -F "cost=700.00" \
  -F "status=active" \
  -F "brandId=1" \
  -F "categoryId=1" \
  -F "hasStock=true" \
  -F "minStock=10" \
  -F "media=@/path/to/image.jpg"
```

### فلترة المنتجات حسب نطاق السعر
```bash
curl -X GET "http://localhost:8000/api/v1/admin/products?filter[price]=100,1000&sort=price" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### فلترة المنتجات حسب حالة المخزون
```bash
curl -X GET "http://localhost:8000/api/v1/admin/products?filter[stockStatus]=2" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

# ملاحظات مهمة

1. **الأمان**: جميع APIs محمية بـ Sanctum authentication
2. **الصلاحيات**: كل endpoint يتطلب صلاحية محددة
3. **تعدد اللغات**: دعم كامل للعربية والإنجليزية
4. **التحقق**: تحقق شامل من البيانات مع رسائل خطأ واضحة
5. **الترقيم**: دعم pagination لقوائم العملاء والمنتجات
6. **البحث والفلترة**: إمكانيات بحث وفلترة متقدمة
7. **إدارة الملفات**: دعم رفع صور المنتجات مع معالجة آمنة
8. **إدارة المخزون**: نظام متقدم لإدارة مخزون المنتجات
9. **حماية البيانات**: منع حذف العملاء والمنتجات المرتبطة بطلبات
10. **المعاملات**: استخدام database transactions لضمان سلامة البيانات
