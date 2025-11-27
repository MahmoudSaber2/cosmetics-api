# أمثلة حالات الخطأ في User Management APIs

## نظرة عامة
هذا الملف يحتوي على أمثلة مفصلة لجميع حالات الخطأ المحتملة في User Management APIs مع أمثلة واقعية لكل حالة.

## حالات الخطأ الرئيسية

### 1. خطأ 401 - Unauthorized (غير مصرح)

#### الوصف
يحدث هذا الخطأ عندما:
- لا يتم إرسال Bearer token
- Bearer token غير صحيح أو منتهي الصلاحية
- Bearer token تم إلغاؤه

#### أمثلة الاستجابة

**مثال 1: عدم وجود token**
```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": {}
}
```

**مثال 2: token غير صحيح**
```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": {}
}
```

#### أمثلة cURL التي تسبب هذا الخطأ

```bash
# بدون Authorization header
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json"

# مع token غير صحيح
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer INVALID_TOKEN_HERE"

# مع token منتهي الصلاحية
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.EXPIRED_TOKEN"
```

### 2. خطأ 403 - Forbidden (محظور)

#### الوصف
يحدث هذا الخطأ عندما:
- المستخدم مصرح له بالدخول لكن ليس لديه الصلاحية المطلوبة
- محاولة حذف النفس
- محاولة الوصول لمورد محظور

#### أمثلة الاستجابة حسب العملية

**للعمليات العامة (عدم وجود صلاحية)**
```json
{
    "success": false,
    "message": "This action is unauthorized.",
    "data": {}
}
```

**لحذف النفس**
```json
{
    "success": false,
    "message": "You cannot delete yourself.",
    "data": {}
}
```

#### أمثلة cURL التي تسبب هذا الخطأ

```bash
# مستخدم بدون صلاحية all_users
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer VALID_TOKEN_WITHOUT_PERMISSION"

# مستخدم بدون صلاحية create_user
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer VALID_TOKEN_WITHOUT_CREATE_PERMISSION" \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "password=Password123" \
  -F "status=1" \
  -F "roleId=2"

# محاولة حذف النفس
curl -X DELETE "http://localhost:8000/api/v1/admin/users/5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_OF_USER_ID_5"
```

### 3. خطأ 500 - Internal Server Error (خطأ خادم داخلي)

#### الوصف
يحدث هذا الخطأ عندما:
- خطأ في قاعدة البيانات
- فشل في المعاملة (Transaction)
- خطأ في النظام أو الخادم
- مشكلة في الاتصال بقاعدة البيانات

#### أمثلة الاستجابة حسب العملية

**للعمليات العامة**
```json
{
    "success": false,
    "message": "An error occurred while processing your request.",
    "data": {}
}
```

**لإنشاء مستخدم**
```json
{
    "success": false,
    "message": "An error occurred while creating the user.",
    "data": {}
}
```

**لتحديث مستخدم**
```json
{
    "success": false,
    "message": "An error occurred while updating the user.",
    "data": {}
}
```

#### حالات تسبب هذا الخطأ

```bash
# عندما تكون قاعدة البيانات غير متاحة
# عندما يحدث خطأ في SQL
# عندما تفشل المعاملة (Transaction)
# عندما يحدث خطأ في النظام
```

## حالات خطأ إضافية

### 4. خطأ 404 - Not Found (غير موجود)

#### الوصف
يحدث عند محاولة الوصول لمستخدم غير موجود.

#### مثال الاستجابة
```json
{
    "success": false,
    "message": "User not found.",
    "data": {}
}
```

#### مثال cURL
```bash
curl -X GET "http://localhost:8000/api/v1/admin/users/99999" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. خطأ 422 - Validation Error (خطأ تحقق)

#### الوصف
يحدث عند إرسال بيانات غير صحيحة.

#### مثال الاستجابة
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

#### أمثلة cURL التي تسبب هذا الخطأ

```bash
# بريد إلكتروني مكرر
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Test User" \
  -F "email=existing@example.com" \
  -F "password=Password123" \
  -F "status=1" \
  -F "roleId=2"

# كلمة مرور ضعيفة
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "password=123" \
  -F "status=1" \
  -F "roleId=2"

# دور غير موجود
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "password=Password123" \
  -F "status=1" \
  -F "roleId=99999"
```

## أمثلة شاملة لكل Endpoint

### GET /api/v1/admin/users

#### حالات الخطأ المحتملة:
- **401**: عدم وجود أو صحة token
- **403**: عدم وجود صلاحية `all_users`
- **500**: خطأ في قاعدة البيانات

```bash
# 401 - بدون token
curl -X GET "http://localhost:8000/api/v1/admin/users"

# 403 - بدون صلاحية
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Authorization: Bearer TOKEN_WITHOUT_PERMISSION"

# 500 - يحدث عند مشاكل النظام (لا يمكن محاكاته بسهولة)
```

### POST /api/v1/admin/users

#### حالات الخطأ المحتملة:
- **401**: عدم وجود أو صحة token
- **403**: عدم وجود صلاحية `create_user`
- **422**: بيانات غير صحيحة
- **500**: فشل في إنشاء المستخدم

```bash
# 401 - بدون token
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -F "name=Test User"

# 403 - بدون صلاحية
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Authorization: Bearer TOKEN_WITHOUT_CREATE_PERMISSION" \
  -F "name=Test User"

