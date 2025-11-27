# Brand & Category Management API Documentation - V1

## نظرة عامة
هذه الوثيقة تحتوي على جميع APIs الخاصة بإدارة البراندات والتصنيفات في لوحة التحكم.

## Base URLs
```
/api/v1/admin/brands
/api/v1/admin/categories
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

# إدارة البراندات (Brands)

## GET /admin/brands
استرجاع قائمة البراندات مع إمكانية البحث.

**Required Permission**: `all_brands`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)
- `perPage` (query, optional): عدد السجلات في الصفحة (افتراضي: 15)
- `filter[search]` (query, optional): البحث في اسم البراند
- `page` (query, optional): رقم الصفحة (افتراضي: 1)

**Response Example:**
```json
{
    "success": true,
    "message": "Brands retrieved successfully.",
    "data": {
        "brands": [
            {
                "brandId": 1,
                "name": "Nike",
                "createdAt": "2023-01-01T00:00:00Z"
            },
            {
                "brandId": 2,
                "name": "Adidas",
                "createdAt": "2023-01-02T00:00:00Z"
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

## POST /admin/brands
إنشاء براند جديد.

**Required Permission**: `create_brand`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (JSON):**
```json
{
    "name": "Puma"
}
```

**Required Fields:**
- `name` (string, max: 255, unique): اسم البراند

**Response Example:**
```json
{
    "success": true,
    "message": "Brand created successfully.",
    "data": {}
}
```

## GET /admin/brands/{id}
استرجاع تفاصيل براند محدد.

**Required Permission**: `edit_brand`

**Parameters:**
- `id` (path, required): معرف البراند
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Brand retrieved successfully.",
    "data": {
        "brandId": 1,
        "name": "Nike"
    }
}
```

## PUT /admin/brands/{id}
تحديث معلومات براند موجود.

**Required Permission**: `update_brand`

**Parameters:**
- `id` (path, required): معرف البراند المراد تحديثه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (JSON):**
```json
{
    "name": "Nike Updated"
}
```

**Required Fields:**
- `name` (string, max: 255, unique): اسم البراند

**Response Example:**
```json
{
    "success": true,
    "message": "Brand updated successfully.",
    "data": {}
}
```

## DELETE /admin/brands/{id}
حذف براند من النظام.

**Required Permission**: `delete_brand`

**Parameters:**
- `id` (path, required): معرف البراند المراد حذفه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Brand deleted successfully.",
    "data": {}
}
```

---

# إدارة التصنيفات (Categories)

## GET /admin/categories
استرجاع قائمة التصنيفات مع إمكانية البحث والفلترة.

**Required Permission**: `all_categories`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)
- `perPage` (query, optional): عدد السجلات في الصفحة (افتراضي: 15)
- `filter[search]` (query, optional): البحث في اسم أو وصف التصنيف
- `filter[status]` (query, optional): فلترة حسب الحالة (`0` = غير نشط، `1` = نشط)
- `page` (query, optional): رقم الصفحة (افتراضي: 1)

**Response Example:**
```json
{
    "success": true,
    "message": "Categories retrieved successfully.",
    "data": {
        "categories": [
            {
                "categoryId": 1,
                "name": "Electronics",
                "slug": "electronics",
                "description": "Electronic devices and accessories",
                "status": 1,
                "createdAt": "2023-01-01T00:00:00Z"
            }
        ],
        "pagination": {
            "total": 25,
            "count": 15,
            "perPage": 15,
            "currentPage": 1,
            "totalPages": 2
        }
    }
}
```

## POST /admin/categories
إنشاء تصنيف جديد.

**Required Permission**: `create_category`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (JSON):**
```json
{
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices and accessories",
    "status": 1
}
```

**Required Fields:**
- `name` (string, max: 255, unique): اسم التصنيف
- `slug` (string, max: 255, unique): رابط التصنيف
- `status` (integer, 0 or 1): حالة التصنيف

**Optional Fields:**
- `description` (string, nullable): وصف التصنيف

**Response Example:**
```json
{
    "success": true,
    "message": "Category created successfully.",
    "data": {}
}
```

## GET /admin/categories/{id}
استرجاع تفاصيل تصنيف محدد.

**Required Permission**: `edit_category`

**Parameters:**
- `id` (path, required): معرف التصنيف
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Category retrieved successfully.",
    "data": {
        "categoryId": 1,
        "name": "Electronics",
        "slug": "electronics",
        "description": "Electronic devices and accessories",
        "status": 1
    }
}
```

## PUT /admin/categories/{id}
تحديث معلومات تصنيف موجود.

**Required Permission**: `update_category`

**Parameters:**
- `id` (path, required): معرف التصنيف المراد تحديثه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (JSON):**
```json
{
    "name": "Electronics Updated",
    "slug": "electronics-updated",
    "description": "Updated electronic devices and accessories",
    "status": 1
}
```

**Required Fields:**
- `name` (string, max: 255, unique): اسم التصنيف
- `slug` (string, max: 255, unique): رابط التصنيف
- `status` (integer, 0 or 1): حالة التصنيف

**Optional Fields:**
- `description` (string, nullable): وصف التصنيف

**Response Example:**
```json
{
    "success": true,
    "message": "Category updated successfully.",
    "data": {}
}
```

## DELETE /admin/categories/{id}
حذف تصنيف من النظام.

**Required Permission**: `delete_category`

**Parameters:**
- `id` (path, required): معرف التصنيف المراد حذفه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "Category deleted successfully.",
    "data": {}
}
```

---

# حالات التصنيفات (Category Status)

| Value | Description |
|-------|-------------|
| `0` | غير نشط (Inactive) |
| `1` | نشط (Active) |

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
    "message": "Brand not found.",
    "data": {}
}
```
```json
{
    "success": false,
    "message": "Category not found.",
    "data": {}
}
```
**الأسباب**: البراند أو التصنيف المطلوب غير موجود في قاعدة البيانات

## 422 Validation Error - خطأ تحقق
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "slug": ["The slug has already been taken."]
    }
}
```
**الأسباب**: البيانات المرسلة لا تتوافق مع قواعد التحقق

