<?php

/**
 * الغرض من الملف:
 * إدارة مستخدمي لوحة التحكم وصلاحياتهم الأساسية.
 *
 * التبعية:
 * App\Http\Controllers\Admin\UserController.
 *
 * المكونات الأساسية:
 * - إنشاء وتعديل وحذف المستخدمين.
 * - التحكم في صلاحية المشرف الأعلى.
 *
 * خريطة تدفق البيانات:
 * هذا المتحكم لا يغيّر محتوى الموقع مباشرة، لكنه يحدد من يملك صلاحية
 * إدارة البيانات التي تظهر في الواجهة الأمامية.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * عرض قائمة المستخدمين الإداريين.
     */
    public function index()
    {
        $users = User::query()->orderByDesc('id')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * عرض نموذج إنشاء مستخدم جديد.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * حفظ مستخدم جديد مع تحديد مستوى الصلاحية.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        $validated['is_super_admin'] = $request->boolean('is_super_admin');

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'تمت إضافة المستخدم بنجاح.');
    }

    /**
     * عرض نموذج تعديل مستخدم موجود.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * تحديث بيانات المستخدم.
     * إذا تُركت كلمة المرور فارغة فلن يتم تغييرها.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        $validated['is_super_admin'] = $request->boolean('is_super_admin');

        if (($validated['password'] ?? null) === null || $validated['password'] === '') {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    /**
     * حذف مستخدم من لوحة التحكم.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }
}

