# خوارزمية إدارة صفحة من نحن

## الهدف

إعطاء الشركة صفحة تعريفية قابلة للتعديل من لوحة التحكم، تعرض عنوانا ونصوصا وصورة رئيسية وإحصاءات من بيانات النظام.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/SettingController.php`
- `app/Http/Requests/Admin/SaveAboutPageRequest.php`
- `app/Http/Controllers/SiteController.php`
- `resources/views/site/about.blade.php`

## خوارزمية عرض إعدادات من نحن

```mermaid
flowchart TD
    A["GET admin/settings/about"] --> B["جلب settings"]
    B --> C["mapWithKeys حسب key"]
    C --> D["parseValue لكل قيمة"]
    D --> E["عرض نموذج من نحن"]
```

## خوارزمية حفظ صفحة من نحن

```mermaid
flowchart TD
    A["POST admin/settings/about"] --> B["SaveAboutPageRequest validate"]
    B --> C["حفظ about_title"]
    C --> D["حفظ about_text_1"]
    D --> E["حفظ about_text_2"]
    E --> F{"هل توجد about_main_image؟"}
    F -- "لا" --> G["عودة نجاح"]
    F -- "نعم" --> H["جلب الصورة القديمة من settings"]
    H --> I{"هل توجد صورة قديمة؟"}
    I -- "نعم" --> J["حذفها من public disk"]
    I -- "لا" --> K["تخزين الصورة الجديدة"]
    J --> K
    K --> L["Setting::setValue about_main_image كنوع image"]
    L --> G
```

## خوارزمية عرض الصفحة للزائر

```mermaid
flowchart TD
    A["GET /about"] --> B["SiteController::about"]
    B --> C["siteSettings من settings"]
    C --> D["mainStats من قاعدة البيانات"]
    D --> E["عرض site.about"]
```

## الإحصاءات المعروضة

تستخدم الصفحة إحصاءات حقيقية من النظام:

- عدد المشاريع.
- عدد الخدمات.
- عدد سنوات الخبرة.
- عدد الوظائف النشطة.

## نتيجة العملية

تتحول صفحة `من نحن` من صفحة ثابتة إلى صفحة تعريفية مرنة تعكس بيانات الشركة وصورتها وإنجازاتها من داخل النظام.