## 500 Internal Server Error - خطأ خادم داخلي
```json
{
    "success": false,
    "message": "An error occurred while processing your request.",
    "data": {}
}
```
**الأسباب**: خطأ في قاعدة البيانات أو النظام

---

# قواعد التحقق (Validation Rules)

## البراندات (Brands)

### إنشاء/تحديث براند
- `name`: مطلوب، نص، حد أقصى 255 حرف، فريد

## التصنيفات (Categories)

### إنشاء تصنيف جديد
- `name`: مطلوب، نص، حد أقصى 255 حرف، فريد
- `slug`: مطلوب، نص، حد أقصى 255 حرف، فريد
- `status`: مطلوب، رقم صحيح (0 أو 1)
- `description`: اختياري، نص

### تحديث تصنيف
- `name`: مطلوب، نص، حد أقصى 255 حرف، فريد (باستثناء التصنيف الحالي)
- `slug`: مطلوب، نص، حد أقصى 255 حرف، فريد (باستثناء التصنيف الحالي)
- `status`: مطلوب، رقم صحيح (0 أو 1)
- `description`: اختياري، نص

---

# الصلاحيات المطلوبة (Required Permissions)

## البراندات (Brands)
| Endpoint | Permission | Description |
|----------|------------|-------------|
| GET /brands | `all_brands` | عرض قائمة البراندات |
| POST /brands | `create_brand` | إنشاء براند جديد |
| GET /brands/{id} | `edit_brand` | عرض تفاصيل براند |
| PUT /brands/{id} | `update_brand` | تحديث براند |
| DELETE /brands/{id} | `delete_brand` | حذف براند |

## التصنيفات (Categories)
| Endpoint | Permission | Description |
|----------|------------|-------------|
| GET /categories | `all_categories` | عرض قائمة التصنيفات |
| POST /categories | `create_category` | إنشاء تصنيف جديد |
| GET /categories/{id} | `edit_category` | عرض تفاصيل تصنيف |
| PUT /categories/{id} | `update_category` | تحديث تصنيف |
| DELETE /categories/{id} | `delete_category` | حذف تصنيف |

---

# أمثلة على الاستخدام

## البراندات (Brands)

### استرجاع البراندات مع بحث
```bash
curl -X GET "http://localhost:8000/api/v1/admin/brands?filter[search]=Nike&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### إنشاء براند جديد
```bash
curl -X POST "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Puma"}'
```

### تحديث براند
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/brands/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Nike Updated"}'
```

### حذف براند
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/brands/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## التصنيفات (Categories)

### استرجاع التصنيفات مع فلاتر
```bash
curl -X GET "http://localhost:8000/api/v1/admin/categories?filter[search]=Electronics&filter[status]=1&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### إنشاء تصنيف جديد
```bash
curl -X POST "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices and accessories",
    "status": 1
  }'
```

### تحديث تصنيف
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/categories/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics Updated",
    "slug": "electronics-updated",
    "description": "Updated electronic devices and accessories",
    "status": 1
  }'
```

### حذف تصنيف
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/categories/1" \
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
5. **الترقيم**: دعم pagination لقوائم البراندات والتصنيفات
6. **البحث والفلترة**: إمكانيات بحث وفلترة متقدمة للتصنيفات
7. **الفرادة**: أسماء البراندات وأسماء/روابط التصنيفات يجب أن تكون فريدة
8. **الحالة**: التصنيفات تدعم حالات نشط/غير نشط
