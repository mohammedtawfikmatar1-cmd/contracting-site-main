<?php

/**
 * الغرض من الملف:
 * إدارة الخدمات في لوحة التحكم من حيث الإنشاء والتعديل والحذف والنشر.
 *
 * التبعية:
 * App\Http\Controllers\Admin\ServiceController.
 *
 * المكونات الأساسية:
 * - Service لحفظ بيانات الخدمة.
 * - Storage لرفع صورة الخدمة ومعالجة الاستبدال.
 * - Setting لتحديد نمط الإدخال متعدد اللغات.
 *
 * خريطة تدفق البيانات:
 * البيانات التي تُدار هنا تظهر مباشرة في صفحة الخدمات بالواجهة،
 * وتُستخدم أيضا كمرجع لربط المشاريع وتصنيفها.
 */
namespace App\Http\Controllers\Admin;

use App\Events\ServiceSavedForNews;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * قائمة الخدمات في الإدارة مع دعم البحث.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $services = Service::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.services.index', compact('services', 'q'));
    }

    /**
     * عرض نموذج إنشاء خدمة جديدة.
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * حفظ خدمة جديدة بعد التحقق من البيانات ورفع الصورة إن وُجدت.
     */
    public function store(StoreServiceRequest $request)
    {
        $validated = $request->validated();
        $validated = $this->normalizeTranslatables($validated, ['title', 'overview', 'description']);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            // حفظ صورة الخدمة لاستخدامها في بطاقات وقوالب الواجهة.
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        $service = Service::create($validated);
        event(new ServiceSavedForNews($service));

        return redirect()->route('admin.services.index')->with('success', 'تمت إضافة الخدمة بنجاح.');
    }

    /**
     * عرض نموذج تعديل خدمة موجودة.
     */
    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    /**
     * تحديث الخدمة.
     * عند استبدال الصورة يتم حذف الملف القديم للحفاظ على نظافة التخزين.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $validated = $request->validated();
        $validated = $this->normalizeTranslatables($validated, ['title', 'overview', 'description']);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            if ($service->image) {
                // حذف الصورة السابقة قبل حفظ الصورة الجديدة.
                Storage::disk('public')->delete($service->image);
            }
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($validated);
        $service->refresh();
        event(new ServiceSavedForNews($service));

        return redirect()->route('admin.services.index')->with('success', 'تم تحديث الخدمة بنجاح.');
    }

    /**
     * حذف خدمة من لوحة التحكم.
     * يؤدي الحذف إلى اختفائها من الواجهة، وقد يؤثر على المشاريع المرتبطة بحسب قيود قاعدة البيانات.
     */
    public function destroy(Service $service)
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'تم حذف الخدمة بنجاح.');
    }

    /**
     * توحيد بنية الحقول المترجمة قبل الإرسال إلى الموديل.
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
