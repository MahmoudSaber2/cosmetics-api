# دليل حل مشاكل Sanctum Authentication

## ✅ المشاكل التي تم حلها

### 1. إضافة Sanctum Guard
تم إضافة guard للـ sanctum في `config/auth.php`:
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

### 2. حل تضارب أسماء المسارات
تم إضافة أسماء مختلفة لمسارات V2 لتجنب التضارب مع V1:
```php
Route::apiResource('users', V2UserController::class)->names('v2.users');
Route::apiResource('brands', V2BrandController::class)->names('v2.brands');
// ... إلخ
```

### 3. مسح Cache
تم مسح cache المسارات والإعدادات:
```bash
php artisan route:clear
php artisan config:clear
```

## 🧪 اختبار المصادقة

### 1. تسجيل الدخول
```bash
curl -X POST "http://localhost/api/v2/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

### 2. استخدام Token
```bash
curl -X GET "http://localhost/api/v2/admin/users" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 🔍 التحقق من الإعدادات

### 1. التأكد من User Model
```php
// في app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    // ...
}
```

### 2. التأكد من Sanctum في composer.json
```json
{
    "require": {
        "laravel/sanctum": "^4.2"
    }
}
```

### 3. التأكد من Middleware
Controllers تستخدم:
```php
new Middleware('auth:sanctum')
```

## 🚨 مشاكل محتملة وحلولها

### 1. "Unauthenticated" رغم وجود Token
**الأسباب المحتملة:**
- Token غير صحيح أو منتهي الصلاحية
- Header Authorization غير صحيح
- Guard غير مُعرف في config/auth.php

**الحل:**
```bash
# تأكد من format الـ header
Authorization: Bearer 1|your-token-here

# وليس
Authorization: your-token-here
```

### 2. Route Name Conflicts
**المشكلة:**
```
Unable to prepare route [api/v2/admin/users] for serialization. 
Another route has already been assigned name [users.index].
```

**الحل:**
إضافة أسماء مختلفة للمسارات:
```php
Route::apiResource('users', V2UserController::class)->names('v2.users');
```

### 3. CORS Issues
إذا كنت تستخدم frontend منفصل، تأكد من إعدادات CORS في `config/cors.php`.

### 4. Token Expiration
تحقق من إعدادات انتهاء الصلاحية في `config/sanctum.php`:
```php
'expiration' => null, // لا انتهاء صلاحية
// أو
'expiration' => 1440, // 24 ساعة بالدقائق
```

## 🔧 أوامر مفيدة للتشخيص

### 1. فحص المسارات
```bash
php artisan route:list | findstr "v2"
```

### 2. فحص الإعدادات
```bash
php artisan config:show auth.guards
php artisan config:show sanctum
```

### 3. مسح Cache
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 4. إنشاء Token يدوياً للاختبار
```bash
php artisan tinker
>>> $user = App\Models\User::first()
>>> $token = $user->createToken('test-token')->plainTextToken
>>> echo $token
```

## 📝 نصائح للتطوير

### 1. استخدام Postman
- أضف Authorization header
- اختر Bearer Token
- الصق token بدون "Bearer" prefix

### 2. Frontend Integration
```javascript
// في JavaScript
const token = localStorage.getItem('auth_token');
const headers = {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
};
```

### 3. Laravel Debugging
```php
// في Controller للتشخيص
dd(auth('sanctum')->user()); // يجب أن يعرض المستخدم
dd(request()->bearerToken()); // يجب أن يعرض الـ token
```

## ✅ التحقق النهائي

إذا كان كل شيء يعمل بشكل صحيح، يجب أن تحصل على:

1. **تسجيل دخول ناجح** مع token
2. **وصول للمسارات المحمية** باستخدام token
3. **عدم وجود أخطاء** في route caching
4. **استجابات صحيحة** من API endpoints

النظام الآن جاهز للاستخدام! 🚀
