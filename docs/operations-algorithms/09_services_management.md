# خوارزمية إدارة الخدمات

## الهدف

إدارة خدمات شركة المقاولات وعرضها في الموقع، مع ربطها بالمشاريع وإنشاء خبر تلقائي عند النشر.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/ServiceController.php`
- `app/Http/Requests/Admin/StoreServiceRequest.php`
- `app/Http/Requests/Admin/UpdateServiceRequest.php`
- `app/Models/Service.php`
- `app/Services/NewsAutomationService.php`

## خوارزمية القائمة والبحث

```mermaid
flowchart TD
    A["GET admin/services"] --> B["قراءة q"]
    B --> C{"q غير فارغة؟"}
    C -- "نعم" --> D["where title/slug/description like q"]
    C -- "لا" --> E["كل الخدمات"]
    D --> F["latest + paginate(15)"]
    E --> F
    F --> G["عرض القائمة"]
```

## خوارزمية إنشاء خدمة

```mermaid
flowchart TD
    A["POST admin/services"] --> B["StoreServiceRequest validate"]
    B --> C["كشف إدخال متعدد اللغات أو عربي فقط"]
    C --> D["normalize title/overview/description"]
    D --> E["is_published من checkbox"]
    E --> F{"هل توجد image؟"}
    F -- "نعم" --> G["store('services', 'public')"]
    F -- "لا" --> H["بدون صورة"]
    G --> I["Service::create"]
    H --> I
    I --> J["event ServiceSavedForNews"]
    J --> K["NewsAutomationService ينشئ/يحذف الخبر حسب النشر"]
```

## خوارزمية تحديث خدمة

```mermaid
flowchart TD
    A["PUT admin/services/{service}"] --> B["UpdateServiceRequest validate"]
    B --> C["normalize"]
    C --> D{"هل image جديدة؟"}
    D -- "نعم" --> E["حذف الصورة القديمة"]
    E --> F["رفع الصورة الجديدة"]
    D -- "لا" --> G["إبقاء الصورة الحالية"]
    F --> H["service->update"]
    G --> H
    H --> I["service->refresh"]
    I --> J["event ServiceSavedForNews"]
```

## خوارزمية العرض في الموقع

```mermaid
flowchart TD
    A["GET /services"] --> B["Service::published"]
    B --> C["is_published = true"]
    C --> D["orderBy sort_order"]
    D --> E["عرض site.services"]
```

## نتيجة العملية

عند نشر خدمة تظهر في صفحة الخدمات، ويمكن أن تظهر لها مشاريع مرتبطة، كما يمكن إنشاء خبر تلقائي عنها في صفحة الأخبار.
