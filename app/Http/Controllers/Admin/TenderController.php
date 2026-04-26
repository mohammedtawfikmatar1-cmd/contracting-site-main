<?php

/**
 * الغرض من الملف:
 * إدارة المناقصات داخل لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\TenderController.
 *
 * المكونات الأساسية:
 * - التحقق من حالة المناقصة وتاريخ الإغلاق.
 * - التحكم في نشر المناقصة لتظهر أو تختفي من الواجهة.
 *
 * خريطة تدفق البيانات:
 * المناقصات التي تُنشأ أو تُعدّل هنا تظهر في صفحة المناقصات الأمامية
 * فقط عند تفعيل حالة النشر.
 */
namespace App\Http\Controllers\Admin;

use App\Events\TenderSavedForNews;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTenderRequest;
use App\Http\Requests\Admin\UpdateTenderRequest;
use App\Models\Tender;

class TenderController extends Controller
{
    /**
     * عرض قائمة المناقصات مع إمكانية البحث النصي.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $tenders = Tender::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('work_type', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.tenders.index', compact('tenders', 'q'));
    }

    /**
     * عرض نموذج إنشاء مناقصة.
     */
    public function create()
    {
        return view('admin.tenders.create');
    }

    /**
     * حفظ مناقصة جديدة.
     * بعد الحفظ يمكن أن تنعكس مباشرة في الموقع إذا كانت حالة النشر مفعلة.
     */
    public function store(StoreTenderRequest $request)
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');

        $tender = Tender::create($validated);

        // مزامنة خبر تلقائي مرتبط بهذه المناقصة (أو حذفه إن لم تكن منشورة)
        event(new TenderSavedForNews($tender));

        return redirect()->route('admin.tenders.index')->with('success', 'تمت إضافة المناقصة بنجاح.');
    }

    /**
     * عرض نموذج تعديل مناقصة قائمة.
     */
    public function edit(Tender $tender)
    {
        return view('admin.tenders.edit', compact('tender'));
    }

    /**
     * تحديث بيانات المناقصة وحالة ظهورها للزوار.
     */
    public function update(UpdateTenderRequest $request, Tender $tender)
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');

        $tender->update($validated);

        // إعادة مزامنة الخبر التلقائي بعد أي تعديل على المناقصة أو حالة النشر
        event(new TenderSavedForNews($tender));

        return redirect()->route('admin.tenders.index')->with('success', 'تم تحديث المناقصة بنجاح.');
    }

    /**
     * حذف المناقصة من الإدارة، وبالتالي إزالتها من صفحة المناقصات الأمامية.
     */
    public function destroy(Tender $tender)
    {
        $tender->delete();

        return redirect()->route('admin.tenders.index')->with('success', 'تم حذف المناقصة بنجاح.');
    }

}
