<?php

/**
 * الغرض من الملف:
 * إدارة CRUD الخاصة بالمشاريع داخل لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\ProjectController.
 *
 * المكونات الأساسية:
 * - Project و Service لإدارة الربط بين المشروع والخدمة.
 * - Storage لمعالجة رفع الصور واستبدالها أو حذفها.
 * - Setting لتحديد ما إذا كان الإدخال متعدد اللغات مفعلا.
 *
 * خريطة تدفق البيانات:
 * أي مشروع يُنشأ أو يُحدّث هنا يظهر لاحقا في صفحات المشاريع بالواجهة الأمامية
 * عند تفعيل حالة النشر `is_published`.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\ProjectSavedForNews;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * عرض قائمة المشاريع في لوحة التحكم مع البحث النصي.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $projects = Project::query()
            ->with('service')
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', compact('projects', 'q'));
    }

    /**
     * عرض نموذج إنشاء مشروع جديد مع تحميل قائمة الخدمات المتاحة للربط.
     */
    public function create()
    {
        $services = Service::query()->orderBy('title')->get();
        $clients = Client::query()->orderBy('name')->get();

        return view('admin.projects.create', compact('services', 'clients'));
    }

    /**
     * حفظ مشروع جديد.
     * يتضمن هذا التدفق التحقق من صحة البيانات، تهيئة الحقول متعددة اللغة،
     * ثم رفع الصورة الرئيسية إن وُجدت، وأخيرا إنشاء السجل.
     */
    public function store(Request $request)
    {
        $validated = $this->validateProject($request);
        $validated = $this->normalizeTranslatables($validated, ['title', 'description', 'category', 'location']);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            // حفظ صورة المشروع داخل التخزين العام ليتم عرضها لاحقا في الواجهة.
            $validated['image'] = $request->file('image')->store('projects', 'public');
        }

        $project = Project::create($validated);
        event(new ProjectSavedForNews($project));

        return redirect()->route('admin.projects.index')->with('success', 'تمت إضافة المشروع بنجاح.');
    }

    /**
     * عرض نموذج تعديل مشروع موجود.
     */
    public function edit(Project $project)
    {
        $services = Service::query()->orderBy('title')->get();
        $clients = Client::query()->orderBy('name')->get();

        return view('admin.projects.edit', compact('project', 'services', 'clients'));
    }

    /**
     * تحديث بيانات مشروع قائم.
     * عند رفع صورة جديدة يتم حذف الصورة القديمة أولًا لتجنب بقاء ملفات غير مستخدمة.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $this->validateProject($request, $project->id);
        $validated = $this->normalizeTranslatables($validated, ['title', 'description', 'category', 'location']);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            if ($project->image) {
                // تنظيف التخزين من الصورة السابقة قبل استبدالها.
                Storage::disk('public')->delete($project->image);
            }
            $validated['image'] = $request->file('image')->store('projects', 'public');
        }

        $project->update($validated);
        event(new ProjectSavedForNews($project));

        return redirect()->route('admin.projects.index')->with('success', 'تم تحديث المشروع بنجاح.');
    }

    /**
     * حذف مشروع من لوحة التحكم.
     * حذف المشروع يؤدي إلى اختفائه من الواجهة، مع إزالة صورته الرئيسية من التخزين.
     */
    public function destroy(Project $project)
    {
        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'تم حذف المشروع بنجاح.');
    }

    /**
     * قواعد التحقق الخاصة بالمشروع.
     * تتبدل القواعد حسب تفعيل الإدخال متعدد اللغات من الإعدادات العامة.
     */
    private function validateProject(Request $request, ?int $ignoreId = null): array
    {
        // إيقاف نظام الإدخال متعدد اللغة حاليا والإبقاء على العربية فقط.
        $enabled = false;

        if ($enabled) {
            return $request->validate([
                'service_id' => ['required', 'exists:services,id'],
                'client_id' => ['nullable', 'exists:clients,id'],
                'title.ar' => ['required', 'string', 'max:255'],
                'title.en' => ['nullable', 'string', 'max:255'],
                'description.ar' => ['nullable', 'string'],
                'description.en' => ['nullable', 'string'],
                'category.ar' => ['nullable', 'string', 'max:255'],
                'category.en' => ['nullable', 'string', 'max:255'],
                'location.ar' => ['nullable', 'string', 'max:255'],
                'location.en' => ['nullable', 'string', 'max:255'],
                'image' => ['nullable', 'image', 'max:4096'],
            ]);
        }

        return $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    /**
     * توحيد شكل الحقول القابلة للترجمة قبل الحفظ.
     * إذا كانت اللغة الواحدة فقط مفعلة، تُخزن القيمة داخل المفتاح العربي `ar`.
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
