# دليل استخدام Product Media API

## نظرة عامة
تم تطوير نظام متقدم لإدارة عدة ملفات media (صور وفيديوهات) للمنتجات مع إمكانية تحديد صورة رئيسية.

## الميزات الجديدة
- رفع ملف media واحد للمنتج
- دعم الصور والفيديوهات
- تحديد صورة رئيسية للمنتج
- حذف ملفات media منفردة
- تغيير الصورة الرئيسية
- **جديد**: استخدام MediaTypeEnum لتصنيف أنواع الملفات
- **جديد**: إرجاع mediaTypeLabel مترجمة في الاستجابة
- **جديد**: تحسين validation باستخدام enum values

## API Endpoints

### 1. عرض جميع media للمنتج
```
GET /api/v1/admin/products/{product}/media
```

**Response:**
```json
{
    "success": true,
    "message": "Success",
    "data": [
        {
            "mediaId": 1,
            "url": "products/image1.jpg",
            "fullUrl": "https://example.com/storage/products/image1.jpg",
            "mediaType": "image",
            "isMain": 1,
            "createdAt": "01/12/24 02:30 م"
        },
        {
            "mediaId": 2,
            "url": "products/video1.mp4",
            "fullUrl": "https://example.com/storage/products/video1.mp4",
            "mediaType": "video",
            "isMain": 0,
            "createdAt": "01/12/24 02:35 م"
        }
    ]
}
```

### 2. رفع ملف media جديد
```
POST /api/v1/admin/products/{product}/media
Content-Type: multipart/form-data
```

**Parameters:**
- `media`: ملف واحد (5MB حد أقصى)
- `isMain` (اختياري): integer لتحديد إذا كان هذا الملف سيكون رئيسي (0=غير رئيسي، 1=رئيسي)

**Supported file types:**
- Images: jpg, jpeg, png, gif, webp, svg
- Videos: mp4, avi, mov, wmv, flv, webm

**Response:**
```json
{
    "success": true,
    "message": "تم رفع الملف بنجاح",
    "data": {
        "mediaId": 1,
        "url": "products/image1.jpg",
        "fullUrl": "https://example.com/storage/products/image1.jpg",
        "mediaType": "image",
        "isMain": 1,
        "createdAt": "01/12/24 02:30 م"
    }
}
```

### 3. حذف ملف media
```
DELETE /api/v1/admin/products/{product}/media/{media}
```

**Response:**
```json
{
    "success": true,
    "message": "تم حذف الملف بنجاح",
    "data": []
}
```

**ملاحظة:** لا يمكن حذف الصورة الرئيسية إذا كانت هناك صور أخرى موجودة.

### 4. تعيين ملف كصورة رئيسية
```
PUT /api/v1/admin/products/{product}/media/{media}/set-main
```

**Response:**
```json
{
    "success": true,
    "message": "تم تعيين الملف كصورة رئيسية",
    "data": {
        "mediaId": 1,
        "url": "products/image1.jpg",
        "isMain": true
    }
}
```

## التغييرات في Product API

### ProductResource المحدث
الآن يعرض جميع media files بدلاً من ملف واحد:

```json
{
    "productId": 1,
    "name": "iPhone 14",
    "media": [
        {
            "mediaId": 1,
            "url": "products/iphone-front.jpg",
            "fullUrl": "https://example.com/storage/products/iphone-front.jpg",
            "mediaType": "image",
            "isMain": 1
        },
        {
            "mediaId": 2,
            "url": "products/iphone-back.jpg",
            "fullUrl": "https://example.com/storage/products/iphone-back.jpg",
            "mediaType": "image",
            "isMain": 0
        }
    ]
}
```

## Database Changes

### Migration الجديدة
تم إضافة `is_main` column إلى `product_media` table:

```sql
ALTER TABLE product_media ADD COLUMN is_main BOOLEAN DEFAULT FALSE;
CREATE INDEX idx_product_media_main ON product_media(product_id, is_main);
```

