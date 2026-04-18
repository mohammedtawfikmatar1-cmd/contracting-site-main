<?php

/**
 * الغرض من الملف:
 * إدارة الإشعارات الداخلية الخاصة بمستخدمي لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\NotificationController.
 *
 * المكونات الأساسية:
 * - قراءة إشعارات المستخدم الحالي.
 * - تعليم إشعار واحد أو جميع الإشعارات كمقروءة.
 *
 * خريطة تدفق البيانات:
 * الإشعارات تُنشأ من أحداث النظام مثل استقبال طلبات التواصل،
 * ثم تظهر هنا داخل لوحة التحكم ليطّلع عليها المشرف.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationController extends Controller
{
    /**
     * عرض قائمة الإشعارات للمستخدم الحالي مع تقسيم الصفحات.
     */
    public function index()
    {
        $user = auth()->user();

        $notifications = $user?->notifications()->latest()->paginate(20)
            ?? new LengthAwarePaginator([], 0, 20);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * تعليم إشعار واحد كمقروء.
     */
    public function markAsRead(string $id)
    {
        $user = auth()->user();

        if ($user) {
            $notification = $user->unreadNotifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }

        return back()->with('success', 'تم تعليم الإشعار كمقروء.');
    }

    /**
     * تعليم جميع إشعارات المستخدم الحالي كمقروءة دفعة واحدة.
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }
}
