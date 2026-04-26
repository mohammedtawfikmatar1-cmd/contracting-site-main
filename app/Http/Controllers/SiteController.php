<?php

/**
 * SiteController — واجهة الزوار (الموقع العام، وليس /admin)
 *
 * الفكرة ببساطة:
 * ---------------
 * - المسارات في routes/web.php تربط كل URL بدالة هنا (مثل home، news، contact).
 * - كل دالة تجلب من قاعدة البيانات ما هو "منشور" فقط، ثم تعيد view('site....').
 *
 * أين تُدار البيانات؟
 * ---------------------
 * من لوحة التحكم (مجلد Admin). ما يُحفظ هناك يُقرأ هنا باستخدام نماذج Eloquent (App\Models\*).
 *
 * الأخبار:
 * --------
 * تظهر الأخبار اليدوية + الأخبار الناتجة تلقائيًا عن مشروع/مناقصة/وظيفة (انظر NewsAutomationService).
 */
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Job;
use App\Models\News;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Tender;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteController extends Controller
{
    /**
     * الصفحة الرئيسية:
     * تسحب أبرز الخدمات والمشاريع وآخر الأخبار المنشورة من لوحة التحكم.
     */
    public function home()
    {
        // كاش قصير للصفحة الرئيسية لتخفيف ضغط الاستعلامات المتكررة.
        $services = Cache::remember('site:home:services', now()->addMinutes(5), fn () => Service::query()->published()->limit(8)->get());
        $projects = Cache::remember('site:home:projects', now()->addMinutes(5), fn () => Project::query()->published()->latest()->limit(6)->get());
        // الأخبار: تشمل المنشورة يدويًا والمزامَنة تلقائيًا (نفس جدول news)
        $news = Cache::remember('site:home:news', now()->addMinutes(2), fn () => News::query()->published()->latest('published_at')->limit(3)->get());

        $homeClients = Cache::remember('site:home:clients', now()->addMinutes(10), function () {
            if (! Schema::hasTable((new Client())->getTable())) {
                return collect();
            }

            return Client::query()
                ->published()
                ->whereHas('projects', fn ($q) => $q->published())
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });

        return view('site.index', [
            'services' => $services,
            'projects' => $projects,
            'news' => $news,
            'homeClients' => $homeClients,
            'settings' => $this->siteSettings(),
        ]);
    }

    /**
     * صفحة "عملاؤنا":
     * تظهر فقط عند تفعيل clients_page_enabled من لوحة التحكم؛ وإلا يُعاد 404.
     */
    public function clients()
    {
        if (! (bool) Setting::getValue('clients_page_enabled', false)) {
            abort(404);
        }

        if (! Schema::hasTable((new Client())->getTable())) {
            abort(404);
        }

        $clients = Cache::remember('site:clients:index', now()->addMinutes(5), fn () => Client::query()
            ->published()
            ->whereHas('projects', fn ($q) => $q->published())
            ->with(['projects' => fn ($q) => $q->published()->latest()->limit(12)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get());

        return view('site.clients', [
            'settings' => $this->siteSettings(),
            'clients' => $clients,
        ]);
    }

    /**
     * صفحة "من نحن":
     * تعتمد على الإعدادات العامة وإحصاءات مستخلصة من البيانات الفعلية داخل النظام.
     */
    public function about()
    {
        return view('site.about', [
            'settings' => $this->siteSettings(),
            'stats' => $this->mainStats(),
        ]);
    }

    /**
     * عرض جميع الخدمات المنشورة كما أدخلها مدير المحتوى.
     */
    public function services()
    {
        $services = Cache::remember('site:services:index', now()->addMinutes(5), fn () => Service::query()->published()->get());

        return view('site.services', compact('services'));
    }

    /**
     * صفحة تفاصيل الخدمة.
     * يتم فيها أيضا جلب مشاريع مرتبطة بهذه الخدمة لتوضيح الأعمال المنفذة تحت نفس التصنيف.
     */
    public function serviceDetails(string $slug)
    {
        $service = Cache::remember("site:services:{$slug}", now()->addMinutes(5), fn () => Service::query()->where('slug', $slug)->firstOrFail());
        $relatedProjects = Cache::remember("site:services:{$slug}:projects", now()->addMinutes(5), fn () => Project::query()
            ->where('service_id', $service->id)
            ->published()
            ->latest()
            ->limit(4)
            ->get());

        return view('site.sub-pages.service-details', compact('service', 'relatedProjects'));
    }

    /**
     * صفحة أرشيف المشاريع المنشورة مع تقسيم الصفحات.
     */
    public function projects()
    {
        $page = (int) request('page', 1);
        $projects = Cache::remember("site:projects:index:p{$page}", now()->addMinutes(2), fn () => Project::query()->published()->latest()->paginate(9));

        return view('site.projects', compact('projects'));
    }

    /**
     * صفحة تفاصيل مشروع واحد مع اقتراح مشاريع مشابهة من نفس الخدمة.
     */
    public function projectDetails(string $slug)
    {
        $project = Cache::remember("site:projects:{$slug}", now()->addMinutes(5), fn () => Project::query()->with('service')->where('slug', $slug)->firstOrFail());
        $relatedProjects = Cache::remember("site:projects:{$slug}:related", now()->addMinutes(5), fn () => Project::query()
            ->where('id', '!=', $project->id)
            ->when($project->service_id, fn ($q) => $q->where('service_id', $project->service_id))
            ->published()
            ->latest()
            ->limit(3)
            ->get());

        return view('site.sub-pages.project-details', compact('project', 'relatedProjects'));
    }

    /**
     * عرض قائمة الأخبار المنشورة للزوار.
     */
    public function news()
    {
        $page = (int) request('page', 1);
        $news = Cache::remember("site:news:index:p{$page}", now()->addMinutes(2), fn () => News::query()->published()->latest('published_at')->paginate(9));

        return view('site.news', compact('news'));
    }

    /**
     * صفحة تفاصيل الخبر مع أخبار ذات صلة من نفس التصنيف إن وجد.
     */
    public function newsDetails(string $slug)
    {
        $newsItem = Cache::remember("site:news:{$slug}", now()->addMinutes(5), fn () => News::query()->where('slug', $slug)->firstOrFail());
        $relatedNews = Cache::remember("site:news:{$slug}:related", now()->addMinutes(5), fn () => News::query()
            ->where('id', '!=', $newsItem->id)
            ->when($newsItem->category, fn ($q) => $q->where('category', $newsItem->category))
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get());

        return view('site.sub-pages.news-details', compact('newsItem', 'relatedNews'));
    }

    /**
     * صفحة المناقصات المنشورة القادمة من قسم المناقصات في الإدارة.
     */
    public function tenders()
    {
        $page = (int) request('page', 1);
        $tenders = Cache::remember("site:tenders:index:p{$page}", now()->addMinutes(2), fn () => Tender::query()->published()->latest('closing_date')->paginate(10));

        return view('site.tenders', compact('tenders'));
    }

    /**
     * عرض نموذج التقديم/الطلب الخاص بمناقصة محددة.
     */
    public function tenderRequest(int $tenderId)
    {
        $tender = Tender::query()->findOrFail($tenderId);

        return view('site.sub-pages.tender-request', compact('tender'));
    }

    /**
     * صفحة الوظائف الشاغرة.
     * لا تُعرض إلا الوظائف النشطة القادمة من لوحة التحكم.
     */
    public function careers()
    {
        $page = (int) request('page', 1);
        $jobs = Cache::remember("site:careers:index:p{$page}", now()->addMinutes(2), fn () => Job::query()->published()->paginate(10));

        return view('site.careers', compact('jobs'));
    }

    /**
     * عرض نموذج التقديم على وظيفة محددة.
     */
    public function jobApply(int $jobId)
    {
        $job = Job::query()->findOrFail($jobId);

        return view('site.sub-pages.job-application', compact('job'));
    }

    /**
     * صفحة التواصل:
     * تُحمّل الإعدادات العامة وقائمة الخدمات المنشورة لاستخدامها داخل النموذج.
     */
    public function contact()
    {
        return view('site.contact', [
            'settings' => $this->siteSettings(),
            'services' => Cache::remember('site:contact:services', now()->addMinutes(10), fn () => Service::query()->published()->orderBy('title')->get()),
        ]);
    }

    /**
     * عرض صفحة ديناميكية من جدول الصفحات مع محاولة اختيار قالب مخصص إن كان موجودا.
     */
    public function page(string $slug)
    {
        $page = \App\Models\Page::query()->published()->where('slug', $slug)->firstOrFail();
        return view('site.page', compact('page'));
    }

    /**
     * مسار بديل يعيد استخدام نفس منطق page().
     */
    public function dynamicPage(string $slug)
    {
        return $this->page($slug);
    }

    /**
     * البحث الشامل في محتوى الموقع المنشور.
     * يعتمد على جداول الإدارة المختلفة ويجمع النتائج في صفحة واحدة.
     */
    public function search(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $q = trim((string) ($validated['q'] ?? ''));
        // تحويل عبارة البحث إلى LIKE pattern آمن للاستخدام في SQL.
        $like = $this->toLikePattern($q);

        $services = Service::query()
            ->published()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->limit(8)
            ->get();

        $projects = Project::query()
            ->published()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->limit(8)
            ->get();

        $news = News::query()
            ->published()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('content', 'like', $like);
            }))
            ->limit(8)
            ->get();

        $jobs = Job::query()
            ->published()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->limit(8)
            ->get();

        $tenders = Tender::query()
            ->published()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->limit(8)
            ->get();

        $pages = \App\Models\Page::query()
            ->published()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('content', 'like', $like);
            }))
            ->limit(8)
            ->get();

        return view('site.search', compact('q', 'services', 'projects', 'news', 'jobs', 'tenders', 'pages'));
    }

    /**
     * خريطة الموقع XML:
     * تجمع أهم روابط الموقع المنشورة لتسهيل الأرشفة على محركات البحث.
     */
    public function sitemap()
    {
        $urls = collect([
            [
                'loc' => route('home'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('about'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('services'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => route('projects'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => route('news'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('contact'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('careers'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('tenders'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
        ]);

        $urls = $urls
            ->merge(Service::query()->published()->get()->map(fn ($service) => [
                'loc' => route('services.details', $service->slug),
                'lastmod' => optional($service->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]))
            ->merge(Project::query()->published()->get()->map(fn ($project) => [
                'loc' => route('projects.details', $project->slug),
                'lastmod' => optional($project->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]))
            ->merge(News::query()->published()->get()->map(fn ($newsItem) => [
                'loc' => route('news.details', $newsItem->slug),
                'lastmod' => optional($newsItem->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.7',
            ]));

        if (Schema::hasTable('pages')) {
            $urls = $urls->merge(\App\Models\Page::query()->published()->get()->map(fn ($page) => [
                'loc' => route('pages.show', $page->slug),
                'lastmod' => optional($page->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ]));
        }

        if ((bool) Setting::getValue('clients_page_enabled', false) && Schema::hasTable((new Client())->getTable())) {
            $urls->push([
                'loc' => route('clients'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);
        }

        return response()
            ->view('site.sitemap', ['urls' => $urls->values()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * ملف robots.txt ديناميكي:
     * يسمح بأرشفة صفحات الموقع العامة ويمنع صفحات الإدارة والبحث الداخلي.
     */
    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /search',
            'Sitemap: ' . route('sitemap'),
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function siteSettings(): Collection
    {
        // جدول settings: إعدادات عامة (هوية، ألوان، نصوص) تُعرض في القوالب عبر $siteSettings في الـ composer
        return Cache::remember('site:settings:all', now()->addMinutes(10), fn () => Setting::query()->get()->mapWithKeys(fn ($setting) => [$setting->key => $setting->parseValue()]));
    }

    private function mainStats(): array
    {
        // إحصاءات تظهر في صفحات تعريفية، وتعتمد على البيانات المخزنة فعليا في لوحة التحكم.
        return Cache::remember('site:stats:main', now()->addMinutes(10), fn () => [
            'projects' => Project::count(),
            'services' => Service::count(),
            'years' => (int) date('Y') - 2009,
            'jobs' => Job::active()->count(),
        ]);
    }

    private function toLikePattern(string $q): ?string
    {
        if ($q === '') {
            return null;
        }

        // الهروب من المحارف الخاصة حتى لا تؤثر على نتيجة LIKE.
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

        return "%{$escaped}%";
    }
}
