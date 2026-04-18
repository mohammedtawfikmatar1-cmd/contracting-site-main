<?php

namespace App\Listeners;

use App\Events\ContactRequestSubmitted;
use App\Models\User;
use App\Notifications\NewContactRequestNotification;

class SendAdminContactNotification
{
    public function handle(ContactRequestSubmitted $event): void
    {
        User::query()->each(function (User $user) use ($event) {
            $user->notify(new NewContactRequestNotification($event->contact));
        });
    }
}
