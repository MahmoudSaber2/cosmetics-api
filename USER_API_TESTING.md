# اختبار User Management APIs

## قائمة بجميع الـ Routes

### 1. استرجاع المستخدمين
```bash
GET /api/v1/admin/users
```

### 2. إنشاء مستخدم جديد
```bash
POST /api/v1/admin/users
```

### 3. عرض مستخدم محدد
```bash
GET /api/v1/admin/users/{id}
```

### 4. تحديث مستخدم
```bash
PUT /api/v1/admin/users/{id}
```

### 5. حذف مستخدم
```bash
DELETE /api/v1/admin/users/{id}
```

## أمثلة للاختبار باستخدام cURL

### 1. استرجاع قائمة المستخدمين
```bash
# استرجاع جميع المستخدمين
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# مع فلاتر البحث
curl -X GET "http://localhost:8000/api/v1/admin/users?filter[search]=john&filter[status]=1&perPage=10&page=1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة حسب الدور
curl -X GET "http://localhost:8000/api/v1/admin/users?filter[role]=2" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. إنشاء مستخدم جديد
```bash
# إنشاء مستخدم بدون صورة
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=أحمد محمد" \
  -F "email=ahmed@example.com" \
  -F "password=Password123" \
  -F "phone=01234567890" \
  -F "address=شارع النيل، القاهرة" \
  -F "status=1" \
  -F "roleId=2"

# إنشاء مستخدم مع صورة
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=فاطمة علي" \
  -F "email=fatima@example.com" \
  -F "password=SecurePass456" \
  -F "phone=01987654321" \
  -F "address=شارع الجامعة، الجيزة" \
  -F "status=1" \
  -F "roleId=3" \
  -F "avatar=@/path/to/avatar.jpg"
```

### 3. عرض مستخدم محدد
```bash
curl -X GET "http://localhost:8000/api/v1/admin/users/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. تحديث مستخدم
```bash
# تحديث بدون تغيير كلمة المرور
curl -X PUT "http://localhost:8000/api/v1/admin/users/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=أحمد محمد المحدث" \
  -F "email=ahmed.updated@example.com" \
  -F "phone=01111111111" \
  -F "address=شارع التحرير، القاهرة" \
  -F "status=1" \
  -F "roleId=3"

# تحديث مع تغيير كلمة المرور
curl -X PUT "http://localhost:8000/api/v1/admin/users/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=أحمد محمد" \
  -F "email=ahmed@example.com" \
  -F "password=NewPassword789" \
  -F "phone=01234567890" \
  -F "address=شارع النيل، القاهرة" \
  -F "status=0" \
  -F "roleId=2"
```

### 5. حذف مستخدم
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/users/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## اختبار مع اللغة الإنجليزية

### استرجاع المستخدمين بالإنجليزية
```bash
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### إنشاء مستخدم بالإنجليزية
```bash
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Smith" \
  -F "email=john.smith@example.com" \
  -F "password=Password123" \
  -F "phone=1234567890" \
  -F "address=123 Main Street, New York" \
  -F "status=1" \
  -F "roleId=2"
```

## بيانات تجريبية للاختبار

### إنشاء أدوار (Roles) - إذا لم تكن موجودة
```sql
INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('Super Admin', 'web', NOW(), NOW()),
('Admin', 'web', NOW(), NOW()),
('Manager', 'web', NOW(), NOW()),
('Employee', 'web', NOW(), NOW());
```

### إنشاء صلاحيات (Permissions) - إذا لم تكن موجودة
```sql
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('all_users', 'web', NOW(), NOW()),
('create_user', 'web', NOW(), NOW()),
('edit_user', 'web', NOW(), NOW()),
('update_user', 'web', NOW(), NOW()),
('delete_user', 'web', NOW(), NOW());
```

### ربط الصلاحيات بالأدوار
```sql
-- Super Admin gets all permissions
INSERT INTO role_has_permissions (permission_id, role_id) 
SELECT p.id, r.id FROM permissions p, roles r WHERE r.name = 'Super Admin';

-- Admin gets most permissions
INSERT INTO role_has_permissions (permission_id, role_id) 
SELECT p.id, r.id FROM permissions p, roles r 
WHERE r.name = 'Admin' AND p.name IN ('all_users', 'create_user', 'edit_user', 'update_user');
```

## سيناريوهات الاختبار

### 1. اختبار إنشاء مستخدم ناجح
- استخدم بيانات صحيحة
- تأكد من فرادة البريد الإلكتروني
- تحقق من قوة كلمة المرور
- تأكد من وجود الدور المحدد

### 2. اختبار أخطاء التحقق
```bash
# بريد إلكتروني مكرر
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Test User" \
  -F "email=existing@example.com" \
  -F "password=weak" \
  -F "status=1" \
  -F "roleId=999"

