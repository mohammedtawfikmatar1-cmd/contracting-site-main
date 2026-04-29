# خوارزمية إدارة الرسائل والطلبات

## الهدف

استقبال كل تفاعلات الزوار في جدول واحد `contacts` ثم عرضها في لوحة التحكم ومتابعة حالتها.

## الملفات المرتبطة

- `app/Http/Controllers/ContactRequestController.php`
- `app/Http/Controllers/Admin/ContactController.php`
- `app/Listeners/SendAdminContactNotification.php`
- `app/Notifications/NewContactRequestNotification.php`
- `app/Models/Contact.php`

## مصادر الطلبات

| المصدر | النوع المخزن |
|---|---|
| نموذج التواصل العام | `تواصل عام` |
| طلب خدمة | `طلب خدمة` |
| تقديم وظيفة | `طلب توظيف` |
| عرض مناقصة | `عرض طلب` |

## خوارزمية استقبال طلب من الزائر

```mermaid
flowchart TD
    A["زائر يرسل نموذج"] --> B["Route POST"]
    B --> C["ContactRequestController"]
    C --> D["validate البيانات والملفات"]
    D --> E{"هل يوجد ملف؟"}
    E -- "نعم" --> F["رفع الملف إلى public disk"]
    E -- "لا" --> G["تجهيز البيانات"]
    F --> G
    G --> H["Contact::create status=pending"]
    H --> I["event ContactRequestSubmitted"]
    I --> J["SendAdminContactNotification"]
    J --> K["إشعار كل مستخدم بدون تكرار"]
```

## خوارزمية عرض الرسائل في الإدارة

```mermaid
flowchart TD
    A["GET admin/contacts"] --> B["قراءة q"]
    B --> C{"هل q موجودة؟"}
    C -- "نعم" --> D["بحث في الاسم والبريد والجوال والرسالة"]
    C -- "لا" --> E["كل الرسائل"]
    D --> F["latest + paginate(20)"]
    E --> F
    F --> G["عرض admin.contacts.index"]
```

## خوارزمية فتح رسالة

```mermaid
flowchart TD
    A["GET admin/contacts/{contact}"] --> B["markRelatedNotificationsAsRead"]
    B --> C["قراءة المستخدم الحالي"]
    C --> D["unreadNotifications where data->contact_id"]
    D --> E["markAsRead لكل إشعار"]
    E --> F["عرض تفاصيل الطلب"]
```

## خوارزمية تحويل الحالة إلى قيد المعالجة

```mermaid
flowchart TD
    A["PATCH admin/contacts/{contact}/read"] --> B{"status = pending؟"}
    B -- "نعم" --> C["status = in_progress"]
    B -- "لا" --> D["إبقاء الحالة"]
    C --> E["تعليم الإشعارات المرتبطة كمقروءة"]
    D --> E
    E --> F["العودة لتفاصيل الرسالة"]
```

## خوارزمية حذف رسالة

```mermaid
flowchart TD
    A["DELETE admin/contacts/{contact}"] --> B["contact->delete"]
    B --> C["عودة لقائمة الرسائل"]
```

## نتيجة العملية

كل فرصة تواصل أو طلب خدمة أو توظيف أو عرض مناقصة تتحول إلى سجل قابل للمتابعة، مما يجعل الموقع أداة تشغيل وليس مجرد واجهة تعريفية.
