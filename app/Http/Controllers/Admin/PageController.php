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
use App\Models\Page;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $validated = $this->validatePage($request);
        $validated = $this->normalizeTranslatables($validated, ['title', 'content']);
        $validated['is_published'] = $request->boolean('is_published');

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
    public function update(Request $request, Page $page)
    {
        $validated = $this->validatePage($request, $page->id);
        $validated = $this->normalizeTranslatables($validated, ['title', 'content']);
        $validated['is_published'] = $request->boolean('is_published');

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
     * قواعد التحقق الخاصة بالصفحات.
     */
    private function validatePage(Request $request, ?int $ignoreId = null): array
    {
        // إيقاف نظام الإدخال متعدد اللغة حاليا والإبقاء على العربية فقط.
        $enabled = false;
        if ($enabled) {
            return $request->validate([
                'title.ar' => ['required', 'string', 'max:255'],
                'title.en' => ['nullable', 'string', 'max:255'],
                'content.ar' => ['nullable', 'string'],
                'content.en' => ['nullable', 'string'],
                'template' => ['required', 'string', 'max:255'],
            ]);
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'template' => ['required', 'string', 'max:255'],
        ]);
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
