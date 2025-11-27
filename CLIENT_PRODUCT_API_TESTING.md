# اختبار Client & Product Management APIs

## قائمة بجميع الـ Routes

### العملاء (Clients)
```bash
GET /api/v1/admin/clients
GET /api/v1/admin/clients/{id}
PUT /api/v1/admin/clients/{id}
DELETE /api/v1/admin/clients/{id}
```

### المنتجات (Products)
```bash
GET /api/v1/admin/products
POST /api/v1/admin/products
GET /api/v1/admin/products/{id}
PUT /api/v1/admin/products/{id}
DELETE /api/v1/admin/products/{id}
```

## أمثلة للاختبار باستخدام cURL

### العملاء (Clients)

#### 1. استرجاع قائمة العملاء
```bash
# استرجاع جميع العملاء
curl -X GET "http://localhost:8000/api/v1/admin/clients" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# مع بحث
curl -X GET "http://localhost:8000/api/v1/admin/clients?filter[search]=john&perPage=10&page=1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 2. عرض عميل محدد
```bash
curl -X GET "http://localhost:8000/api/v1/admin/clients/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 3. تحديث عميل
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

#### 4. حذف عميل
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/clients/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
### المنتجات (Products)

#### 1. استرجاع قائمة المنتجات
```bash
# استرجاع جميع المنتجات
curl -X GET "http://localhost:8000/api/v1/admin/products" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# مع فلاتر متقدمة
curl -X GET "http://localhost:8000/api/v1/admin/products?filter[search]=iPhone&filter[status]=active&filter[brand]=1&filter[category]=1&sort=-created_at&perPage=10" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة حسب نطاق السعر
curl -X GET "http://localhost:8000/api/v1/admin/products?filter[price]=100,1000&sort=price" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"

# فلترة حسب حالة المخزون
curl -X GET "http://localhost:8000/api/v1/admin/products?filter[stockStatus]=2" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 2. إنشاء منتج جديد
```bash
# منتج بدون صورة
curl -X POST "http://localhost:8000/api/v1/admin/products" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=iPhone 14" \
  -F "description=Latest iPhone model with advanced features" \
  -F "slug=iphone-14" \
  -F "price=999.99" \
  -F "cost=700.00" \
  -F "status=active" \
  -F "brandId=1" \
  -F "categoryId=1" \
  -F "hasStock=true" \
  -F "minStock=10"

# منتج مع صورة
curl -X POST "http://localhost:8000/api/v1/admin/products" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Samsung Galaxy S23" \
  -F "description=Latest Samsung flagship phone" \
  -F "slug=samsung-galaxy-s23" \
  -F "price=899.99" \
  -F "cost=600.00" \
  -F "status=active" \
  -F "brandId=2" \
  -F "categoryId=1" \
  -F "hasStock=true" \
  -F "minStock=15" \
  -F "media=@/path/to/samsung-s23.jpg"
```

#### 3. عرض منتج محدد
```bash
curl -X GET "http://localhost:8000/api/v1/admin/products/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
#### 4. تحديث منتج
```bash
# تحديث بدون تغيير الصورة
curl -X PUT "http://localhost:8000/api/v1/admin/products/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=iPhone 14 Pro" \
  -F "description=Updated iPhone model with pro features" \
  -F "slug=iphone-14-pro" \
  -F "price=1199.99" \
  -F "cost=800.00" \
  -F "status=active" \
  -F "brandId=1" \
  -F "categoryId=1" \
  -F "hasStock=true" \
  -F "minStock=15"

# تحديث مع تغيير الصورة
curl -X PUT "http://localhost:8000/api/v1/admin/products/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=iPhone 14 Pro Max" \
  -F "description=Premium iPhone model" \
  -F "slug=iphone-14-pro-max" \
  -F "price=1299.99" \
  -F "cost=900.00" \
  -F "status=active" \
  -F "brandId=1" \
  -F "categoryId=1" \
  -F "hasStock=true" \
  -F "minStock=20" \
  -F "media=@/path/to/new-image.jpg"
```

#### 5. حذف منتج
```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/products/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## اختبار مع اللغة الإنجليزية

### العملاء بالإنجليزية
```bash
curl -X GET "http://localhost:8000/api/v1/admin/clients" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN"

curl -X PUT "http://localhost:8000/api/v1/admin/clients/1" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "phone": "1234567890",
    "address": "789 Business Ave",
    "city": "Chicago"
  }'
```

### المنتجات بالإنجليزية
```bash
curl -X GET "http://localhost:8000/api/v1/admin/products" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN"

curl -X POST "http://localhost:8000/api/v1/admin/products" \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=MacBook Pro" \
  -F "description=Professional laptop for developers" \
  -F "slug=macbook-pro" \
  -F "price=2499.99" \
  -F "cost=1800.00" \
  -F "status=active" \
  -F "brandId=1" \
  -F "categoryId=2" \
  -F "hasStock=true" \
  -F "minStock=5"
```
