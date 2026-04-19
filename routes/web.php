<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TenderController as AdminTenderController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

/**
 * الغرض من الملف:
 * تعريف مسارات الويب (واجهة الموقع + لوحة التحكم) وتوجيهها إلى المتحكمات المناسبة.
 *
 * التبعية:
 * routes/web.php ضمن Laravel Router.
 *
 * المكونات الأساسية:
 * - SiteController: صفحات الواجهة الأمامية وعرض البيانات المنشورة.
 * - ContactRequestController: استقبال نماذج التواصل/الخدمة/التوظيف/المناقصات من الواجهة.
 * - Controllers داخل App\Http\Controllers\Admin: إدارة المحتوى من لوحة التحكم.
 *
 * خريطة تدفق البيانات:
 * - مسارات الواجهة (GET) تعرض بيانات منشورة مُدارة من لوحة التحكم (services/projects/clients/news/pages/tenders/jobs/settings).
 * - مسارات النماذج (POST) تستقبل طلبات الزوار وتخزنها (contacts) ثم تُطلق إشعارات للإدارة.
 * - مسارات الإدارة (admin/*) هي المصدر الأساسي لإنشاء/تحديث المحتوى الذي يظهر لاحقا في الواجهة الأمامية.
 */

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- واجهات الموقع العام (Site Routes) ---
// هذه المجموعة هي نقطة العرض لبيانات لوحة التحكم: تعرض العناصر المنشورة فقط.
Route::get('/', [SiteController::class, 'home'])->name('home');
// الصفحة الرئيسية: خدمات + مشاريع + أخبار (من لوحة التحكم) + إعدادات عامة.
Route::get('/about', [SiteController::class, 'about'])->name('about');
// صفحة من نحن: تعتمد على إعدادات عامة + إحصاءات من قاعدة البيانات.
Route::get('/services', [SiteController::class, 'services'])->name('services');
// قائمة الخدمات المنشورة (مُدارة من قسم الخدمات في الإدارة).
Route::get('/services/{slug}', [SiteController::class, 'serviceDetails'])->name('services.details');
// تفاصيل خدمة واحدة + مشاريع مرتبطة بها (تأتي من قسم المشاريع والخدمات).
Route::get('/projects', [SiteController::class, 'projects'])->name('projects');
// أرشيف المشاريع المنشورة (مُدار من قسم المشاريع).
Route::get('/projects/{slug}', [SiteController::class, 'projectDetails'])->name('projects.details');
// تفاصيل مشروع واحد + مشاريع ذات صلة (حسب الخدمة).
Route::get('/news', [SiteController::class, 'news'])->name('news');
// أرشيف الأخبار المنشورة (مُدار من قسم الأخبار).
Route::get('/news/{slug}', [SiteController::class, 'newsDetails'])->name('news.details');
// تفاصيل خبر واحد + أخبار ذات صلة.
Route::get('/careers', [SiteController::class, 'careers'])->name('careers');
// صفحة الوظائف: تعرض الوظائف النشطة التي أضافتها الإدارة.
Route::get('/careers/apply/{jobId}', [SiteController::class, 'jobApply'])->name('careers.apply');
// نموذج التقديم على وظيفة محددة (الوظائف تُدار من قسم الوظائف في الإدارة).
Route::get('/tenders', [SiteController::class, 'tenders'])->name('tenders');
// صفحة المناقصات المنشورة (تأتي من قسم المناقصات في الإدارة).
Route::get('/tenders/request/{tenderId}', [SiteController::class, 'tenderRequest'])->name('tenders.request');
// نموذج إرسال عرض/طلب لمناقصة محددة.
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
// صفحة التواصل: تعتمد على الإعدادات العامة + قائمة الخدمات المنشورة لاستخدامها في النموذج.
Route::get('/clients', [SiteController::class, 'clients'])->name('clients');
// صفحة عملاؤنا: تظهر للزوار فقط عند تفعيل الإعداد clients_page_enabled من لوحة التحكم.
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
// توحيد مسار login ليحول إلى صفحة دخول لوحة التحكم.
Route::get('/pages/{slug}', [SiteController::class, 'page'])->name('pages.show');
// صفحات ثابتة من قاعدة البيانات (تُدار من قسم الصفحات في الإدارة).
Route::get('/search', [SiteController::class, 'search'])->name('search');
// البحث في محتوى الموقع المنشور (خدمات/مشاريع/أخبار/وظائف/مناقصات/صفحات).
Route::get('/media/{path}', function (string $path) {
    // مسار وسائط موحد: يعرض الملفات مباشرة من التخزين العام دون الحاجة إلى storage:link.
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('media.file');

// --- استقبال نماذج الواجهة (Public Forms) ---
// هذه المسارات تُنشئ بيانات جديدة في جدول contacts وتُطلق إشعارات للإدارة.
Route::post('/contact', [ContactRequestController::class, 'storeGeneral'])->name('contact.store');
// نموذج التواصل العام: يُخزن كرسالة ويظهر في قسم "طلبات التواصل" في لوحة التحكم.
Route::post('/services/{service}/request', [ContactRequestController::class, 'storeServiceRequest'])->name('services.request');
// نموذج طلب خدمة من صفحة خدمة محددة: يظهر للإدارة مع اسم الخدمة المطلوبة.
Route::post('/careers/{job}/apply', [ContactRequestController::class, 'storeJobApplication'])->name('careers.apply.store');
// نموذج التوظيف: يرفع ملف السيرة الذاتية ويُسجل الطلب كـ career داخل contacts.
Route::post('/tenders/{tender}/request', [ContactRequestController::class, 'storeTenderRequest'])->name('tenders.request.store');
// نموذج المناقصة: يرفع ملف العرض (إن وجد) ويُسجل الطلب ليتابع في لوحة التحكم.

// --- صفحات ديناميكية من لوحة التحكم (Catch-all) ---
// يجب أن تكون في نهاية المسارات لتجنب التعارض
Route::get('/{slug}', [SiteController::class, 'dynamicPage'])
    ->where('slug', '^(?!admin(?:/|$)|services(?:/|$)|projects(?:/|$)|news(?:/|$)|careers(?:/|$)|tenders(?:/|$)|contact(?:/|$)|clients(?:/|$)|search(?:/|$)|pages(?:/|$)|storage(?:/|$)|up(?:/|$)).+');

// --- واجهات لوحة التحكم (Admin Dashboard Routes) ---
Route::prefix('admin')->name('admin.')->group(function () {
    // إعداد أول حساب إداري (مرة واحدة عند بداية المشروع) - يظهر إذا لم يوجد أي مستخدم.
    Route::get('/setup', [AdminAuthController::class, 'setupCreate'])->name('setup')->middleware('guest');
    Route::post('/setup', [AdminAuthController::class, 'setupStore'])->name('setup.store')->middleware('guest');
    // تسجيل دخول/خروج لوحة التحكم.
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    Route::middleware('auth')->group(function () {
        // لوحة القيادة: ملخص إحصائي لبيانات النظام القادمة من الأقسام المختلفة.
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // الإعدادات العامة + الهوية البصرية: تتحكم في عناصر ثابتة تظهر في الواجهة.
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
        Route::put('/settings/{setting}', [SettingController::class, 'update'])->name('settings.update');
        Route::delete('/settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy');
        Route::get('/settings/branding', [SettingController::class, 'branding'])->name('settings.branding');
        Route::post('/settings/branding', [SettingController::class, 'saveBranding'])->name('settings.branding.save');
        // صفحة مخصصة للتحكم الكامل بمحتوى "من نحن" في الواجهة.
        Route::get('/settings/about', [SettingController::class, 'aboutPage'])->name('settings.about');
        Route::post('/settings/about', [SettingController::class, 'saveAboutPage'])->name('settings.about.save');

        // إدارة المستخدمين: تتحكم في من لديه صلاحية دخول الإدارة وإدارة المحتوى.
        Route::resource('users', AdminUserController::class)->except(['show'])->middleware('can:manage-users');

        // أقسام المحتوى: هذه هي مصادر البيانات التي تظهر في الواجهة عند تفعيل النشر/الحالة.
        Route::resource('pages', PageController::class)->except(['show']);
        Route::resource('services', AdminServiceController::class)->except(['show']);
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::resource('news', AdminNewsController::class)->except(['show']);
        Route::resource('tenders', AdminTenderController::class)->except(['show']);
        Route::resource('jobs', JobController::class)->except(['show']);

        // العملاء (شعارات وشركاء): يُربطون بمشاريع حقيقية ويظهر أثرهم في الصفحة الرئيسية وصفحة عملاؤنا عند التفعيل.
        Route::resource('clients', AdminClientController::class)->except(['show']);
        Route::post('/clients/page-toggle', [AdminClientController::class, 'toggleClientsPage'])->name('clients.page-toggle');

        // الرسائل/الطلبات الواردة من الواجهة: لمراجعتها وتغيير حالتها وحذفها.
        Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}', [AdminContactController::class, 'show'])->name('contacts.show');
        Route::patch('/contacts/{contact}/read', [AdminContactController::class, 'markAsRead'])->name('contacts.read');
        Route::delete('/contacts/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');

        // البحث الداخلي في لوحة التحكم للوصول السريع إلى كيانات الإدارة.
        Route::get('/search', AdminSearchController::class)->name('search');
        // إشعارات الإدارة: تُنشأ غالبا عند وصول طلبات جديدة من الواجهة.
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
});
