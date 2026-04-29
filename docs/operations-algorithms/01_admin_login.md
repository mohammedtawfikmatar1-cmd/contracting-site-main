# خوارزمية تسجيل الدخول للوحة التحكم

## الهدف

ضمان أن لوحة التحكم لا تستخدم إلا من مسؤول مصرح له، مع دعم إعداد أول حساب إداري عند تشغيل المشروع لأول مرة.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/AuthController.php`
- `routes/web.php`
- `app/Models/User.php`

## الحالات الأساسية

1. إذا كان المسؤول مسجل الدخول بالفعل، يوجه إلى لوحة القيادة.
2. إذا لم يوجد أي مستخدم في جدول `users`، يوجه إلى شاشة الإعداد الأولي.
3. إذا وجد مستخدمون، تظهر شاشة تسجيل الدخول.

## خوارزمية عرض شاشة الدخول

```mermaid
flowchart TD
    A["طلب /admin/login"] --> B{"Auth::check؟"}
    B -- "نعم" --> C["تحويل إلى admin.dashboard"]
    B -- "لا" --> D{"هل يوجد مستخدمون؟"}
    D -- "لا" --> E["تحويل إلى /admin/setup"]
    D -- "نعم" --> F["عرض شاشة login"]
```

## خوارزمية إعداد أول مشرف

```mermaid
flowchart TD
    A["POST /admin/setup"] --> B{"هل يوجد مستخدم مسبقا؟"}
    B -- "نعم" --> C["تحويل إلى login"]
    B -- "لا" --> D["التحقق من name/email/password"]
    D --> E["is_super_admin = true"]
    E --> F["إنشاء User"]
    F --> G["Auth::login"]
    G --> H["session regenerate"]
    H --> I["تحويل إلى dashboard"]
```

## خوارزمية تسجيل الدخول

```mermaid
flowchart TD
    A["POST /admin/login"] --> B{"هل النظام بلا مستخدمين؟"}
    B -- "نعم" --> C["تحويل إلى setup"]
    B -- "لا" --> D["validate email/password"]
    D --> E["قراءة remember"]
    E --> F{"Auth::attempt ناجح؟"}
    F -- "لا" --> G["العودة مع خطأ بيانات الدخول"]
    F -- "نعم" --> H["تجديد الجلسة"]
    H --> I["الدخول إلى لوحة القيادة"]
```

## خوارزمية الخروج

```mermaid
flowchart TD
    A["POST /admin/logout"] --> B["Auth::logout"]
    B --> C["session invalidate"]
    C --> D["regenerateToken"]
    D --> E["تحويل إلى admin.login"]
```

## نتيجة العملية

بعد نجاح الدخول يستطيع المستخدم الوصول إلى مسارات الإدارة المحمية، ومن خلالها إدارة الهوية الرقمية والمحتوى والطلبات.
