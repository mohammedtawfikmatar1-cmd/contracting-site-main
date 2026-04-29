# خوارزمية إدارة الإعدادات العامة

## الهدف

إدارة أي إعداد عام في النظام بمفتاح وقيمة ونوع، مع كاش لتقليل الاستعلامات وتحويل القيم حسب نوعها.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/SettingController.php`
- `app/Http/Requests/Admin/StoreSettingRequest.php`
- `app/Http/Requests/Admin/UpdateSettingRequest.php`
- `app/Models/Setting.php`

## خوارزمية إنشاء إعداد

```mermaid
flowchart TD
    A["POST admin/settings"] --> B["StoreSettingRequest validate"]
    B --> C["key alpha_dash وفريد"]
    C --> D["type ضمن الأنواع المسموحة"]
    D --> E{"هل type = boolean؟"}
    E -- "نعم" --> F["تحويل value إلى 1 أو 0"]
    E -- "لا" --> G["ترك القيمة كما هي"]
    F --> H["Setting::create"]
    G --> H
    H --> I["حدث saved يفرغ الكاش"]
    I --> J["عودة نجاح"]
```

## خوارزمية تحديث إعداد

```mermaid
flowchart TD
    A["PUT admin/settings/{setting}"] --> B["UpdateSettingRequest validate"]
    B --> C["key فريد مع تجاهل الإعداد الحالي"]
    C --> D{"هل type = boolean؟"}
    D -- "نعم" --> E["توحيد value إلى 1 أو 0"]
    D -- "لا" --> F["تحديث بالقيمة المدخلة"]
    E --> G["setting->update"]
    F --> G
    G --> H["تفريغ كاش key و site:settings:all"]
    H --> I["عودة نجاح"]
```

## خوارزمية قراءة إعداد

```mermaid
flowchart TD
    A["Setting::getValue(key, default)"] --> B{"هل جدول settings موجود؟"}
    B -- "لا" --> C["إرجاع default"]
    B -- "نعم" --> D["Cache::remember لمدة 10 دقائق"]
    D --> E["جلب السجل حسب key"]
    E --> F{"هل السجل موجود؟"}
    F -- "لا" --> C
    F -- "نعم" --> G["parseValue حسب type"]
    G --> H["إرجاع القيمة"]
```

## أنواع القيم

| النوع | طريقة التحويل |
|---|---|
| `text` | يرجع كنص |
| `longtext` | يرجع كنص طويل |
| `json` | يحاول `json_decode` |
| `boolean` | يحول إلى true/false |
| `integer` | يحول إلى رقم |
| `image` | يرجع رابط `/media/{path}` |
| `color` | يرجع كقيمة لون نصية |

## نتيجة العملية

هذه الخوارزمية تجعل إعدادات الموقع مرنة وسريعة، وتربط لوحة التحكم بالقوالب العامة من خلال `siteSettings`.
