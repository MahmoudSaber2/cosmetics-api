# تحديثات API الباك اند - إرجاع بيانات الأسعار الكاملة

## ✅ التعديلات المنفذة

### Public ProductController

**الملف**: `cosmetics-api/app/Http/Controllers/Api/Public/ProductController.php`

---

## 📊 البيانات المُرجعة من الـ API

### قبل التحديث

```json
{
  "id": 1,
  "name": "Product Name",
  "price": 80.00  // السعر النهائي فقط
}
```

### بعد التحديث

```json
{
  "id": 1,
  "name": "Product Name",
  "selling_price": 100.00,        // سعر البيع الأصلي
  "purchase_price": 70.00,        // سعر الشراء
  "discount_type": "percentage",  // نوع الخصم
  "discount_value": 20,           // قيمة الخصم
  "discount_start_date": "2025-01-01",
  "discount_end_date": "2025-12-31",
  "price": 80.00,                 // السعر النهائي بعد الخصم
  "image_url": "...",
  "thumbnail_url": "..."
}
```

---

## 🔄 الدوال المحدثة

### 1. `index()` - قائمة المنتجات

```php
$transformedProducts = $products->getCollection()->map(function ($product) {
    return [
        'selling_price' => $product->selling_price,
        'purchase_price' => $product->purchase_price,
        'discount_type' => $product->discount_type,
        'discount_value' => $product->discount_value,
        'discount_start_date' => $product->discount_start_date,
        'discount_end_date' => $product->discount_end_date,
        'price' => $product->getFinalPrice(), // السعر النهائي
        // ... باقي الحقول
    ];
});
```

### 2. `show($id)` - تفاصيل منتج واحد

```php
$transformedProduct = [
    'selling_price' => $product->selling_price,
    'purchase_price' => $product->purchase_price,
    'discount_type' => $product->discount_type,
    'discount_value' => $product->discount_value,
    'discount_start_date' => $product->discount_start_date,
    'discount_end_date' => $product->discount_end_date,
    'price' => $product->getFinalPrice(),
    // ... باقي الحقول
];
```

### 3. `search()` - البحث عن منتجات

```php
$transformedProducts = $products->map(function ($product) {
    return [
        'selling_price' => $product->selling_price,
        'discount_type' => $product->discount_type,
        'discount_value' => $product->discount_value,
        'discount_start_date' => $product->discount_start_date,
        'discount_end_date' => $product->discount_end_date,
        'price' => $product->getFinalPrice(),
        // ... باقي الحقول
    ];
});
```

### 4. `featured()` - المنتجات المميزة

```php
$transformedProducts = $products->map(function ($product) {
    return [
        'selling_price' => $product->selling_price,
        'discount_type' => $product->discount_type,
        'discount_value' => $product->discount_value,
        'discount_start_date' => $product->discount_start_date,
        'discount_end_date' => $product->discount_end_date,
        'price' => $product->getFinalPrice(),
        // ... باقي الحقول
    ];
});
```

---

## 🎯 الفوائد

### 1. الشفافية الكاملة

- الفرونت اند يحصل على كل البيانات
- يمكن عرض السعر الأصلي والنهائي
- يمكن عرض تفاصيل الخصم

### 2. المرونة

- الفرونت اند يمكنه استخدام `price` مباشرة (السعر النهائي)
- أو حساب السعر النهائي محلياً من `selling_price` و `discount_*`
- أو عرض كلا السعرين

### 3. الأداء

- الحساب يتم مرة واحدة في الباك اند
- `getFinalPrice()` يستخدم الدوال الموجودة في Model
- لا حاجة لحسابات معقدة في الفرونت اند

---

## 📝 ملاحظات مهمة

### استخدام `getFinalPrice()`

```php
'price' => $product->getFinalPrice()
```

هذه الدالة موجودة في `Product` Model وتحسب:

- السعر النهائي بعد تطبيق الخصم
- تتحقق من نوع الخصم (نسبة أو مبلغ ثابت)
- تتأكد أن السعر لا يكون سالب

### الحقول الإضافية

```php
'thumbnail_url' => $product->thumbnail_url,
'stock_quantity' => $product->getStockQuantity(),
```

تم إضافة حقول إضافية مفيدة للفرونت اند

---

## 🔍 أمثلة الاستخدام

### مثال 1 - منتج بخصم 20%

```json
{
  "selling_price": 100.00,
  "discount_type": "percentage",
  "discount_value": 20,
  "price": 80.00
}
```

### مثال 2 - منتج بخصم $15

```json
{
  "selling_price": 100.00,
  "discount_type": "fixed",
  "discount_value": 15,
  "price": 85.00
}
```

### مثال 3 - منتج بدون خصم

```json
{
  "selling_price": 100.00,
  "discount_type": null,
  "discount_value": null,
  "price": 100.00
}
```

---

## 🎨 استخدام في الفرونت اند

### الطريقة الموصى بها

```typescript
// استخدام price مباشرة (السعر النهائي من الباك اند)
const finalPrice = product.price;

// عرض السعر الأصلي إذا كان هناك خصم
if (product.discount_type && product.discount_value) {
  const originalPrice = product.selling_price;
  // عرض originalPrice مشطوب
  // عرض finalPrice بالأخضر
}
```

### الطريقة البديلة (حساب محلي)

```typescript
import { calculateFinalPrice } from '@/lib/price-utils';

const finalPrice = calculateFinalPrice(product);
```

---

## ✨ الملخص

### ما تم إنجازه

1. ✅ تحديث `index()` - قائمة المنتجات
2. ✅ تحديث `show()` - تفاصيل منتج
3. ✅ تحديث `search()` - البحث
4. ✅ تحديث `featured()` - المنتجات المميزة
5. ✅ إضافة جميع حقول الأسعار والخصومات
6. ✅ استخدام `getFinalPrice()` للسعر النهائي
7. ✅ إضافة `thumbnail_url` و `stock_quantity`

### النتيجة

- الـ API الآن يرجع بيانات كاملة وشاملة
- الفرونت اند لديه كل المعلومات اللازمة
- يمكن عرض الأسعار بشكل صحيح ومفصل
- تجربة مستخدم أفضل مع شفافية كاملة
