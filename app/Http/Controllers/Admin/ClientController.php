<?php

/**
 * الغرض من الملف:
 * إدارة عملاء الموقع (شعارات وشركاء) وربطهم بمشاريع حقيقية في النظام.
 *
 * التبعية:
 * App\Http\Controllers\Admin\ClientController.
 *
 * المكونات الأساسية:
 * - Client و Project لربط المشاريع عبر client_id.
 * - Setting::setValue لتفعيل/تعطيل صفحة "عملاؤنا" في الواجهة.
 *
 * خريطة تدفق البيانات:
 * أي عميل يُنشر هنا وله مشاريع مرتبطة يظهر في الشريط بالصفحة الرئيسية،
 * بينما رابط القائمة ومسار /clients يخضعان لإعداد clients_page_enabled.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * قائمة العملاء مع إمكانية تفعيل صفحة "عملاؤنا" للزوار.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $clients = Client::query()
            ->withCount('projects')
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            }))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $clientsPageEnabled = (bool) Setting::getValue('clients_page_enabled', false);

        return view('admin.clients.index', compact('clients', 'q', 'clientsPageEnabled'));
    }

    /**
     * تفعيل أو تعطيل صفحة "عملاؤنا" والرابط في القائمة الرئيسية.
     */
    public function toggleClientsPage(\Illuminate\Http\Request $request)
    {
        Setting::setValue('clients_page_enabled', $request->boolean('enabled') ? '1' : '0', 'boolean');

        return redirect()
            ->route('admin.clients.index')
            ->with('success', $request->boolean('enabled') ? 'تم تفعيل صفحة عملاؤنا للزوار.' : 'تم إخفاء صفحة عملاؤنا عن الزوار.');
    }

    /**
     * نموذج إنشاء عميل جديد مع اختيار مشاريع يجب أن يكون عددها واحدا على الأقل.
     */
    public function create()
    {
        $projects = Project::query()->orderBy('title')->get(['id', 'title', 'client_id']);

        return view('admin.clients.create', compact('projects'));
    }

    /**
     * حفظ عميل جديد ثم ربط المشاريع المختارة به.
     */
    public function store(StoreClientRequest $request)
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $projectIds = array_values(array_unique(array_map('intval', $validated['project_ids'] ?? [])));
        unset($validated['project_ids']);

        // منع ربط نفس المشروع بأكثر من عميل في وقت واحد.
        $conflict = Project::query()
            ->whereIn('id', $projectIds)
            ->whereNotNull('client_id')
            ->exists();
        if ($conflict) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'project_ids' => 'أحد المشاريع المختارة مرتبط بالفعل بعميل آخر؛ أزل التعارض أو عدّل العميل الآخر أولا.',
            ]);
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('clients', 'public');
        }

        $client = Client::create($validated);
        $this->syncClientProjects($client, $projectIds);

        return redirect()->route('admin.clients.index')->with('success', 'تمت إضافة العميل وربط المشاريع بنجاح.');
    }

    /**
     * نموذج تعديل عميل مع عرض المشاريع المتاحة والمحددة مسبقا.
     */
    public function edit(Client $client)
    {
        $projects = Project::query()->orderBy('title')->get(['id', 'title', 'client_id']);
        $selectedProjectIds = $client->projects()->pluck('id')->all();

        return view('admin.clients.edit', compact('client', 'projects', 'selectedProjectIds'));
    }

    /**
     * تحديث بيانات العميل وإعادة ربط المشاريع وفق القائدة: مشروع واحد على الأقل.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $projectIds = array_values(array_unique(array_map('intval', $validated['project_ids'] ?? [])));
        unset($validated['project_ids']);

        // منع ربط نفس المشروع بأكثر من عميل في وقت واحد (مع السماح للعميل الحالي).
        $conflict = Project::query()
            ->whereIn('id', $projectIds)
            ->whereNotNull('client_id')
            ->where('client_id', '!=', $client->id)
            ->exists();
        if ($conflict) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'project_ids' => 'أحد المشاريع المختارة مرتبط بالفعل بعميل آخر؛ أزل التعارض أو عدّل العميل الآخر أولا.',
            ]);
        }

        if ($request->hasFile('logo')) {
            if ($client->logo) {
                Storage::disk('public')->delete($client->logo);
            }
            $validated['logo'] = $request->file('logo')->store('clients', 'public');
        }

        $client->update($validated);
        $this->syncClientProjects($client, $projectIds);

        return redirect()->route('admin.clients.index')->with('success', 'تم تحديث العميل بنجاح.');
    }

    /**
     * حذف عميل مع فك ارتباط المشاريع دون حذف المشاريع نفسها.
     */
    public function destroy(Client $client)
    {
        Project::query()->where('client_id', $client->id)->update(['client_id' => null]);

        if ($client->logo) {
            Storage::disk('public')->delete($client->logo);
        }

        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'تم حذف العميل وفك ربط المشاريع.');
    }

    /**
     * إعادة تعيين مشاريع هذا العميل ثم ربط المجموعة الجديدة.
     */
    private function syncClientProjects(Client $client, array $projectIds): void
    {
        Project::query()->where('client_id', $client->id)->update(['client_id' => null]);

        Project::query()
            ->whereIn('id', $projectIds)
            ->update(['client_id' => $client->id]);
    }
}
