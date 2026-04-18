<?php

/**
 * الغرض من الملف:
 * إدارة تسجيل الدخول الأولي وتسجيل الدخول والخروج لمستخدمي لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\AuthController.
 *
 * المكونات الأساسية:
 * - إنشاء أول مستخدم مشرف أعلى عند الإعداد الأول.
 * - التحقق من بيانات الدخول وتجديد الجلسة.
 *
 * خريطة تدفق البيانات:
 * هذا المتحكم يضبط الوصول إلى لوحة التحكم نفسها،
 * وبالتالي يتحكم بشكل غير مباشر بمن يملك صلاحية تعديل البيانات الظاهرة في الموقع.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * عرض شاشة الدخول أو إعادة التوجيه بحسب حالة المصادقة والإعداد الأولي.
     */
    public function create()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        if (! User::query()->exists()) {
            return redirect()->route('admin.setup');
        }

        return view('admin.auth.login');
    }

    /**
     * عرض شاشة إعداد أول حساب إداري في حال كان النظام جديدا.
     */
    public function setupCreate()
    {
        if (User::query()->exists()) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.setup');
    }

    /**
     * إنشاء أول مستخدم بصلاحية مشرف أعلى وتسجيل دخوله مباشرة.
     */
    public function setupStore(Request $request)
    {
        if (User::query()->exists()) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['is_super_admin'] = true;

        $user = User::query()->create($validated);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    /**
     * معالجة تسجيل الدخول للمستخدمين الحاليين.
     */
    public function store(Request $request)
    {
        if (! User::query()->exists()) {
            return redirect()->route('admin.setup');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'بيانات الدخول غير صحيحة.']);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    /**
     * تسجيل الخروج وإنهاء الجلسة الحالية بأمان.
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

