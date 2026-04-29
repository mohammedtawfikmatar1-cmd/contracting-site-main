# خوارزمية إدارة المشاريع

## الهدف

إدارة أعمال الشركة المنفذة وربط كل مشروع بخدمة وعميل اختياري، ثم عرضه في الموقع وإنشاء خبر تلقائي عند نشره.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/ProjectController.php`
- `app/Http/Requests/Admin/StoreProjectRequest.php`
- `app/Http/Requests/Admin/UpdateProjectRequest.php`
- `app/Models/Project.php`
- `app/Models/Service.php`
- `app/Models/Client.php`
- `app/Services/NewsAutomationService.php`

## خوارزمية تجهيز نموذج الإنشاء أو التعديل

```mermaid
flowchart TD
    A["فتح create/edit project"] --> B["جلب الخدمات orderBy(title)"]
    B --> C{"هل جدول clients موجود؟"}
    C -- "نعم" --> D["جلب العملاء orderBy(name)"]
    C -- "لا" --> E["clients = collect()"]
    D --> F["عرض النموذج"]
    E --> F
```

## خوارزمية إنشاء مشروع

```mermaid
flowchart TD
    A["POST admin/projects"] --> B["StoreProjectRequest validate"]
    B --> C["service_id مطلوب وموجود"]
    C --> D["normalize title/description/category/location"]
    D --> E["is_published من checkbox"]
    E --> F{"هل توجد image؟"}
    F -- "نعم" --> G["رفع الصورة إلى projects"]
    F -- "لا" --> H["بدون صورة"]
    G --> I["Project::create"]
    H --> I
    I --> J["event ProjectSavedForNews"]
    J --> K["مزامنة خبر تلقائي"]
```

## خوارزمية تحديث مشروع

```mermaid
flowchart TD
    A["PUT admin/projects/{project}"] --> B["UpdateProjectRequest validate"]
    B --> C["normalize"]
    C --> D{"هل image جديدة؟"}
    D -- "نعم" --> E["حذف الصورة القديمة من التخزين"]
    E --> F["رفع الصورة الجديدة"]
    D -- "لا" --> G["إبقاء الصورة القديمة"]
    F --> H["project->update"]
    G --> H
    H --> I["event ProjectSavedForNews"]
```

## خوارزمية حذف مشروع

```mermaid
flowchart TD
    A["DELETE admin/projects/{project}"] --> B{"هل للمشروع صورة؟"}
    B -- "نعم" --> C["حذف الصورة من public disk"]
    B -- "لا" --> D["project->delete"]
    C --> D
    D --> E["Model deleting يحذف الأخبار المرتبطة"]
    E --> F["عودة نجاح"]
```

## خوارزمية العرض في الموقع

```mermaid
flowchart TD
    A["GET /projects أو /projects/{slug}"] --> B["Project::published"]
    B --> C["جلب المشاريع المنشورة فقط"]
    C --> D["في التفاصيل: جلب service والمشاريع المشابهة"]
    D --> E["عرض صفحات المشاريع"]
```

## نتيجة العملية

المشروع المنشور يصبح جزءا من معرض أعمال الشركة، ويرتبط بالخدمة والعميل، وقد ينتج عنه خبر تلقائي في الأخبار.
