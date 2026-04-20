<?php

/**
 * AppServiceProvider — يُحمّل مبكرًا عند تشغيل Laravel (boot).
 *
 * ماذا نضع هنا عادة؟
 * --------------------
 * - ربط الأحداث (Events) بالمستمعين (Listeners): أي حدث يحدث → من ينفّذ الكود بعده.
 * - View::composer: تمرير متغيرات جاهزة لكل قوالب الموقع (site.*) مثل الإعدادات والقائمة.
 * - Gate: قواعد صلاحيات بسيطة (هنا: إدارة المستخدمين للمشرف الأعلى فقط).
 *
 * تسلسل مهم للمبتدئين:
 * ---------------------
 * 1) طلب HTTP يصل إلى routes/web.php
 * 2) المتحكم ينفّذ المنطق ويعيد view(...)
 * 3) قبل عرض القالب، يعمل composer الخاص بـ site.* فيضيف siteSettings و siteMenu
 */
namespace App\Providers;

use App\Events\ContactRequestSubmitted;
use App\Events\JobSavedForNews;
use App\Events\ProjectSavedForNews;
use App\Events\ServiceSavedForNews;
use App\Events\TenderSavedForNews;
use App\Listeners\SendAdminContactNotification;
use App\Listeners\SyncAutoNewsFromJob;
use App\Listeners\SyncAutoNewsFromProject;
use App\Listeners\SyncAutoNewsFromService;
use App\Listeners\SyncAutoNewsFromTender;
use App\Models\Client;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // هنا تُسجّل الخدمات في "حاوية" Laravel (مثل أصناف تُستبدل باختبارات). غالبًا يُترك فارغًا في مشاريع صغيرة.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تثبيت اللغة الحالية على العربية بعد إزالة نظام التبديل من الواجهة.
        app()->setLocale('ar');

        // طلب تواصل من الموقع → إشعار للمستخدمين في لوحة التحكم
        Event::listen(ContactRequestSubmitted::class, SendAdminContactNotification::class);

        /*
         | سلسلة الأخبار التلقائية (راجع app/Services/NewsAutomationService.php):
         | حفظ في الإدارة → حدث *SavedForNews → مستمع Sync* → تحديث جدول news
         */
        Event::listen(ProjectSavedForNews::class, SyncAutoNewsFromProject::class);
        Event::listen(TenderSavedForNews::class, SyncAutoNewsFromTender::class);
        Event::listen(JobSavedForNews::class, SyncAutoNewsFromJob::class);
        Event::listen(ServiceSavedForNews::class, SyncAutoNewsFromService::class);

        Gate::define('manage-users', function (User $user): bool {
            return (bool) $user->is_super_admin;
        });

        // أي قالب يبدأ اسمه بـ site. (مثل site.index) يستقبل المتغيرات التالية تلقائيًا
        View::composer('site.*', function ($view) {
            $settings = collect();
            if (Schema::hasTable((new Setting())->getTable())) {
                $settings = Setting::query()
                    ->get()
                    ->mapWithKeys(fn ($setting) => [$setting->key => $setting->parseValue()]);
            }

            $sitePages = collect();
            if (Schema::hasTable((new Page())->getTable())) {
                $sitePages = Page::query()
                    ->published()
                    ->orderBy('id')
                    ->get(['id', 'title', 'slug']);
            }

            $clientsPageEnabled = (bool) ($settings->get('clients_page_enabled', false));
            $clientsUrl = route('clients');

            $defaultMenu = collect([
                ['label' => 'الرئيسية', 'url' => route('home'), 'active' => request()->routeIs('home')],
                ['label' => 'خدماتنا', 'url' => route('services'), 'active' => request()->routeIs('services*')],
                ['label' => 'أعمالنا', 'url' => route('projects'), 'active' => request()->routeIs('projects*')],
                ['label' => 'من نحن', 'url' => route('about'), 'active' => request()->routeIs('about')],
                ['label' => 'الوظائف', 'url' => route('careers'), 'active' => request()->routeIs('careers*')],
                ['label' => 'المناقصات', 'url' => route('tenders'), 'active' => request()->routeIs('tenders*')],
                ['label' => 'الأخبار', 'url' => route('news'), 'active' => request()->routeIs('news*')],
                ['label' => 'اتصل بنا', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
            ]);

            // إدراج "عملاؤنا" بعد "أعمالنا" عند تفعيل الصفحة وجاهزية جدول العملاء.
            if ($clientsPageEnabled && Schema::hasTable((new Client())->getTable())) {
                $aboutIndex = $defaultMenu->search(fn ($item) => ($item['label'] ?? '') === 'من نحن');
                $item = [
                    'label' => 'عملاؤنا',
                    'url' => $clientsUrl,
                    'active' => request()->routeIs('clients'),
                ];
                if ($aboutIndex !== false) {
                    $defaultMenu->splice($aboutIndex, 0, [$item]);
                } else {
                    $defaultMenu->push($item);
                }
            }

            /*
             * الصفحات الثابتة (CMS) تُعرض في الهيدر تحت عنوان فرعي (قائمة منسدلة)
             * ولا تُدمج في الشريط الأفقي الرئيسي حتى لا تزدحم الروابط وتُفسد التصميم.
             */
            $siteMenu = $defaultMenu;
            $configuredMenu = $settings->get('site_menu');
            if (is_array($configuredMenu) && ! empty($configuredMenu)) {
                $siteMenu = collect($configuredMenu)
                    ->filter(fn ($item) => is_array($item) && ! empty($item['label']) && ! empty($item['url']))
                    ->map(fn ($item) => [
                        'label' => (string) $item['label'],
                        'url' => (string) $item['url'],
                        'active' => request()->fullUrlIs((string) $item['url']) || request()->url() === (string) $item['url'],
                    ])
                    ->values();
            }

            // عند استخدام قائمة مخصصة، نُدرج رابط "عملاؤنا" تلقائياً إن كان مفعّلاً وغير مضاف يدوياً.
            if ($clientsPageEnabled && Schema::hasTable((new Client())->getTable())) {
                $hasClientsLink = $siteMenu->contains(fn ($item) => ($item['url'] ?? '') === $clientsUrl);
                if (! $hasClientsLink) {
                    $siteMenu->push([
                        'label' => 'عملاؤنا',
                        'url' => $clientsUrl,
                        'active' => request()->routeIs('clients'),
                    ]);
                }
            }

            $view->with('siteSettings', $settings);
            $view->with('sitePages', $sitePages);
            $view->with('siteMenu', $siteMenu);
        });

        View::composer('admin.*', function ($view) {
            $adminUser = Auth::user() ?? User::query()->first();
            // إبقاء خيار الإعدادات كمكوّن مستقبلي فقط دون تفعيل فعلي داخل النماذج حاليا.
            $enableMultilingual = false;

            $view->with('adminUnreadNotificationsCount', $adminUser?->unreadNotifications()->count() ?? 0);
            $view->with('adminLatestNotifications', $adminUser?->notifications()->latest()->limit(5)->get() ?? collect());
            $view->with('enableMultilingual', $enableMultilingual);
        });
    }
}
