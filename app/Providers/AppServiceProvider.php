<?php

namespace App\Providers;

use App\Events\ContactRequestSubmitted;
use App\Listeners\SendAdminContactNotification;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ContactRequestSubmitted::class, SendAdminContactNotification::class);

        Gate::define('manage-users', function (User $user): bool {
            return (bool) $user->is_super_admin;
        });

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

            $pagesMenu = $sitePages->map(fn ($page) => [
                'label' => $page->title,
                'url' => route('pages.show', $page->slug),
                'active' => request()->routeIs('pages.show') && request()->route('slug') === $page->slug,
            ]);

            $siteMenu = $defaultMenu->merge($pagesMenu);
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
            $enableMultilingual = (bool) Setting::getValue('enable_multilingual', false);

            $view->with('adminUnreadNotificationsCount', $adminUser?->unreadNotifications()->count() ?? 0);
            $view->with('adminLatestNotifications', $adminUser?->notifications()->latest()->limit(5)->get() ?? collect());
            $view->with('enableMultilingual', $enableMultilingual);
        });
    }
}
