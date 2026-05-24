# أمثلة اختبار CategoryController V2

## 🧪 اختبارات API

### 1. إنشاء فئة مع صورة

#### Postman/Insomnia
```
POST http://localhost/api/v2/admin/categories
Authorization: Bearer {your_token}
Content-Type: multipart/form-data

Body (form-data):
- name: "إلكترونيات"
- slug: "electronics"
- description: "أجهزة إلكترونية ومعدات تقنية"
- status: 1
- image: [اختر ملف صورة .jpg/.png]
```

#### cURL
```bash
curl -X POST "http://localhost/api/v2/admin/categories" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "name=إلكترونيات" \
  -F "slug=electronics" \
  -F "description=أجهزة إلكترونية ومعدات تقنية" \
  -F "status=1" \
  -F "image=@/path/to/your/image.jpg"
```

#### الاستجابة المتوقعة
```json
{
    "success": true,
    "message": "تم الإنشاء بنجاح",
    "data": {}
}
```

### 2. جلب قائمة الفئات مع الصور

#### Request
```
GET http://localhost/api/v2/admin/categories
Authorization: Bearer {your_token}
Accept: application/json
```

#### الاستجابة المتوقعة
```json
{
    "success": true,
    "message": "تم جلب البيانات بنجاح",
    "data": {
        "categories": [
            {
                "categoryId": 1,
                "name": "إلكترونيات",
                "slug": "electronics",
                "status": 1,
                "image": "http://localhost/storage/categories/xyz123.jpg"
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

### 3. جلب فئة محددة

#### Request
```
GET http://localhost/api/v2/admin/categories/1
Authorization: Bearer {your_token}
Accept: application/json
```

#### الاستجابة المتوقعة
```json
{
    "success": true,
    "message": "تم جلب البيانات بنجاح",
    "data": {
        "categoryId": 1,
        "name": "إلكترونيات",
        "slug": "electronics",
        "description": "أجهزة إلكترونية ومعدات تقنية",
        "status": 1,
        "image": "http://localhost/storage/categories/xyz123.jpg",
        "created_at": "2025-12-17T08:00:00.000000Z",
        "updated_at": "2025-12-17T08:00:00.000000Z"
    }
}
```

### 4. تحديث فئة مع صورة جديدة

#### Postman/Insomnia
```
POST http://localhost/api/v2/admin/categories/1?_method=PUT
Authorization: Bearer {your_token}
Content-Type: multipart/form-data

Body (form-data):
- name: "إلكترونيات محدثة"
- slug: "electronics-updated"
- description: "أجهزة إلكترونية ومعدات تقنية محدثة"
- status: 1
- image: [اختر ملف صورة جديد] (اختياري)
```

#### cURL
```bash
curl -X POST "http://localhost/api/v2/admin/categories/1?_method=PUT" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "name=إلكترونيات محدثة" \
  -F "slug=electronics-updated" \
  -F "description=أجهزة إلكترونية ومعدات تقنية محدثة" \
  -F "status=1" \
  -F "image=@/path/to/new/image.jpg"
```

### 5. حذف فئة (مع حذف الصورة)

#### Request
```
DELETE http://localhost/api/v2/admin/categories/1
Authorization: Bearer {your_token}
Accept: application/json
```

#### الاستجابة المتوقعة
```json
{
    "success": true,
    "message": "تم الحذف بنجاح",
    "data": {}
}
```

## 🔍 اختبارات التحقق من الصحة

### 1. اختبار الحقول المطلوبة

#### Request (بدون name)
```json
POST /api/v2/admin/categories
{
    "slug": "test",
    "status": 1
}
```

#### الاستجابة المتوقعة
```json
{
    "success": false,
    "message": "",
    "errors": {
        "name": ["اسم الفئة مطلوب"]
    }
}
```

### 2. اختبار تفرد الاسم

#### Request (اسم موجود)
```json
POST /api/v2/admin/categories
{
    "name": "إلكترونيات",
    "slug": "electronics-2",
    "status": 1
}
```

#### الاستجابة المتوقعة
```json
{
    "success": false,
    "message": "",
    "errors": {
        "name": ["اسم الفئة موجود بالفعل"]
    }
}
```

### 3. اختبار نوع الصورة

#### Request (ملف غير صورة)
```
POST /api/v2/admin/categories
Content-Type: multipart/form-data