### Product Model Updates
- `media()`: relation للحصول على جميع media files
- `mainMedia()`: relation للحصول على الصورة الرئيسية فقط

## أمثلة الاستخدام

### رفع صورة مع تحديدها كرئيسية
```javascript
const formData = new FormData();
formData.append('media', file);
formData.append('isMain', 1); // تحديد الملف كرئيسي (1=رئيسي، 0=غير رئيسي)

fetch('/api/v1/admin/products/1/media', {
    method: 'POST',
    body: formData,
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

### تغيير الصورة الرئيسية
```javascript
fetch('/api/v1/admin/products/1/media/3/set-main', {
    method: 'PUT',
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## قواعد العمل

1. **الصورة الرئيسية**: أول صورة يتم رفعها تصبح رئيسية تلقائياً
2. **حذف الصورة الرئيسية**: لا يمكن حذفها إذا كانت هناك صور أخرى
3. **تغيير الصورة الرئيسية**: يتم إلغاء الرئيسية من الصور الأخرى تلقائياً
4. **رفع الملفات**: ملف واحد في كل طلب
5. **حجم الملف**: حد أقصى 5MB لكل ملف

## رسائل الخطأ

- `يجب اختيار ملف`
- `يجب أن يكون الملف من نوع: jpg, jpeg, png, gif, webp, svg, mp4, avi, mov, wmv, flv, webm`
- `يجب أن يكون حجم الملف أقل من 5 ميجابايت`
- `يجب أن يكون isMain قيمة صحيحة (0 أو 1)`
- `لا يمكن حذف الصورة الرئيسية عندما توجد صور أخرى`
- `الملف غير موجود`

## الأمان والصلاحيات

جميع endpoints تتطلب:
- Authentication: `auth:sanctum`
- يمكن إضافة permissions حسب الحاجة (معلقة حالياً)

## الملفات المضافة/المحدثة

### ملفات جديدة:
- `app/Http/Controllers/Api/V1/Admin/ProductMediaController.php`
- `app/Http/Controllers/Api/V1/Admin/SetProductMediaAsMainController.php` - **جديد**: invokable controller
- `app/Http/Requests/V1/ProductMedia/StoreProductMediaRequest.php`
- `app/Http/Resources/V1/ProductMedia/ProductMediaResource.php`
- `app/Http/Resources/V1/ProductMedia/ProductMediaCollection.php`
- `app/Enums/MediaTypeEnum.php` - **جديد**: enum لأنواع الملفات
- `app/Enums/IsMainEnum.php` - **جديد**: enum للملف الرئيسي
- `database/migrations/2025_01_12_000000_add_is_main_to_product_media_table.php`

### ملفات محدثة:
- `app/Models/ProductMedia.php` - **IsMainEnum casting** + scopes محدثة
- `app/Models/Product.php` - تحديث media relations
- `app/Http/Resources/V1/Product/ProductResource.php` - عرض جميع media مع enum values
- `app/Http/Controllers/Api/V1/Admin/ProductController.php` - دعم multiple media في store
- `app/Services/FileUploadService.php` - **إضافة getMediaType method**
- `database/migrations/2025_11_18_165400_create_product_media_table.php` - **استخدام varchar**
- `routes/api.php` - إضافة routes جديدة + **invokable controller**
- `lang/ar/messages.php` - رسائل ترجمة جديدة
- `lang/en/messages.php` - رسائل ترجمة جديدة

## MediaTypeEnum

تم إنشاء enum جديد لتصنيف أنواع الملفات:

```php
enum MediaTypeEnum: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case FILE = 'file';
}
```

## IsMainEnum

تم إنشاء enum جديد لتحديد الملف الرئيسي:

```php
enum IsMainEnum: int
{
    case NOT_MAIN = 0;
    case MAIN = 1;
}
```

**الفوائد:**
- Type safety في الكود
- ترجمة تلقائية للأنواع
- سهولة إضافة أنواع جديدة
- تحسين validation
- وضوح في القيم (0/1 بدلاً من true/false)

النظام جاهز للاستخدام ويتبع نفس النمط المستخدم في المشروع.
