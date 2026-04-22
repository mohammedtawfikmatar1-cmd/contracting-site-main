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
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    /**
     * عرض قائمة الإشعارات للمستخدم الحالي مع تقسيم الصفحات.
     */
    public function index()
    {
        $user = auth()->user();

        if (! $user) {
            $notifications = new LengthAwarePaginator([], 0, 20);

            return view('admin.notifications.index', compact('notifications'));
        }

        // نعرض الإشعارات بشكل فريد حتى لا يرى المسؤول نسخا مكررة لنفس الطلب.
        $allNotifications = $user->notifications()->latest()->get();
        $uniqueNotifications = $this->uniqueNotifications($allNotifications)->values();

        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $uniqueNotifications->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $notifications = new LengthAwarePaginator(
            $currentItems,
            $uniqueNotifications->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * تعليم إشعار واحد كمقروء.
     */
    public function markAsRead(string $id)
    {
        $user = auth()->user();

        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                // عند تعليم إشعار كمقروء نعلّم أيضا أي نسخ مكررة مرتبطة بنفس الطلب.
                $this->relatedNotifications($user->notifications()->get(), $notification)
                    ->whereNull('read_at')
                    ->each
                    ->markAsRead();
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
            $this->uniqueNotifications($user->unreadNotifications()->get())
                ->each
                ->markAsRead();
        }

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }

    /**
     * إزالة التكرار من مجموعة الإشعارات بحسب الطلب المرتبط ونوع الإشعار.
     */
    private function uniqueNotifications(Collection $notifications): Collection
    {
        return $notifications->unique(fn ($notification) => $this->notificationFingerprint($notification));
    }

    /**
     * جلب الإشعارات المرتبطة بنفس البصمة حتى نتعامل مع النسخ المكررة دفعة واحدة.
     */
    private function relatedNotifications(Collection $notifications, $target): Collection
    {
        $fingerprint = $this->notificationFingerprint($target);

        return $notifications->filter(fn ($notification) => $this->notificationFingerprint($notification) === $fingerprint);
    }

    /**
     * إنشاء بصمة موحدة للإشعار تعتمد على الطلب المرتبط بدلا من المعرّف العشوائي للإشعار.
     */
    private function notificationFingerprint($notification): string
    {
        return ($notification->data['contact_id'] ?? $notification->id)
            . '|' . ($notification->type ?? '')
            . '|' . ($notification->data['url'] ?? '');
    }
}
