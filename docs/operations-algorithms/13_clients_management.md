# خوارزمية إدارة العملاء

## الهدف

إدارة شعارات وعملاء الشركة وربطهم بمشاريع حقيقية، ثم عرضهم في الصفحة الرئيسية وصفحة عملاؤنا عند تفعيلها.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/ClientController.php`
- `app/Http/Requests/Admin/StoreClientRequest.php`
- `app/Http/Requests/Admin/UpdateClientRequest.php`
- `app/Models/Client.php`
- `app/Models/Project.php`
- `app/Models/Setting.php`

## خوارزمية إنشاء عميل

```mermaid
flowchart TD
    A["POST admin/clients"] --> B["StoreClientRequest validate"]
    B --> C["name مطلوب و logo مطلوب"]
    C --> D["project_ids مصفوفة مطلوبة min:1"]
    D --> E["إزالة تكرار project_ids وتحويلها int"]
    E --> F{"هل أحد المشاريع مرتبط بعميل آخر؟"}
    F -- "نعم" --> G["ValidationException"]
    F -- "لا" --> H["رفع الشعار إلى clients"]
    H --> I["Client::create"]
    I --> J["syncClientProjects"]
```

## خوارزمية تحديث عميل

```mermaid
flowchart TD
    A["PUT admin/clients/{client}"] --> B["UpdateClientRequest validate"]
    B --> C["تحضير project_ids"]
    C --> D{"هل يوجد مشروع مرتبط بعميل غير الحالي؟"}
    D -- "نعم" --> E["ValidationException"]
    D -- "لا" --> F{"هل logo جديد؟"}
    F -- "نعم" --> G["حذف الشعار القديم ورفع الجديد"]
    F -- "لا" --> H["تحديث البيانات"]
    G --> H
    H --> I["syncClientProjects"]
```

## خوارزمية مزامنة مشاريع العميل

```mermaid
flowchart TD
    A["syncClientProjects(client, projectIds)"] --> B["فك ارتباط كل مشاريع العميل الحالية"]
    B --> C["whereIn(projectIds)"]
    C --> D["update client_id = client.id"]
    D --> E["انتهاء"]
```

## خوارزمية تفعيل صفحة العملاء

```mermaid
flowchart TD
    A["POST admin/clients/page-toggle"] --> B["قراءة enabled"]
    B --> C["Setting::setValue clients_page_enabled"]
    C --> D["تفريغ كاش الإعدادات"]
    D --> E["عودة للقائمة"]
```

## خوارزمية العرض في الموقع

```mermaid
flowchart TD
    A["GET /clients أو الرئيسية"] --> B{"clients_page_enabled مفعلة؟"}
    B -- "لا" --> C["404 لصفحة clients أو إخفاء الرابط"]
    B -- "نعم" --> D["Client::published"]
    D --> E["whereHas projects المنشورة"]
    E --> F["عرض العملاء وشعاراتهم"]
```

## نتيجة العملية

لا يظهر العميل كاسم مجرد، بل يرتبط بمشاريع فعلية، وهذا يجعل صفحة العملاء أقوى تسويقيا وأكثر مصداقية.
