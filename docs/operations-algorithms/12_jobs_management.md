# خوارزمية إدارة الوظائف

## الهدف

إدارة الوظائف الشاغرة في الشركة، عرض النشطة منها فقط في الموقع، واستقبال طلبات التقديم مع ملفات السيرة الذاتية.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/JobController.php`
- `app/Http/Requests/Admin/StoreJobRequest.php`
- `app/Http/Requests/Admin/UpdateJobRequest.php`
- `app/Models/Job.php`
- `app/Http/Controllers/ContactRequestController.php`
- `app/Services/NewsAutomationService.php`

## خوارزمية إنشاء أو تحديث وظيفة

```mermaid
flowchart TD
    A["submit وظيفة"] --> B["Store/UpdateJobRequest validate"]
    B --> C["title مطلوب"]
    C --> D["is_active من checkbox"]
    D --> E["requirements: تحويل أسطر إلى مصفوفة"]
    E --> F["skills: تحويل أسطر إلى مصفوفة"]
    F --> G{"إنشاء أم تحديث؟"}
    G -- "إنشاء" --> H["Job::create"]
    G -- "تحديث" --> I["job->update"]
    H --> J["event JobSavedForNews"]
    I --> J
    J --> K["خبر تلقائي للوظيفة النشطة أو حذف الخبر عند التعطيل"]
```

## خوارزمية تحويل المتطلبات والمهارات

```mermaid
flowchart TD
    A["نص متعدد الأسطر"] --> B{"هل النص فارغ؟"}
    B -- "نعم" --> C["[]"]
    B -- "لا" --> D["تقسيم حسب \\r\\n أو \\n"]
    D --> E["trim لكل سطر"]
    E --> F["حذف الفارغ"]
    F --> G["values"]
    G --> H["مصفوفة تحفظ JSON"]
```

## خوارزمية عرض الوظائف

```mermaid
flowchart TD
    A["GET /careers"] --> B["Job::published"]
    B --> C["Job::active"]
    C --> D["is_active = true"]
    D --> E["closing_date فارغ أو >= now"]
    E --> F["paginate(10)"]
    F --> G["عرض site.careers"]
```

## خوارزمية التقديم على وظيفة

```mermaid
flowchart TD
    A["POST /careers/{job}/apply"] --> B["validate بيانات المتقدم"]
    B --> C["cv_file مطلوب PDF حتى 5MB"]
    C --> D["store cv-files"]
    D --> E["Contact::create بنوع طلب توظيف"]
    E --> F["service_requested = عنوان الوظيفة"]
    F --> G["event ContactRequestSubmitted"]
    G --> H["إشعار الإدارة"]
```

## نتيجة العملية

تدير الشركة صفحة توظيف كاملة، وتصل طلبات المرشحين إلى لوحة التحكم ضمن الرسائل والطلبات.
