# خوارزمية إدارة الصفحات الثابتة

## الهدف

إنشاء صفحات تعريفية ثابتة من لوحة التحكم، مثل صفحات معلومات أو سياسات أو محتوى تسويقي، ثم عرضها في الموقع عبر رابط `slug`.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/PageController.php`
- `app/Http/Requests/Admin/StorePageRequest.php`
- `app/Http/Requests/Admin/UpdatePageRequest.php`
- `app/Models/Page.php`
- `app/Http/Controllers/SiteController.php`

## خوارزمية القائمة والبحث

```mermaid
flowchart TD
    A["GET admin/pages"] --> B["قراءة q"]
    B --> C{"هل q فارغة؟"}
    C -- "لا" --> D["where title/slug/content like q"]
    C -- "نعم" --> E["بدون فلترة"]
    D --> F["latest + paginate(15)"]
    E --> F
    F --> G["عرض قائمة الصفحات"]
```

## خوارزمية إنشاء أو تحديث صفحة

```mermaid
flowchart TD
    A["submit صفحة"] --> B["Store/UpdatePageRequest"]
    B --> C{"هل الإدخال متعدد اللغات؟"}
    C -- "نعم" --> D["validate title.ar و content.ar/en"]
    C -- "لا" --> E["validate title و content"]
    D --> F["normalizeTranslatables"]
    E --> F
    F --> G["is_published من checkbox"]
    G --> H["template = null"]
    H --> I{"إنشاء أم تحديث؟"}
    I -- "إنشاء" --> J["Page::create"]
    I -- "تحديث" --> K["page->update"]
    J --> L["عودة نجاح"]
    K --> L
```

## خوارزمية العرض في الموقع

```mermaid
flowchart TD
    A["GET /pages/{slug} أو /{slug}"] --> B["SiteController::page"]
    B --> C["Page::published()->where(slug)"]
    C --> D{"هل الصفحة موجودة ومنشورة؟"}
    D -- "لا" --> E["404"]
    D -- "نعم" --> F["عرض site.page"]
```

## ملاحظات

- `HasUniqueSlug` يولد الرابط من العنوان.
- `published()` يمنع ظهور الصفحة إذا لم تكن منشورة.
- `template` مضبوط حاليا على `null` لأن المشروع يعتمد قالب عرض قياسي للصفحات.
