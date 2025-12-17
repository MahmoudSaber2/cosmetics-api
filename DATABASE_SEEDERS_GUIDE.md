# دليل قواعد البيانات والـ Seeders

## نظرة عامة

تم تنظيم الـ seeders لدعم إصدارين من API مع بيانات مختلفة لكل إصدار:

- **V1 Seeders**: البيانات الأساسية للوظائف الأساسية
- **V2 Seeders**: جميع بيانات V1 + بيانات نظام الدفع المتقدم

## الـ Seeders المتاحة

### الـ Seeders الأساسية (مشتركة)
- `RolePermissionSeeder` - الأدوار والصلاحيات
- `AdminUserSeeder` - المستخدمين الإداريين
- `BrandSeeder` - العلامات التجارية
- `CategorySeeder` - فئات المنتجات
- `ProductSeeder` - المنتجات
- `ClientSeeder` - العملاء الأساسيين
- `OrderSeeder` - الطلبات الأساسية

### الـ Seeders المتقدمة (V2)
- `PaymentSeeder` - سجلات الدفع مع Stripe
- `V2ClientSeeder` - عملاء مع تفضيلات الدفع
- `V2OrderSeeder` - طلبات مع تكامل الدفع

### الـ Seeders الرئيسية
- `DatabaseSeeder` - الافتراضي (يشغل V1)
- `V1DatabaseSeeder` - بيانات API v1
- `V2DatabaseSeeder` - بيانات API v2 (شاملة)

## كيفية الاستخدام

### 1. تشغيل الـ Seeding الافتراضي
```bash
php artisan db:seed
```
يشغل V1DatabaseSeeder بشكل افتراضي للتوافق العكسي.

### 2. تشغيل V1 Seeding (البيانات الأساسية)
```bash
php artisan db:seed --class=V1DatabaseSeeder
```

**يتضمن:**
- الأدوار والصلاحيات
- المستخدمين الإداريين
- كتالوج المنتجات (علامات، فئات، منتجات)
- إعدادات النظام الأساسية

### 3. تشغيل V2 Seeding (البيانات المتقدمة)
```bash
php artisan db:seed --class=V2DatabaseSeeder
```

**يتضمن:**
- جميع بيانات V1
- سجلات دفع تجريبية
- عملاء مع تفضيلات الدفع
- طلبات مع تكامل الدفع
- إعدادات نظام الدفع

### 4. تشغيل Seeders محددة

#### بيانات الدفع فقط
```bash
php artisan db:seed --class=PaymentSeeder
```

#### عملاء V2 فقط
```bash
php artisan db:seed --class=V2ClientSeeder
```

#### طلبات V2 فقط
```bash
php artisan db:seed --class=V2OrderSeeder
```

## البيانات التجريبية

### V1 Data
- **المستخدمين**: مدير واحد مع صلاحيات كاملة
- **المنتجات**: مجموعة من منتجات التجميل
- **العلامات التجارية**: علامات تجارية مختلفة
- **الفئات**: فئات منتجات متنوعة

### V2 Additional Data
- **العملاء**: 5 عملاء مع تفضيلات دفع مختلفة
- **الطلبات**: 5 طلبات بحالات دفع مختلفة
- **المدفوعات**: سجلات دفع بحالات مختلفة (نجح، فشل، معلق)

## حالات الدفع التجريبية

### حالات الطلبات
- ✅ **مكتمل مع دفع ناجح**
- ⏳ **معلق في انتظار الدفع**
- 🔄 **قيد المعالجة مع دفع مؤكد**
- ❌ **ملغي مع فشل الدفع**

### حالات المدفوعات
- `succeeded` - دفع ناجح
- `pending` - في انتظار المعالجة
- `failed` - فشل الدفع
- `processing` - قيد المعالجة

## بيانات الاختبار

### بيانات Stripe التجريبية
- **Payment Intent IDs**: `pi_xxxxxxxxxxxxxxxxxxxxxxxx`
- **Client Secrets**: `pi_xxx_secret_xxx`
- **Transaction IDs**: `txn_xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`

### بيانات البطاقات التجريبية
- **أرقام البطاقات**: أرقام وهمية للاختبار
- **العلامات التجارية**: Visa, Mastercard, Amex
- **تواريخ الانتهاء**: 2025-2030

## إعادة تعيين قاعدة البيانات

### إعادة تعيين كاملة مع V1
```bash
php artisan migrate:fresh --seed --class=V1DatabaseSeeder
```

### إعادة تعيين كاملة مع V2
```bash
php artisan migrate:fresh --seed --class=V2DatabaseSeeder
```

### إعادة تعيين مع الـ Seeder الافتراضي
```bash
php artisan migrate:fresh --seed
```

## نصائح للتطوير

### 1. اختبار API v1
```bash
# إعداد بيانات أساسية
php artisan migrate:fresh --seed --class=V1DatabaseSeeder
```

### 2. اختبار API v2 مع الدفع
```bash
# إعداد بيانات كاملة مع نظام الدفع
php artisan migrate:fresh --seed --class=V2DatabaseSeeder
```

### 3. اختبار نظام الدفع فقط
```bash
# إضافة بيانات دفع لقاعدة بيانات موجودة
php artisan db:seed --class=PaymentSeeder
```

### 4. التحقق من البيانات
```bash
# عرض إحصائيات قاعدة البيانات
php artisan tinker
>>> App\Models\User::count()
>>> App\Models\Product::count()
>>> App\Models\Order::count()
>>> App\Models\Payment::count()
```

## الملفات ذات الصلة

- `database/seeders/` - جميع ملفات الـ Seeders
- `database/migrations/` - هيكل قاعدة البيانات
- `app/Models/` - نماذج البيانات
- `app/Enums/` - تعدادات الحالات

## استكشاف الأخطاء

### خطأ: "Class not found"
```bash
composer dump-autoload
```

### خطأ: "Table doesn't exist"
```bash
php artisan migrate
```

### خطأ: "Foreign key constraint"
تأكد من تشغيل الـ Seeders بالترتيب الصحيح (V1 قبل V2).

## الدعم والمساعدة

للحصول على مساعدة إضافية:
- راجع ملف `PAYMENT_SYSTEM_COMPLETION.md` لتفاصيل نظام الدفع
- راجع ملف `API_ROUTES_GUIDE.md` لتفاصيل المسارات
- استخدم `php artisan db:seed --help` للخيارات المتاحة