# كلمة مرور ضعيفة
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "password=123" \
  -F "status=1" \
  -F "roleId=2"
```

### 3. اختبار الصلاحيات
```bash
# محاولة الوصول بدون token
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json"

# محاولة الوصول بـ token غير صحيح
curl -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer INVALID_TOKEN"
```

### 4. اختبار حذف المستخدم
```bash
# محاولة حذف النفس (يجب أن تفشل)
curl -X DELETE "http://localhost:8000/api/v1/admin/users/[CURRENT_USER_ID]" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# حذف مستخدم آخر (يجب أن تنجح)
curl -X DELETE "http://localhost:8000/api/v1/admin/users/[OTHER_USER_ID]" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. اختبار البحث والفلترة
```bash
# البحث بالاسم
curl -X GET "http://localhost:8000/api/v1/admin/users?filter[search]=أحمد" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# البحث بالبريد الإلكتروني
curl -X GET "http://localhost:8000/api/v1/admin/users?filter[search]=ahmed@example.com" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة المستخدمين النشطين فقط
curl -X GET "http://localhost:8000/api/v1/admin/users?filter[status]=1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة حسب الدور
curl -X GET "http://localhost:8000/api/v1/admin/users?filter[role]=2" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## التحقق من النتائج

### بعد إنشاء مستخدم
```sql
-- تحقق من إنشاء المستخدم
SELECT * FROM users WHERE email = 'ahmed@example.com';

-- تحقق من ربط الدور
SELECT u.name, r.name as role_name 
FROM users u 
JOIN model_has_roles mhr ON u.id = mhr.model_id 
JOIN roles r ON mhr.role_id = r.id 
WHERE u.email = 'ahmed@example.com';
```

### بعد تحديث مستخدم
```sql
-- تحقق من تحديث البيانات
SELECT name, email, phone, address, status FROM users WHERE id = 1;

-- تحقق من تحديث الدور
SELECT u.name, r.name as role_name 
FROM users u 
JOIN model_has_roles mhr ON u.id = mhr.model_id 
JOIN roles r ON mhr.role_id = r.id 
WHERE u.id = 1;
```

### بعد حذف مستخدم
```sql
-- تحقق من حذف المستخدم
SELECT * FROM users WHERE id = 1;

-- تحقق من حذف الأدوار المرتبطة
SELECT * FROM model_has_roles WHERE model_id = 1;
```

## أخطاء شائعة وحلولها

### خطأ: "Unauthenticated"
- تأكد من إرسال Bearer token صحيح
- تحقق من صلاحية الـ token

### خطأ: "This action is unauthorized"
- تأكد من أن المستخدم لديه الصلاحية المطلوبة
- تحقق من ربط الصلاحيات بالأدوار

### خطأ: "This email address is already registered"
- استخدم بريد إلكتروني فريد
- أو استخدم endpoint التحديث بدلاً من الإنشاء

### خطأ: "The password must be at least 8 characters"
- استخدم كلمة مرور قوية (8 أحرف على الأقل مع أحرف وأرقام)

### خطأ: "You cannot delete yourself"
- لا يمكن للمستخدم حذف نفسه
- استخدم مستخدم آخر لحذف المستخدم المطلوب

## مثال على استجابة ناجحة

### إنشاء مستخدم
```json
{
    "success": true,
    "message": "تم إنشاء المستخدم بنجاح",
    "data": {}
}
```

### استرجاع المستخدمين
```json
{
    "success": true,
    "message": "تم استرجاع المستخدمين بنجاح",
    "data": {
        "users": [
            {
                "userId": 1,
                "name": "أحمد محمد",
                "email": "ahmed@example.com",
                "phone": "01234567890",
                "status": 1,
                "roleId": 2,
                "createdAt": "2023-01-01T00:00:00Z"
            }
        ],
        "pagination": {
            "total": 1,
            "count": 1,
            "perPage": 15,
            "currentPage": 1,
            "totalPages": 1
        }
    }
}
```

## ملاحظات للاختبار

1. **استخدم tokens صحيحة**: تأكد من الحصول على token من endpoint تسجيل الدخول
2. **تحقق من الصلاحيات**: تأكد من أن المستخدم لديه الصلاحيات المطلوبة
3. **اختبر اللغات**: اختبر مع `ar` و `en` في المسار والـ header
4. **اختبر رفع الملفات**: استخدم صور حقيقية لاختبار رفع الصور الشخصية
5. **اختبر حدود التحقق**: اختبر مع بيانات غير صحيحة للتأكد من عمل التحقق
6. **اختبر الترقيم**: اختبر مع قيم مختلفة لـ `perPage` و `page`
