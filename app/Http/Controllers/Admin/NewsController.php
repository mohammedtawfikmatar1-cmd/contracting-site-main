<?php

/**
 * الغرض من الملف:
 * إدارة الأخبار والمستجدات المنشورة من لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\NewsController.
 *
 * المكونات الأساسية:
 * - رفع صور الأخبار.
 * - دعم الحقول متعددة اللغة.
 * - التحكم في حالة النشر وتاريخ النشر.
 *
 * خريطة تدفق البيانات:
 * الأخبار التي تُدار هنا تظهر في صفحة الأخبار بالواجهة الأمامية،
 * ويمكن أن تظهر أيضا ضمن أقسام مرتبطة حسب تصميم العرض.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsRequest;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * عرض قائمة الأخبار مع البحث.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $news = News::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.news.index', compact('news', 'q'));
    }

    /**
     * عرض نموذج إنشاء خبر جديد.
     */
    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * حفظ خبر جديد مع معالجة صورة الخبر ووقت النشر.
     */
    public function store(StoreNewsRequest $request)
    {
        $validated = $request->validated();
        $validated = $this->normalizeTranslatables($validated, ['title', 'content', 'category']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $request->input('published_at');

        if ($request->hasFile('image')) {
            // رفع الصورة الرئيسية للخبر لاستخدامها في البطاقات وصفحة التفاصيل.
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        /*
         * أخبار يدوية من شاشة "الأخبار" في الإدارة:
         * لا نربطها بمشروع/مناقصة/وظيفة (newsable = null).
         * أمّا الأخبار التلقائية فتُنشأ من NewsAutomationService وتملأ newsable_type / newsable_id.
         */
        $validated['newsable_type'] = null;
        $validated['newsable_id'] = null;

        News::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'تمت إضافة الخبر بنجاح.');
    }

    /**
     * عرض نموذج تعديل خبر قائم.
     */
    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    /**
     * تحديث خبر موجود واستبدال صورته عند رفع ملف جديد.
     */
    public function update(UpdateNewsRequest $request, News $news)
    {
        $validated = $request->validated();
        $validated = $this->normalizeTranslatables($validated, ['title', 'content', 'category']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $request->input('published_at');

        if ($request->hasFile('image')) {
            if ($news->image) {
                // حذف الصورة القديمة قبل تخزين البديل.
                Storage::disk('public')->delete($news->image);
            }
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'تم تحديث الخبر بنجاح.');
    }

    /**
     * حذف خبر من الإدارة، وبالتالي إزالته من الواجهة.
     */
    public function destroy(News $news)
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'تم حذف الخبر بنجاح.');
    }

    /**
     * تجهيز الحقول المترجمة لتُحفظ بصيغة متوافقة مع HasTranslations.
     */
    private function normalizeTranslatables(array $validated, array $fields): array
    {
        foreach ($fields as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            if (is_array($validated[$field])) {
                $validated[$field] = array_filter($validated[$field], fn ($v) => $v !== null && $v !== '');
                continue;
            }

            $validated[$field] = ['ar' => $validated[$field]];
        }

        return $validated;
    }
}
