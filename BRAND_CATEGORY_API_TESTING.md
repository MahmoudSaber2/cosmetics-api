# اختبار Brand & Category Management APIs

## قائمة بجميع الـ Routes

### البراندات (Brands)
```bash
GET /api/v1/admin/brands
POST /api/v1/admin/brands
GET /api/v1/admin/brands/{id}
PUT /api/v1/admin/brands/{id}
DELETE /api/v1/admin/brands/{id}
```

### التصنيفات (Categories)
```bash
GET /api/v1/admin/categories
POST /api/v1/admin/categories
GET /api/v1/admin/categories/{id}
PUT /api/v1/admin/categories/{id}
DELETE /api/v1/admin/categories/{id}
```

## أمثلة للاختبار باستخدام cURL

### البراندات (Brands)

#### 1. استرجاع قائمة البراندات
```bash
# استرجاع جميع البراندات
curl -X GET "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# مع بحث
curl -X GET "http://localhost:8000/api/v1/admin/brands?filter[search]=Nike&perPage=10&page=1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 2. إنشاء براند جديد
```bash
curl -X POST "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Puma"}'

# مع براند آخر
curl -X POST "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Under Armour"}'
```

#### 3. عرض براند محدد
```bash
curl -X GET "http://localhost:8000/api/v1/admin/brands/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 4. تحديث براند
```bash
curl -X PUT "http://localhost:8000/api/v1/admin/brands/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Nike Updated"}'
```

#### 5. حذف براند
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/brands/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### التصنيفات (Categories)

#### 1. استرجاع قائمة التصنيفات
```bash
# استرجاع جميع التصنيفات
curl -X GET "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# مع بحث وفلترة
curl -X GET "http://localhost:8000/api/v1/admin/categories?filter[search]=Electronics&filter[status]=1&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة التصنيفات النشطة فقط
curl -X GET "http://localhost:8000/api/v1/admin/categories?filter[status]=1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 2. إنشاء تصنيف جديد
```bash
# تصنيف إلكترونيات
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

# تصنيف ملابس
curl -X POST "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Clothing",
    "slug": "clothing",
    "description": "Fashion and clothing items",
    "status": 1
  }'

# تصنيف بدون وصف
curl -X POST "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Books",
    "slug": "books",
    "status": 0
  }'
```

#### 3. عرض تصنيف محدد
```bash
curl -X GET "http://localhost:8000/api/v1/admin/categories/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 4. تحديث تصنيف
```bash
# تحديث كامل
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

# تغيير الحالة إلى غير نشط
curl -X PUT "http://localhost:8000/api/v1/admin/categories/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices and accessories",
    "status": 0
  }'
```

#### 5. حذف تصنيف
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/categories/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## اختبار مع اللغة الإنجليزية

### البراندات بالإنجليزية
```bash
curl -X GET "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN"

curl -X POST "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Reebok"}'
```

### التصنيفات بالإنجليزية
```bash
curl -X GET "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN"

curl -X POST "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Sports",
    "slug": "sports",
    "description": "Sports equipment and accessories",
    "status": 1
  }'
```

## بيانات تجريبية للاختبار

### إنشاء براندات تجريبية
```sql
INSERT INTO brands (name, created_at, updated_at) VALUES
('Nike', NOW(), NOW()),
('Adidas', NOW(), NOW()),
('Puma', NOW(), NOW()),
('Under Armour', NOW(), NOW()),
('Reebok', NOW(), NOW());
```

### إنشاء تصنيفات تجريبية
```sql
INSERT INTO categories (name, slug, description, status, created_at, updated_at) VALUES
('Electronics', 'electronics', 'Electronic devices and accessories', 1, NOW(), NOW()),
('Clothing', 'clothing', 'Fashion and clothing items', 1, NOW(), NOW()),
('Sports', 'sports', 'Sports equipment and accessories', 1, NOW(), NOW()),
('Books', 'books', 'Books and educational materials', 0, NOW(), NOW()),
('Home & Garden', 'home-garden', 'Home and garden products', 1, NOW(), NOW());
```

## سيناريوهات الاختبار

### 1. اختبار إنشاء ناجح

#### البراندات
```bash
# إنشاء براند جديد بنجاح
curl -X POST "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "New Brand"}'
```

#### التصنيفات
```bash
# إنشاء تصنيف جديد بنجاح
curl -X POST "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Category",
    "slug": "new-category",
    "description": "A new category for testing",
    "status": 1
  }'
```

### 2. اختبار أخطاء التحقق

