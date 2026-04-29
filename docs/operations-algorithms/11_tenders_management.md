# خوارزمية إدارة المناقصات

## الهدف

إدارة المناقصات التي تعلنها الشركة، مع حالة النشر وتاريخ الإغلاق، ثم عرضها في الموقع واستقبال عروض الزوار عليها.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/TenderController.php`
- `app/Http/Requests/Admin/StoreTenderRequest.php`
- `app/Http/Requests/Admin/UpdateTenderRequest.php`
- `app/Models/Tender.php`
- `app/Services/NewsAutomationService.php`
- `app/Http/Controllers/ContactRequestController.php`

## خوارزمية إنشاء أو تحديث مناقصة

```mermaid
flowchart TD
    A["submit مناقصة"] --> B["Store/UpdateTenderRequest validate"]
    B --> C["title مطلوب"]
    C --> D["closing_date تاريخ مطلوب"]
    D --> E["status ضمن open/closed/completed"]
    E --> F["is_published من checkbox"]
    F --> G{"إنشاء أم تحديث؟"}
    G -- "إنشاء" --> H["Tender::create"]
    G -- "تحديث" --> I["tender->update"]
    H --> J["event TenderSavedForNews"]
    I --> J
    J --> K["مزامنة خبر تلقائي أو حذفه"]
```

## خوارزمية تحديد المناقصة المفتوحة

```mermaid
flowchart TD
    A["Tender::open أو isOpen"] --> B{"status = open؟"}
    B -- "لا" --> C["ليست مفتوحة"]
    B -- "نعم" --> D{"closing_date >= now أو isFuture؟"}
    D -- "نعم" --> E["مفتوحة للتقديم"]
    D -- "لا" --> C
```

## خوارزمية عرض المناقصات للزائر

```mermaid
flowchart TD
    A["GET /tenders"] --> B["Tender::published"]
    B --> C["is_published = true"]
    C --> D["latest(closing_date) + paginate(10)"]
    D --> E["عرض site.tenders"]
```

## خوارزمية إرسال عرض مناقصة

```mermaid
flowchart TD
    A["زائر يفتح /tenders/request/{id}"] --> B["عرض نموذج المناقصة"]
    B --> C["POST /tenders/{tender}/request"]
    C --> D["validate full_name/phone/email/message/proposal_file"]
    D --> E{"هل يوجد ملف عرض PDF؟"}
    E -- "نعم" --> F["store tender-proposals"]
    E -- "لا" --> G["cv_file = null"]
    F --> H["Contact::create بنوع عرض طلب"]
    G --> H
    H --> I["event ContactRequestSubmitted"]
    I --> J["إشعار الإدارة"]
```

## نتيجة العملية

تستطيع الشركة نشر المناقصات واستقبال عروض PDF من المقاولين أو الموردين، وتصل كل العروض إلى صندوق الطلبات في لوحة التحكم.
