<?php

/**
 * الغرض من الملف:
 * إدارة الصفحات التعريفية والثابتة داخل لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\PageController.
 *
 * المكونات الأساسية:
 * - إنشاء وتحديث صفحات تعتمد على slug و template.
 * - دعم الحقول متعددة اللغة.
 *
 * خريطة تدفق البيانات:
 * الصفحات التي تُنشأ هنا تُعرض في الواجهة عبر `SiteController::page()`
 * ويحدد الحقل `template` القالب الذي ستظهر به الصفحة إن كان متوفرا.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * عرض قائمة الصفحات مع البحث.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $pages = Page::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.index', compact('pages', 'q'));
    }

    /**
     * عرض نموذج إنشاء صفحة جديدة.
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * حفظ صفحة جديدة مع تحديد حالة النشر.
     */
    public function store(StorePageRequest $request)
    {
        $validated = $request->validated();
        $validated = $this->normalizeTranslatables($validated, ['title', 'content']);
        $validated['is_published'] = $request->boolean('is_published');
        // إلغاء "نوع القالب" نهائياً: نعتمد عرض الصفحة القياسي فقط.
        $validated['template'] = null;

        Page::create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'تم إنشاء الصفحة بنجاح.');
    }

    /**
     * عرض نموذج تعديل صفحة قائمة.
     */
    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * تحديث صفحة موجودة.
     */
    public function update(UpdatePageRequest $request, Page $page)
    {
        $validated = $request->validated();
        $validated = $this->normalizeTranslatables($validated, ['title', 'content']);
        $validated['is_published'] = $request->boolean('is_published');
        // إلغاء "نوع القالب" نهائياً: نعتمد عرض الصفحة القياسي فقط.
        $validated['template'] = null;

        $page->update($validated);

        return redirect()->route('admin.pages.index')->with('success', 'تم تحديث الصفحة بنجاح.');
    }

    /**
     * حذف صفحة من لوحة التحكم وإزالتها من الواجهة.
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'تم حذف الصفحة بنجاح.');
    }

    /**
     * توحيد شكل الحقول متعددة اللغة قبل التخزين.
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