# 422 - بيانات غير صحيحة
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=" \
  -F "email=invalid-email"

# 500 - يحدث عند فشل المعاملة
```

### GET /api/v1/admin/users/{id}

#### حالات الخطأ المحتملة:
- **401**: عدم وجود أو صحة token
- **403**: عدم وجود صلاحية `edit_user`
- **404**: مستخدم غير موجود

```bash
# 401 - بدون token
curl -X GET "http://localhost:8000/api/v1/admin/users/1"

# 403 - بدون صلاحية
curl -X GET "http://localhost:8000/api/v1/admin/users/1" \
  -H "Authorization: Bearer TOKEN_WITHOUT_EDIT_PERMISSION"

# 404 - مستخدم غير موجود
curl -X GET "http://localhost:8000/api/v1/admin/users/99999" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### PUT /api/v1/admin/users/{id}

#### حالات الخطأ المحتملة:
- **401**: عدم وجود أو صحة token
- **403**: عدم وجود صلاحية `update_user`
- **404**: مستخدم غير موجود
- **422**: بيانات غير صحيحة
- **500**: فشل في تحديث المستخدم

```bash
# 401 - بدون token
curl -X PUT "http://localhost:8000/api/v1/admin/users/1" \
  -F "name=Updated Name"

# 403 - بدون صلاحية
curl -X PUT "http://localhost:8000/api/v1/admin/users/1" \
  -H "Authorization: Bearer TOKEN_WITHOUT_UPDATE_PERMISSION" \
  -F "name=Updated Name"

# 404 - مستخدم غير موجود
curl -X PUT "http://localhost:8000/api/v1/admin/users/99999" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Updated Name"

# 422 - بيانات غير صحيحة
curl -X PUT "http://localhost:8000/api/v1/admin/users/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "email=invalid-email"

# 500 - يحدث عند فشل المعاملة
```

### DELETE /api/v1/admin/users/{id}

#### حالات الخطأ المحتملة:
- **401**: عدم وجود أو صحة token
- **403**: عدم وجود صلاحية `delete_user` أو محاولة حذف النفس
- **404**: مستخدم غير موجود

```bash
# 401 - بدون token
curl -X DELETE "http://localhost:8000/api/v1/admin/users/1"

# 403 - بدون صلاحية
curl -X DELETE "http://localhost:8000/api/v1/admin/users/1" \
  -H "Authorization: Bearer TOKEN_WITHOUT_DELETE_PERMISSION"

# 403 - محاولة حذف النفس
curl -X DELETE "http://localhost:8000/api/v1/admin/users/5" \
  -H "Authorization: Bearer TOKEN_OF_USER_ID_5"

# 404 - مستخدم غير موجود
curl -X DELETE "http://localhost:8000/api/v1/admin/users/99999" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## نصائح لمعالجة الأخطاء

### في Frontend Applications

```javascript
// مثال على معالجة الأخطاء في JavaScript
async function getUsers() {
    try {
        const response = await fetch('/api/v1/admin/users', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            switch (response.status) {
                case 401:
                    // إعادة توجيه لصفحة تسجيل الدخول
                    window.location.href = '/login';
                    break;
                case 403:
                    // عرض رسالة عدم وجود صلاحية
                    showError('ليس لديك صلاحية للوصول لهذه الصفحة');
                    break;
                case 500:
                    // عرض رسالة خطأ عام
                    showError('حدث خطأ في النظام، يرجى المحاولة لاحقاً');
                    break;
                default:
                    showError(data.message || 'حدث خطأ غير متوقع');
            }
            return;
        }
        
        // معالجة البيانات الناجحة
        displayUsers(data.data);
        
    } catch (error) {
        console.error('Network error:', error);
        showError('خطأ في الاتصال بالخادم');
    }
}
```

### في Mobile Applications

```dart
// مثال على معالجة الأخطاء في Flutter/Dart
Future<void> getUsers() async {
  try {
    final response = await http.get(
      Uri.parse('/api/v1/admin/users'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    
    final data = json.decode(response.body);
    
    switch (response.statusCode) {
      case 200:
        // معالجة البيانات الناجحة
        handleSuccessResponse(data);
        break;
      case 401:
        // إعادة توجيه لصفحة تسجيل الدخول
        Navigator.pushReplacementNamed(context, '/login');
        break;
      case 403:
        // عرض رسالة عدم وجود صلاحية
        showErrorDialog('ليس لديك صلاحية للوصول لهذه الصفحة');
        break;
      case 500:
        // عرض رسالة خطأ عام
        showErrorDialog('حدث خطأ في النظام، يرجى المحاولة لاحقاً');
        break;
      default:
        showErrorDialog(data['message'] ?? 'حدث خطأ غير متوقع');
    }
  } catch (error) {
    showErrorDialog('خطأ في الاتصال بالخادم');
  }
}
```

## الخلاصة

هذه الأمثلة تغطي جميع حالات الخطأ المحتملة في User Management APIs وتوفر:

1. **أوصاف واضحة** لكل نوع خطأ
2. **أمثلة واقعية** للاستجابات
3. **أمثلة cURL** لإثارة كل خطأ
4. **نصائح للمعالجة** في التطبيقات
5. **تغطية شاملة** لجميع الـ endpoints

استخدم هذه الأمثلة لفهم وتطوير معالجة أخطاء قوية في تطبيقاتك.
