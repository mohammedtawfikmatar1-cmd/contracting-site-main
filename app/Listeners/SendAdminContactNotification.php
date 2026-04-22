<?php

namespace App\Listeners;

use App\Events\ContactRequestSubmitted;
use App\Models\User;
use App\Notifications\NewContactRequestNotification;

/**
 * عند وصول طلب تواصل من الموقع:
 *
 * 1) ContactRequestController يحفظ السجل في جدول contacts
 * 2) يُطلق الحدث ContactRequestSubmitted
 * 3) هذا المستمع يرسل إشعارًا داخل النظام لكل مستخدم (User) مسجّل
 *
 * بهذه الطريقة تظهر تنبيهات في لوحة التحكم دون أن يكون المتحكم مسؤولًا عن تفاصيل الإشعار.
 */
class SendAdminContactNotification
{
    public function handle(ContactRequestSubmitted $event): void
    {
        User::query()->each(function (User $user) use ($event) {
            // منع إنشاء إشعار مكرر لنفس الطلب ونفس المستخدم إذا كان موجودا مسبقا.
            $alreadyExists = $user->notifications()
                ->where('type', NewContactRequestNotification::class)
                ->where('data->contact_id', $event->contact->id)
                ->exists();

            if ($alreadyExists) {
                return;
            }

            $user->notify(new NewContactRequestNotification($event->contact));
        });
    }
}
