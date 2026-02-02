<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ContactMessageReplied extends Notification
{
    use Queueable;

    public function __construct(
        public ContactMessage $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'contact_reply',
            'channel' => 'mail',
            'title' => __('Reply sent'),
            'body' => __('A reply was sent to :name (:email)', [
                'name' => $this->message->name,
                'email' => $this->message->email,
            ]),
            'url' => route('admin.contact-messages', [
                'message' => $this->message->id,
            ]),
            'meta' => [
                'name' => $this->message->name,
                'email' => $this->message->email,
                'message_id' => $this->message->id,
                'preview' => str($this->message->message)->limit(120),
            ],
        ];
    }


}
