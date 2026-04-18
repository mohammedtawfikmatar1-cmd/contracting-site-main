<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewContactRequestNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Contact $contact)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'طلب جديد من الموقع',
            'message' => sprintf('%s أرسل طلبًا جديدًا (%s).', $this->contact->full_name, $this->contact->request_type),
            'contact_id' => $this->contact->id,
            'url' => route('admin.contacts.show', $this->contact),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
