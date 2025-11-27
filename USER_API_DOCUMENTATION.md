# User Management API Documentation - V1

## نظرة عامة
هذه الوثيقة تحتوي على جميع APIs الخاصة بإدارة المستخدمين في لوحة التحكم.

## Base URL
```
/api/v1/admin/users
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

## إدارة المستخدمين (Users)

### GET /admin/users
استرجاع قائمة المستخدمين مع إمكانية الفلترة والبحث.

**Required Permission**: `all_users`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)
- `perPage` (query, optional): عدد السجلات في الصفحة (افتراضي: 15)
- `filter[search]` (query, optional): البحث في الاسم، البريد الإلكتروني، أو الهاتف
- `filter[status]` (query, optional): فلترة حسب الحالة (`0` = غير نشط، `1` = نشط)
- `filter[role]` (query, optional): فلترة حسب معرف الدور
- `page` (query, optional): رقم الصفحة (افتراضي: 1)

**Response Example:**
```json
{
    "success": true,
    "message": "Users retrieved successfully.",
    "data": {
        "users": [
            {
                "userId": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "1234567890",
                "status": 1,
                "roleId": 2,
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

### POST /admin/users
إنشاء مستخدم جديد.

**Required Permission**: `create_user`

**Parameters:**
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (multipart/form-data):**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "address": "123 Main St",
    "status": 1,
    "password": "Password123",
    "roleId": 2,
    "avatar": "file (optional)"
}
```

**Required Fields:**
- `name` (string, max: 255)
- `email` (string, email, unique, max: 255)
- `password` (string, min: 8, must contain letters and numbers)
- `status` (integer, 0 or 1)
- `roleId` (integer, existing role ID)

**Optional Fields:**
- `phone` (string, max: 20, phone format)
- `address` (string, max: 500)
- `avatar` (file, image formats: jpeg,jpg,png,gif,svg,webp, max: 5MB)

**Response Example:**
```json
{
    "success": true,
    "message": "User created successfully.",
    "data": {}
}
```

### GET /admin/users/{id}
استرجاع تفاصيل مستخدم محدد.

**Required Permission**: `edit_user`

**Parameters:**
- `id` (path, required): معرف المستخدم
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "User retrieved successfully.",
    "data": {
        "userId": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "address": "123 Main St",
        "status": 1,
        "roleId": 2
    }
}
```

### PUT /admin/users/{id}
تحديث معلومات مستخدم موجود.

**Required Permission**: `update_user`

**Parameters:**
- `id` (path, required): معرف المستخدم المراد تحديثه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Request Body (multipart/form-data):**
```json
{
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "0987654321",
    "address": "456 Updated St",
    "status": 1,
    "password": "NewPassword123",
    "roleId": 3,
    "avatar": "file (optional)"
}
```

**Required Fields:**
- `name` (string, max: 255)
- `email` (string, email, unique except current user, max: 255)
- `status` (integer, 0 or 1)
- `roleId` (integer, existing role ID)

**Optional Fields:**
- `phone` (string, max: 20, phone format)
- `address` (string, max: 500)
- `password` (string, min: 8, leave empty to keep current password)
- `avatar` (file, image formats: jpeg,jpg,png,gif,svg,webp, max: 5MB)

**Response Example:**
```json
{
    "success": true,
    "message": "User updated successfully.",
    "data": {}
}
```

### DELETE /admin/users/{id}
حذف مستخدم من النظام.

**Required Permission**: `delete_user`

**Parameters:**
- `id` (path, required): معرف المستخدم المراد حذفه
- `Accept-Language` (header, optional): تفضيل اللغة (`ar` أو `en`)

**Response Example:**
```json
{
    "success": true,
    "message": "User deleted successfully.",
    "data": {}
}
```

## حالات المستخدمين (User Status)

| Value | Description |
|-------|-------------|
| `0` | غير نشط (Inactive) |
| `1` | نشط (Active) |

## رسائل الخطأ الشائعة

### 401 Unauthorized - غير مصرح
```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": {}
}
```
**الأسباب**: عدم وجود Bearer token، token غير صحيح، أو token منتهي الصلاحية

### 403 Forbidden - محظور
```json
{
    "success": false,
    "message": "This action is unauthorized.",
    "data": {}
}
```
**الأسباب**: عدم وجود الصلاحية المطلوبة للعملية

```json
{
    "success": false,
    "message": "You cannot delete yourself.",
    "data": {}
}
```
**الأسباب**: محاولة المستخدم حذف نفسه

### 404 Not Found - غير موجود
```json
{
    "success": false,
    "message": "User not found.",
    "data": {}
}
```
**الأسباب**: المستخدم المطلوب غير موجود في قاعدة البيانات

### 422 Validation Error - خطأ تحقق
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": ["This email address is already registered."],
        "password": ["The password must be at least 8 characters."],
        "roleId": ["The selected role is invalid."]
    }
}
```
**الأسباب**: البيانات المرسلة لا تتوافق مع قواعد التحقق

### 500 Internal Server Error - خطأ خادم داخلي
```json
{
    "success": false,
    "message": "An error occurred while processing your request.",
    "data": {}
}
```
**الأسباب**: خطأ في قاعدة البيانات، فشل المعاملة، أو خطأ في النظام

> **📋 للمزيد من الأمثلة المفصلة**: راجع ملف `USER_API_ERROR_EXAMPLES.md` للحصول على أمثلة شاملة لجميع حالات الخطأ مع أمثلة cURL ونصائح المعالجة.

## قواعد التحقق (Validation Rules)

### إنشاء مستخدم جديد (Store)
- `name`: مطلوب، نص، حد أقصى 255 حرف
- `email`: مطلوب، بريد إلكتروني صحيح، فريد، حد أقصى 255 حرف
- `password`: مطلوب، حد أدنى 8 أحرف، يجب أن يحتوي على أحرف وأرقام
- `phone`: اختياري، نص، تنسيق هاتف صحيح، حد أقصى 20 حرف
- `address`: اختياري، نص، حد أقصى 500 حرف
- `status`: مطلوب، رقم صحيح (0 أو 1)
- `roleId`: مطلوب، رقم صحيح، يجب أن يكون دور موجود
- `avatar`: اختياري، ملف صورة، أنواع مدعومة: jpeg,jpg,png,gif,svg,webp، حد أقصى 5MB

### تحديث مستخدم (Update)
- `name`: مطلوب، نص، حد أقصى 255 حرف
- `email`: مطلوب، بريد إلكتروني صحيح، فريد (باستثناء المستخدم الحالي)، حد أقصى 255 حرف
- `password`: اختياري، حد أدنى 8 أحرف (اتركه فارغاً للاحتفاظ بكلمة المرور الحالية)
- `phone`: اختياري، نص، تنسيق هاتف صحيح، حد أقصى 20 حرف
- `address`: اختياري، نص، حد أقصى 500 حرف
- `status`: مطلوب، رقم صحيح (0 أو 1)
- `roleId`: مطلوب، رقم صحيح، يجب أن يكون دور موجود
- `avatar`: اختياري، ملف صورة، أنواع مدعومة: jpeg,jpg,png,gif,svg,webp، حد أقصى 5MB

## الصلاحيات المطلوبة (Required Permissions)

| Endpoint | Permission | Description |
|----------|------------|-------------|
| GET /users | `all_users` | عرض قائمة المستخدمين |
| POST /users | `create_user` | إنشاء مستخدم جديد |
| GET /users/{id} | `edit_user` | عرض تفاصيل مستخدم |
| PUT /users/{id} | `update_user` | تحديث مستخدم |
| DELETE /users/{id} | `delete_user` | حذف مستخدم |

## أمثلة على الاستخدام

### استرجاع المستخدمين مع فلترة
```bash
curl -X GET "http://localhost:8000/api/v1/admin/users?filter[search]=john&filter[status]=1&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### إنشاء مستخدم جديد
```bash
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Doe" \
  -F "email=john@example.com" \
  -F "password=Password123" \
  -F "phone=1234567890" \
  -F "address=123 Main St" \
  -F "status=1" \
  -F "roleId=2" \
  -F "avatar=@/path/to/image.jpg"
```

### تحديث مستخدم
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/users/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Doe Updated" \
  -F "email=john.updated@example.com" \
  -F "phone=0987654321" \
  -F "status=1" \
  -F "roleId=3"
```

### حذف مستخدم
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/users/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## ملاحظات مهمة

1. **الأمان**: جميع APIs محمية بـ Sanctum authentication
2. **الصلاحيات**: كل endpoint يتطلب صلاحية محددة
3. **منع الحذف الذاتي**: المستخدمون لا يمكنهم حذف أنفسهم
4. **تعدد اللغات**: دعم كامل للعربية والإنجليزية
5. **رفع الملفات**: دعم رفع صور الملف الشخصي
6. **التحقق**: تحقق شامل من البيانات مع رسائل خطأ واضحة
7. **الترقيم**: دعم pagination لقوائم المستخدمين
8. **البحث والفلترة**: إمكانيات بحث وفلترة متقدمة
