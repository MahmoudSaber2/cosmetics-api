# دليل استخدام API مع اللغات المتعددة

## كيفية استخدام API مع اللغات

### 1. تحديد اللغة في الطلبات

يمكنك تحديد اللغة بثلاث طرق:

#### أ) عبر Header

```javascript
fetch('/api/products', {
  headers: {
    'Accept-Language': 'ar', // أو 'en'
    'Content-Type': 'application/json'
  }
})
```

#### ب) عبر Query Parameter

```javascript
fetch('/api/products?lang=ar')
// أو
fetch('/api/products?locale=ar')
```

#### ج) في JavaScript/Frontend

```javascript
// تحديد اللغة في كل الطلبات
axios.defaults.headers.common['Accept-Language'] = 'ar';

// أو في طلب محدد
const response = await axios.get('/api/products', {
  headers: { 'Accept-Language': 'ar' }
});
```

### 2. API Endpoints للغات

#### الحصول على اللغات المتاحة

```
GET /api/locale
```

Response:

```json
{
  "success": true,
  "data": {
    "available_locales": ["ar", "en"],
    "current_locale": "ar",
    "locales": {
      "ar": {
        "code": "ar",
        "name": "العربية",
        "direction": "rtl",
        "flag": "🇸🇦"
      },
      "en": {
        "code": "en",
        "name": "English",
        "direction": "ltr",
        "flag": "🇺🇸"
      }
    }
  }
}
```

#### الحصول على الترجمات

```
GET /api/locale/translations?file=messages
GET /api/locale/translations?key=messages.welcome
```

#### الحصول على رسائل التحقق

```
GET /api/locale/validation
```

#### الحصول على رسائل المصادقة

```
GET /api/locale/auth
```

### 3. استجابات API المترجمة

جميع استجابات API تحتوي على:

```json
{
  "success": true,
  "message": "تم بنجاح", // مترجمة حسب اللغة المحددة
  "data": {...},
  "locale": "ar",
  "direction": "rtl"
}
```

### 4. أمثلة عملية

#### React/Next.js

```javascript
// إعداد اللغة
const setApiLanguage = (locale) => {
  axios.defaults.headers.common['Accept-Language'] = locale;
  localStorage.setItem('locale', locale);
};

// استخدام في component
const ProductList = () => {
  const [products, setProducts] = useState([]);
  const [locale, setLocale] = useState('ar');
  
  useEffect(() => {
    setApiLanguage(locale);
    fetchProducts();
  }, [locale]);
  
  const fetchProducts = async () => {
    const response = await axios.get('/api/public/products');
    setProducts(response.data.data);
  };
  
  return (
    <div dir={locale === 'ar' ? 'rtl' : 'ltr'}>
      <select onChange={(e) => setLocale(e.target.value)}>
        <option value="ar">العربية</option>
        <option value="en">English</option>
      </select>
      {/* عرض المنتجات */}
    </div>
  );
};
```

#### Vue.js

```javascript
// في Vuex store
const store = new Vuex.Store({
  state: {
    locale: 'ar'
  },
  mutations: {
    SET_LOCALE(state, locale) {
      state.locale = locale;
      axios.defaults.headers.common['Accept-Language'] = locale;
    }
  }
});

// في component
export default {
  computed: {
    isRTL() {
      return this.$store.state.locale === 'ar';
    }
  },
  methods: {
    async fetchData() {
      const response = await this.$http.get('/api/public/products');
      return response.data;
    }
  }
}
```

### 5. معالجة الأخطاء المترجمة

```javascript
try {
  const response = await axios.post('/api/public/orders', orderData);
} catch (error) {
  if (error.response?.data) {
    const { message, errors, locale, direction } = error.response.data;
    
    // عرض الرسالة المترجمة
    showError(message);
    
    // معالجة أخطاء التحقق
    if (errors) {
      Object.keys(errors).forEach(field => {
        showFieldError(field, errors[field][0]);
      });
    }
  }
}
```

### 6. نصائح للتطوير

1. **احفظ اللغة المختارة**: استخدم localStorage أو cookies
2. **اتجاه النص**: استخدم خاصية `direction` في الاستجابة
3. **التحقق من صحة البيانات**: رسائل الخطأ ستكون مترجمة تلقائياً
4. **التاريخ والوقت**: استخدم مكتبات مثل moment.js مع اللغة المحددة

### 7. اللغات المدعومة حالياً

- العربية (ar) - RTL
- الإنجليزية (en) - LTR

يمكن إضافة لغات جديدة بسهولة عبر إضافة ملفات ترجمة في مجلد `lang/`.