#### براند بنفس الاسم (خطأ 422)
```bash
curl -X POST "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Nike"}'  # اسم موجود مسبقاً
```

#### تصنيف بدون بيانات مطلوبة (خطأ 422)
```bash
curl -X POST "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'  # بدون بيانات مطلوبة
```

#### تصنيف بـ slug مكرر (خطأ 422)
```bash
curl -X POST "http://localhost:8000/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Different Name",
    "slug": "electronics",  # slug موجود مسبقاً
    "status": 1
  }'
```

### 3. اختبار الصلاحيات

#### بدون token (خطأ 401)
```bash
curl -X GET "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json"
```

#### بـ token غير صحيح (خطأ 401)
```bash
curl -X GET "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer INVALID_TOKEN"
```

#### بدون صلاحية (خطأ 403)
```bash
curl -X GET "http://localhost:8000/api/v1/admin/brands" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_WITHOUT_PERMISSION"
```

### 4. اختبار عدم وجود العنصر (خطأ 404)

#### براند غير موجود
```bash
curl -X GET "http://localhost:8000/api/v1/admin/brands/99999" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### تصنيف غير موجود
```bash
curl -X GET "http://localhost:8000/api/v1/admin/categories/99999" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. اختبار البحث والفلترة

#### بحث في البراندات
```bash
# البحث عن "Nike"
curl -X GET "http://localhost:8000/api/v1/admin/brands?filter[search]=Nike" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# البحث عن جزء من الاسم
curl -X GET "http://localhost:8000/api/v1/admin/brands?filter[search]=Ad" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### بحث وفلترة في التصنيفات
```bash
# البحث في الاسم
curl -X GET "http://localhost:8000/api/v1/admin/categories?filter[search]=Electronics" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة التصنيفات النشطة
curl -X GET "http://localhost:8000/api/v1/admin/categories?filter[status]=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة التصنيفات غير النشطة
curl -X GET "http://localhost:8000/api/v1/admin/categories?filter[status]=0" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# بحث مع فلترة
curl -X GET "http://localhost:8000/api/v1/admin/categories?filter[search]=Sport&filter[status]=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## التحقق من النتائج

### بعد إنشاء براند
```sql
-- تحقق من إنشاء البراند
SELECT * FROM brands WHERE name = 'Puma';
```

### بعد إنشاء تصنيف
```sql
-- تحقق من إنشاء التصنيف
SELECT * FROM categories WHERE slug = 'electronics';
```

### بعد تحديث
```sql
-- تحقق من تحديث البراند
SELECT * FROM brands WHERE id = 1;

-- تحقق من تحديث التصنيف
SELECT * FROM categories WHERE id = 1;
```

### بعد حذف
```sql
-- تحقق من حذف البراند
SELECT * FROM brands WHERE id = 1;

-- تحقق من حذف التصنيف
SELECT * FROM categories WHERE id = 1;
```

## أخطاء شائعة وحلولها

### خطأ: "Unauthenticated"
- تأكد من إرسال Bearer token صحيح
- تحقق من صلاحية الـ token

### خطأ: "This action is unauthorized"
- تأكد من أن المستخدم لديه الصلاحية المطلوبة
- تحقق من ربط الصلاحيات بالأدوار

### خطأ: "The name has already been taken"
- استخدم اسم براند فريد
- أو استخدم endpoint التحديث بدلاً من الإنشاء

### خطأ: "The slug has already been taken"
- استخدم slug فريد للتصنيف
- تأكد من عدم وجود تصنيف بنفس الـ slug

### خطأ: "Brand/Category not found"
- تحقق من صحة معرف البراند/التصنيف
- تأكد من وجود العنصر في قاعدة البيانات

## مثال على استجابة ناجحة

### إنشاء براند
```json
{
    "success": true,
    "message": "تم إنشاء البراند بنجاح",
    "data": {}
}
```

### استرجاع التصنيفات
```json
{
    "success": true,
    "message": "تم استرجاع التصنيفات بنجاح",
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
3. **اختبر اللغات**: اختبر مع `ar` و `en` في الـ header
4. **اختبر حدود التحقق**: اختبر مع بيانات غير صحيحة للتأكد من عمل التحقق
5. **اختبر الترقيم**: اختبر مع قيم مختلفة لـ `perPage` و `page`
6. **اختبر الفرادة**: تأكد من أن الأسماء والـ slugs فريدة
7. **اختبر الحالات**: اختبر تغيير حالة التصنيفات بين نشط وغير نشط
