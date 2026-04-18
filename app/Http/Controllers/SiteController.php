<?php

/**
 * الغرض من الملف:
 * إدارة جميع صفحات الواجهة الأمامية وتجميع البيانات المنشورة القادمة من لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\SiteController.
 *
 * المكونات الأساسية:
 * - استرجاع الخدمات والمشاريع والأخبار والوظائف والمناقصات والصفحات.
 * - استهلاك إعدادات الموقع العامة المخزنة في جدول settings.
 *
 * خريطة تدفق البيانات:
 * هذا المتحكم هو نقطة العرض النهائية لبيانات الإدارة؛ أي محتوى يُنشر من لوحة التحكم
 * يمر عبر Queries هنا ثم يُرسل إلى قوالب Blade في الواجهة الأمامية.
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
use Illuminate\Support\Facades\Schema;

class SiteController extends Controller
{
    /**
     * الصفحة الرئيسية:
     * تسحب أبرز الخدمات والمشاريع وآخر الأخبار المنشورة من لوحة التحكم.
     */
    public function home()
    {
        $services = Service::query()->published()->limit(8)->get();
        $projects = Project::query()->published()->latest()->limit(6)->get();
        $news = News::query()->published()->latest('published_at')->limit(3)->get();

        $homeClients = collect();
        if (Schema::hasTable((new Client())->getTable())) {
            $homeClients = Client::query()
                ->published()
                ->whereHas('projects', fn ($q) => $q->published())
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

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

        $clients = Client::query()
            ->published()
            ->whereHas('projects', fn ($q) => $q->published())
            ->with(['projects' => fn ($q) => $q->published()->latest()->limit(12)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

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
        $services = Service::query()->published()->get();

        return view('site.services', compact('services'));
    }

    /**
     * صفحة تفاصيل الخدمة.
     * يتم فيها أيضا جلب مشاريع مرتبطة بهذه الخدمة لتوضيح الأعمال المنفذة تحت نفس التصنيف.
     */
    public function serviceDetails(string $slug)
    {
        $service = Service::query()->where('slug', $slug)->firstOrFail();
        $relatedProjects = Project::query()
            ->where('service_id', $service->id)
            ->published()
            ->latest()
            ->limit(4)
            ->get();

        return view('site.sub-pages.service-details', compact('service', 'relatedProjects'));
    }

    /**
     * صفحة أرشيف المشاريع المنشورة مع تقسيم الصفحات.
     */
    public function projects()
    {
        $projects = Project::query()->published()->latest()->paginate(9);

        return view('site.projects', compact('projects'));
    }

    /**
     * صفحة تفاصيل مشروع واحد مع اقتراح مشاريع مشابهة من نفس الخدمة.
     */
    public function projectDetails(string $slug)
    {
        $project = Project::query()->with('service')->where('slug', $slug)->firstOrFail();
        $relatedProjects = Project::query()
            ->where('id', '!=', $project->id)
            ->when($project->service_id, fn ($q) => $q->where('service_id', $project->service_id))
            ->published()
            ->latest()
            ->limit(3)
            ->get();

        return view('site.sub-pages.project-details', compact('project', 'relatedProjects'));
    }

    /**
     * عرض قائمة الأخبار المنشورة للزوار.
     */
    public function news()
    {
        $news = News::query()->published()->latest('published_at')->paginate(9);

        return view('site.news', compact('news'));
    }

    /**
     * صفحة تفاصيل الخبر مع أخبار ذات صلة من نفس التصنيف إن وجد.
     */
    public function newsDetails(string $slug)
    {
        $newsItem = News::query()->where('slug', $slug)->firstOrFail();
        $relatedNews = News::query()
            ->where('id', '!=', $newsItem->id)
            ->when($newsItem->category, fn ($q) => $q->where('category', $newsItem->category))
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('site.sub-pages.news-details', compact('newsItem', 'relatedNews'));
    }

    /**
     * صفحة المناقصات المنشورة القادمة من قسم المناقصات في الإدارة.
     */
    public function tenders()
    {
        $tenders = Tender::query()->published()->latest('closing_date')->paginate(10);

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
        $jobs = Job::query()->published()->paginate(10);

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
            'services' => Service::query()->published()->orderBy('title')->get(),
        ]);
    }

    /**
     * عرض صفحة ديناميكية من جدول الصفحات مع محاولة اختيار قالب مخصص إن كان موجودا.
     */
    public function page(string $slug)
    {
        $page = \App\Models\Page::query()->published()->where('slug', $slug)->firstOrFail();
        $viewName = 'site.page';

        if (!empty($page->template)) {
            $candidate = 'site.pages.' . $page->template;
            if (view()->exists($candidate)) {
                // تمكين الإدارة من تغيير طريقة عرض الصفحة عبر اختيار template مناسب.
                $viewName = $candidate;
            }
        }

        return view($viewName, compact('page'));
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

    private function siteSettings(): Collection
    {
        // تحويل الإعدادات إلى خريطة key => parsed value ليسهل استهلاكها في الواجهة.
        return Setting::query()->get()->mapWithKeys(fn ($setting) => [$setting->key => $setting->parseValue()]);
    }

    private function mainStats(): array
    {
        // إحصاءات تظهر في صفحات تعريفية، وتعتمد على البيانات المخزنة فعليا في لوحة التحكم.
        return [
            'projects' => Project::count(),
            'services' => Service::count(),
            'years' => (int) date('Y') - 2009,
            'jobs' => Job::active()->count(),
        ];
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