Body:
- name: "فئة تجريبية"
- slug: "test-category"
- status: 1
- image: [ملف .txt أو .pdf]
```

#### الاستجابة المتوقعة
```json
{
    "success": false,
    "message": "",
    "errors": {
        "image": ["يجب أن يكون الملف صورة"]
    }
}
```

### 4. اختبار حجم الصورة

#### Request (صورة أكبر من 2MB)
```
POST /api/v2/admin/categories
Content-Type: multipart/form-data

Body:
- name: "فئة تجريبية"
- slug: "test-category"
- status: 1
- image: [صورة حجمها أكبر من 2MB]
```

#### الاستجابة المتوقعة
```json
{
    "success": false,
    "message": "",
    "errors": {
        "image": ["يجب أن يكون حجم الصورة أقل من 2 ميجابايت"]
    }
}
```

## 🛠️ اختبارات إدارة الملفات

### 1. التحقق من حفظ الصورة
بعد إنشاء فئة مع صورة، تحقق من:
- وجود الصورة في `storage/app/public/categories/`
- إمكانية الوصول للصورة عبر الرابط المعاد

### 2. التحقق من حذف الصورة القديمة
عند تحديث فئة بصورة جديدة:
- الصورة القديمة يجب أن تُحذف من التخزين
- الصورة الجديدة يجب أن تُحفظ
- الرابط يجب أن يُحدث في قاعدة البيانات

### 3. التحقق من حذف الصورة عند حذف الفئة
عند حذف فئة:
- الصورة يجب أن تُحذف من التخزين
- السجل يجب أن يُحذف من قاعدة البيانات

## 📱 اختبار Frontend Integration

### JavaScript/Axios Example
```javascript
// إنشاء فئة مع صورة
const createCategory = async (formData) => {
    try {
        const response = await axios.post('/api/v2/admin/categories', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${token}`
            }
        });
        console.log('Category created:', response.data);
    } catch (error) {
        console.error('Error:', error.response.data);
    }
};

// استخدام
const formData = new FormData();
formData.append('name', 'إلكترونيات');
formData.append('slug', 'electronics');
formData.append('description', 'أجهزة إلكترونية');
formData.append('status', '1');
formData.append('image', fileInput.files[0]);

createCategory(formData);
```

### React Example
```jsx
const CategoryForm = () => {
    const [formData, setFormData] = useState({
        name: '',
        slug: '',
        description: '',
        status: 1,
        image: null
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        const data = new FormData();
        Object.keys(formData).forEach(key => {
            if (formData[key] !== null) {
                data.append(key, formData[key]);
            }
        });

        try {
            const response = await fetch('/api/v2/admin/categories', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: data
            });
            
            const result = await response.json();
            console.log('Success:', result);
        } catch (error) {
            console.error('Error:', error);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <input 
                type="text" 
                placeholder="اسم الفئة"
                value={formData.name}
                onChange={(e) => setFormData({...formData, name: e.target.value})}
            />
            <input 
                type="file" 
                accept="image/*"
                onChange={(e) => setFormData({...formData, image: e.target.files[0]})}
            />
            <button type="submit">إنشاء فئة</button>
        </form>
    );
};
```

## 🔧 نصائح للاختبار

### 1. إعداد التخزين
تأكد من أن مجلد التخزين قابل للكتابة:
```bash
php artisan storage:link
chmod -R 775 storage/
```

### 2. اختبار الصلاحيات
تأكد من أن المستخدم لديه الصلاحيات المطلوبة:
- `create_category` للإنشاء
- `update_category` للتحديث
- `delete_category` للحذف
- `all_categories` لعرض القائمة
- `edit_category` لعرض فئة محددة

### 3. اختبار أحجام الملفات المختلفة
- صور صغيرة (< 100KB)
- صور متوسطة (500KB - 1MB)
- صور كبيرة (1.5MB - 2MB)
- صور كبيرة جداً (> 2MB) - يجب أن ترفض

### 4. اختبار أنواع الملفات
- JPEG ✅
- PNG ✅
- JPG ✅
- GIF ✅
- SVG ✅
- PDF ❌
- TXT ❌
- DOC ❌

## 🎯 معايير النجاح

### ✅ الاختبارات يجب أن تمر
1. إنشاء فئة بدون صورة
2. إنشاء فئة مع صورة
3. تحديث فئة بدون تغيير الصورة
4. تحديث فئة مع صورة جديدة
5. حذف فئة مع حذف الصورة
6. رفض الملفات غير الصور
7. رفض الصور الكبيرة جداً
8. عرض الصور بروابط صحيحة
9. رسائل خطأ عربية واضحة
10. التحقق من الصلاحيات

النظام جاهز للاختبار والاستخدام! 🚀
