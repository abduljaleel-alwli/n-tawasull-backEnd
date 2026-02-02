<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContactMessage $message
    ) {}

    /**
     * Notification channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data stored in database.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'contact_message',
            'title' => __('New contact message'),
            'message' => __('A new message was received from :name', [
                'name' => $this->message->name,
            ]),
            'contact_message_id' => $this->message->id,
        ];
    }
}
