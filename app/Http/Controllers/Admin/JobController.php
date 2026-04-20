<?php

/**
 * الغرض من الملف:
 * إدارة الوظائف الشاغرة داخل لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\JobController.
 *
 * المكونات الأساسية:
 * - تحويل حقول المتطلبات والمهارات من نص متعدد الأسطر إلى مصفوفات.
 * - التحكم في حالة النشاط لتحديد ظهور الوظيفة في الواجهة.
 *
 * خريطة تدفق البيانات:
 * أي وظيفة تُدار هنا تظهر في صفحة التوظيف بالموقع إذا كانت نشطة
 * ولم يتجاوز تاريخ الإغلاق الخاص بها.
 */
namespace App\Http\Controllers\Admin;

use App\Events\JobSavedForNews;
use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * عرض قائمة الوظائف مع البحث داخل بياناتها الأساسية.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $jobs = Job::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.jobs.index', compact('jobs', 'q'));
    }

    /**
     * عرض نموذج إنشاء وظيفة جديدة.
     */
    public function create()
    {
        return view('admin.jobs.create');
    }

    /**
     * حفظ وظيفة جديدة.
     * المتطلبات والمهارات تُخزن كمصفوفات JSON بعد تحويل الإدخال النصي.
     */
    public function store(Request $request)
    {
        $validated = $this->validateJob($request);
        $validated['is_active'] = $request->boolean('is_active');
        // تحويل النص متعدد الأسطر إلى قوائم منظمة لسهولة العرض في واجهة الوظائف.
        $validated['requirements'] = $this->toArrayFromLines($request->input('requirements'));
        $validated['skills'] = $this->toArrayFromLines($request->input('skills'));

        $job = Job::create($validated);

        // خبر تلقائي للوظائف المفعّلة (is_active) — يُزال عند التعطيل
        event(new JobSavedForNews($job));

        return redirect()->route('admin.jobs.index')->with('success', 'تمت إضافة الوظيفة بنجاح.');
    }

    /**
     * عرض نموذج تعديل وظيفة قائمة.
     */
    public function edit(Job $job)
    {
        return view('admin.jobs.edit', compact('job'));
    }

    /**
     * تحديث وظيفة موجودة مع إعادة بناء قوائم المتطلبات والمهارات.
     */
    public function update(Request $request, Job $job)
    {
        $validated = $this->validateJob($request, $job->id);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['requirements'] = $this->toArrayFromLines($request->input('requirements'));
        $validated['skills'] = $this->toArrayFromLines($request->input('skills'));

        $job->update($validated);

        // تحديث الخبر التلقائي أو إزالته إذا أصبحت الوظيفة غير مفعّلة
        event(new JobSavedForNews($job));

        return redirect()->route('admin.jobs.index')->with('success', 'تم تحديث الوظيفة بنجاح.');
    }

    /**
     * حذف وظيفة من لوحة التحكم، مما يزيلها من صفحة التوظيف.
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return redirect()->route('admin.jobs.index')->with('success', 'تم حذف الوظيفة بنجاح.');
    }

    /**
     * قواعد التحقق لبيانات الوظيفة.
     */
    private function validateJob(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'skills' => ['nullable', 'string'],
            'closing_date' => ['nullable', 'date'],
        ]);
    }

    /**
     * تقسيم النص متعدد الأسطر إلى مصفوفة عناصر.
     * يفيد هذا في حفظ المتطلبات والمهارات كبيانات منظمة.
     */
    private function toArrayFromLines(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

}
