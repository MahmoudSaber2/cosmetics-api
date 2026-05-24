# دليل مسارات API - إصدار v1 و v2

## نظرة عامة

تم تنظيم API في إصدارين:
- **v1**: الوظائف الأساسية للإدارة والموقع
- **v2**: جميع وظائف v1 + ميزات متقدمة (نظام الدفع)

## مسارات v1 (الأساسية)

### الإدارة - `/api/v1/admin/`

#### المصادقة
- `POST /api/v1/admin/auth/login` - تسجيل الدخول
- `POST /api/v1/admin/auth/logout` - تسجيل الخروج

#### إدارة المستخدمين
- `GET /api/v1/admin/users` - قائمة المستخدمين
- `POST /api/v1/admin/users` - إضافة مستخدم
- `GET /api/v1/admin/users/{user}` - عرض مستخدم
- `PUT /api/v1/admin/users/{user}` - تحديث مستخدم
- `DELETE /api/v1/admin/users/{user}` - حذف مستخدم

#### إدارة العلامات التجارية
- `GET /api/v1/admin/brands` - قائمة العلامات
- `POST /api/v1/admin/brands` - إضافة علامة
- `GET /api/v1/admin/brands/{brand}` - عرض علامة
- `PUT /api/v1/admin/brands/{brand}` - تحديث علامة
- `DELETE /api/v1/admin/brands/{brand}` - حذف علامة
- `PUT /api/v1/admin/brands/{id}/restore` - استعادة علامة محذوفة
- `DELETE /api/v1/admin/brands/{id}/force-delete` - حذف نهائي

#### إدارة الفئات
- `GET /api/v1/admin/categories` - قائمة الفئات
- `POST /api/v1/admin/categories` - إضافة فئة
- `GET /api/v1/admin/categories/{category}` - عرض فئة
- `PUT /api/v1/admin/categories/{category}` - تحديث فئة
- `DELETE /api/v1/admin/categories/{category}` - حذف فئة

#### إدارة المنتجات
- `GET /api/v1/admin/products` - قائمة المنتجات
- `POST /api/v1/admin/products` - إضافة منتج
- `GET /api/v1/admin/products/{product}` - عرض منتج
- `PUT /api/v1/admin/products/{product}` - تحديث منتج
- `DELETE /api/v1/admin/products/{product}` - حذف منتج

#### إدارة وسائط المنتجات
- `GET /api/v1/admin/products/{product}/media` - قائمة وسائط المنتج
- `POST /api/v1/admin/products/{product}/media` - إضافة وسائط
- `DELETE /api/v1/admin/products/{product}/media/{media}` - حذف وسائط
- `PUT /api/v1/admin/products/{product}/media/{media}/set-main` - تعيين كصورة رئيسية

#### إدارة العملاء
- `GET /api/v1/admin/clients` - قائمة العملاء
- `POST /api/v1/admin/clients` - إضافة عميل
- `GET /api/v1/admin/clients/{client}` - عرض عميل
- `PUT /api/v1/admin/clients/{client}` - تحديث عميل
- `DELETE /api/v1/admin/clients/{client}` - حذف عميل

#### إدارة الطلبات
- `GET /api/v1/admin/orders` - قائمة الطلبات
- `POST /api/v1/admin/orders` - إضافة طلب
- `GET /api/v1/admin/orders/{order}` - عرض طلب
- `PUT /api/v1/admin/orders/{order}` - تحديث طلب
- `DELETE /api/v1/admin/orders/{order}` - حذف طلب
- `PUT /api/v1/admin/orders/{order}/approve` - الموافقة على طلب
- `PUT /api/v1/admin/orders/{order}/reject` - رفض طلب
- `PUT /api/v1/admin/orders/{order}/complete` - إكمال طلب
- `GET /api/v1/admin/orders-statistics` - إحصائيات الطلبات
- `GET /api/v1/admin/orders-status-counts` - عدد الطلبات حسب الحالة

#### لوحة التحكم
- `GET /api/v1/admin/dashboard` - بيانات لوحة التحكم

### الموقع - `/api/v1/website/`

- `GET /api/v1/website/home` - الصفحة الرئيسية
- `GET /api/v1/website/products` - قائمة المنتجات
- `GET /api/v1/website/products/{product:slug}` - عرض منتج
- `GET /api/v1/website/products/{product:slug}/related` - المنتجات المشابهة
- `POST /api/v1/website/orders` - إنشاء طلب
- `POST /api/v1/website/orders/validate-cart` - التحقق من السلة

### المساعدة
- `GET /api/v1/selects` - البيانات المساعدة

## مسارات v2 (المتقدمة)

### جميع مسارات v1 متاحة في v2
كل المسارات المذكورة أعلاه متاحة في v2 بنفس الطريقة، فقط استبدل `v1` بـ `v2` في URL.

### الميزات الإضافية في v2

#### نظام الدفع - `/api/v2/website/`
- `POST /api/v2/website/orders/{order}/payment/create-intent` - إنشاء نية دفع
- `POST /api/v2/website/payments/{payment}/confirm` - تأكيد الدفع
- `GET /api/v2/website/payments/{payment}/status` - حالة الدفع
- `POST /api/v2/website/payments/webhook/stripe` - webhook من Stripe

## أمثلة الاستخدام

### إنشاء طلب مع دفع (v2)
```bash
# 1. إنشاء الطلب
POST /api/v2/website/orders
{
    "client_id": 1,
    "items": [...]
}

# 2. إنشاء نية الدفع
POST /api/v2/website/orders/1/payment/create-intent
{
    "currency": "usd",
    "payment_method": "stripe"
}

# 3. تأكيد الدفع (بعد معالجة العميل)
POST /api/v2/website/payments/1/confirm
{
    "payment_intent_id": "pi_xxx"
}

# 4. التحقق من حالة الدفع
GET /api/v2/website/payments/1/status
```

### الترقية من v1 إلى v2
- استبدل `/api/v1/` بـ `/api/v2/` في جميع المسارات
- أضف مسارات الدفع الجديدة حسب الحاجة
- جميع الوظائف الموجودة ستعمل بنفس الطريقة

## ملاحظات مهمة

1. **التوافق**: v2 متوافق بالكامل مع v1
2. **الأمان**: جميع مسارات الإدارة تتطلب مصادقة
3. **التوثيق**: استخدم OpenAPI/Swagger للتوثيق التفاعلي
4. **الأخطاء**: جميع الاستجابات تتبع نفس تنسيق الأخطاء
5. **المعدل**: لا توجد قيود على معدل الطلبات حالياً

## الدعم والمساعدة

للحصول على مساعدة إضافية:
- راجع ملف `PAYMENT_SYSTEM_COMPLETION.md` لتفاصيل نظام الدفع
- استخدم `php artisan route:list` لعرض جميع المسارات
- تحقق من متحكمات v2 في `app/Http/Controllers/Api/V2/`
