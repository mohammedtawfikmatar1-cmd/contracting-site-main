# خوارزمية إدارة الأخبار

## الهدف

إدارة الأخبار اليدوية من لوحة التحكم، مع دعم الأخبار التلقائية التي تنشأ من الخدمات والمشاريع والمناقصات والوظائف.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/NewsController.php`
- `app/Http/Requests/Admin/StoreNewsRequest.php`
- `app/Http/Requests/Admin/UpdateNewsRequest.php`
- `app/Models/News.php`
- `app/Services/NewsAutomationService.php`

## خوارزمية الأخبار اليدوية

```mermaid
flowchart TD
    A["submit خبر من لوحة التحكم"] --> B["Store/UpdateNewsRequest validate"]
    B --> C["normalizeTranslatables title/content/category"]
    C --> D["is_published من checkbox"]
    D --> E["published_at من الطلب"]
    E --> F{"هل توجد image؟"}
    F -- "نعم" --> G["رفع الصورة وحذف القديمة عند التحديث"]
    F -- "لا" --> H["متابعة بدون صورة جديدة"]
    G --> I["newsable_type = null و newsable_id = null"]
    H --> I
    I --> J{"إنشاء أم تحديث؟"}
    J -- "إنشاء" --> K["News::create"]
    J -- "تحديث" --> L["news->update"]
    K --> M["عودة نجاح"]
    L --> M
```

## خوارزمية ضبط تاريخ النشر

داخل `News::saving`:

```mermaid
flowchart TD
    A["حفظ خبر"] --> B{"is_published = true؟"}
    B -- "لا" --> C["لا تغيير"]
    B -- "نعم" --> D{"published_at فارغ؟"}
    D -- "نعم" --> E["published_at = now()"]
    D -- "لا" --> C
    E --> F["متابعة الحفظ"]
```

## خوارزمية الأخبار التلقائية

```mermaid
flowchart TD
    A["حفظ خدمة/مشروع/مناقصة/وظيفة"] --> B["إطلاق Event"]
    B --> C["Listener يستدعي NewsAutomationService"]
    C --> D["البحث عن خبر بنفس newsable_type/newsable_id"]
    D --> E{"هل العنصر منشور أو نشط؟"}
    E -- "لا" --> F["حذف الخبر المرتبط"]
    E -- "نعم" --> G["firstOrNew"]
    G --> H["تعبئة title/content/category/url/image"]
    H --> I["save"]
```

## خوارزمية عرض الأخبار للزوار

```mermaid
flowchart TD
    A["GET /news"] --> B["News::published"]
    B --> C["is_published = true"]
    C --> D["published_at <= now"]
    D --> E["latest(published_at) + paginate"]
    E --> F["عرض site.news"]
```

## النتيجة

الأخبار اليدوية والتلقائية تتجمع في جدول واحد، وهذا يعطي الشركة صفحة أخبار ومحتوى متجدد دون إدخال مزدوج.
