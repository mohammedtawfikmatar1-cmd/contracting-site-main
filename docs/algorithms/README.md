# توثيق خوارزميات المشروع

تم تحليل مشروع Laravel الحالي واستخراج الخوارزميات العملية الموجودة في طبقات:

- `app/Models`
- `app/Http/Controllers`
- `app/Services`
- `app/Listeners`
- `app/Providers`
- `routes/web.php`
- `resources/views/admin/partials/summernote.blade.php`

المقصود بالخوارزمية هنا هو أي تدفق منطقي متكرر أو مهم في النظام، حتى لو لم يكن خوارزمية أكاديمية مثل DFS أو BFS. كل ملف يحتوي على: شرح، مكان الكود، مقتطف كود، و Flowchart بصيغة Mermaid.

## الفهرس

| الملف | الخوارزمية | أهم الملفات المرتبطة |
|---|---|---|
| [01_unique_slug_generation.md](01_unique_slug_generation.md) | توليد `slug` فريد وداعم للعربية | `HasUniqueSlug.php`, `Client.php` |
| [02_content_search.md](02_content_search.md) | البحث العام وبحث لوحة التحكم | `SiteController.php`, `Admin/SearchController.php` |
| [03_auto_news_synchronization.md](03_auto_news_synchronization.md) | مزامنة الأخبار التلقائية من الخدمات والمشاريع والمناقصات والوظائف | `NewsAutomationService.php`, `AppServiceProvider.php` |
| [04_contact_request_flow.md](04_contact_request_flow.md) | استقبال طلبات الزوار وتحويلها إلى رسائل إدارية | `ContactRequestController.php`, `SendAdminContactNotification.php` |
| [05_notification_deduplication.md](05_notification_deduplication.md) | إزالة تكرار الإشعارات وتعليم المرتبط منها كمقروء | `NotificationController.php`, `AppServiceProvider.php` |
| [DFS_in_summernote.md](DFS_in_summernote.md) | تدفق رفع صور Summernote واستبدال المعاينة بالرابط النهائي | `summernote.blade.php`, `EditorUploadController.php` |
| [06_settings_cache.md](06_settings_cache.md) | قراءة الإعدادات مع الكاش وتحويل القيم حسب النوع | `Setting.php`, `SiteController.php` |
| [07_client_project_sync.md](07_client_project_sync.md) | ربط العملاء بالمشاريع ومنع التعارض | `ClientController.php`, `Client.php`, `Project.php` |
| [08_sitemap_generation.md](08_sitemap_generation.md) | توليد خريطة الموقع XML وملف robots.txt | `SiteController.php`, `routes/web.php` |
| [09_publication_filters_and_status.md](09_publication_filters_and_status.md) | فلترة المحتوى المنشور وحالات الوظائف والمناقصات | `Service.php`, `Project.php`, `News.php`, `Job.php`, `Tender.php`, `Page.php` |
| [10_input_normalization.md](10_input_normalization.md) | تطبيع الحقول المترجمة والقوائم متعددة الأسطر قبل الحفظ | `ProjectController.php`, `ServiceController.php`, `NewsController.php`, `JobController.php` |

## ملاحظات قراءة

- أغلب الخوارزميات تعتمد على Eloquent scopes و events/listeners، لذلك قد تكون موزعة بين أكثر من ملف.
- مخططات Mermaid يمكن عرضها في GitHub أو أي محرر يدعم Mermaid.
- الملف `DFS_in_summernote.md` يحمل الاسم المطلوب في التبويب المفتوح، لكن الكود الحالي لا يطبق DFS بالمعنى الكلاسيكي؛ هو يوثق تدفق بيانات رفع صور المحرر.
