<?php

/**
 * الغرض من الملف:
 * توفير بحث موحد داخل لوحة التحكم عبر أكثر من كيان.
 *
 * التبعية:
 * App\Http\Controllers\Admin\SearchController.
 *
 * المكونات الأساسية:
 * - البحث في المشاريع والخدمات والأخبار والصفحات والمناقصات والوظائف والرسائل.
 * - إعادة النتائج في شاشة واحدة لتسريع الوصول الإداري.
 *
 * خريطة تدفق البيانات:
 * هذا المتحكم لا يغيّر البيانات، لكنه يساعد الإدارة على الوصول السريع
 * إلى المحتوى الذي سينعكس في الواجهة أو الذي يحتاج متابعة داخل النظام.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Job;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\Tender;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * تنفيذ البحث الشامل داخل لوحة الإدارة.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $q = trim((string) ($validated['q'] ?? ''));
        // إعداد نمط LIKE آمن قبل تطبيقه على مختلف الجداول.
        $like = $this->toLikePattern($q);

        $projects = Project::query()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->latest()
            ->limit(6)
            ->get();

        $services = Service::query()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->latest()
            ->limit(6)
            ->get();

        $news = News::query()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('content', 'like', $like);
            }))
            ->latest()
            ->limit(6)
            ->get();

        $pages = Page::query()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('content', 'like', $like);
            }))
            ->latest()
            ->limit(6)
            ->get();

        $tenders = Tender::query()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->latest()
            ->limit(6)
            ->get();

        $jobs = Job::query()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)->orWhere('description', 'like', $like);
            }))
            ->latest()
            ->limit(6)
            ->get();

        $contacts = Contact::query()
            ->when($like !== null, fn ($query) => $query->where(function ($sub) use ($like) {
                $sub->where('full_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('message', 'like', $like);
            }))
            ->latest()
            ->limit(6)
            ->get();

        return view('admin.search.index', compact('q', 'projects', 'services', 'news', 'pages', 'tenders', 'jobs', 'contacts'));
    }

    /**
     * الهروب من المحارف الخاصة في استعلام LIKE.
     */
    private function toLikePattern(string $q): ?string
    {
        if ($q === '') {
            return null;
        }

        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

        return "%{$escaped}%";
    }
}
