<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserActionNotification extends Notification
{
    public function __construct(
        protected string $type,
        protected array $payload = []
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => match ($this->type) {
                'user.created' => __('User created'),
                'user.updated' => __('User updated'),
                'user.deleted' => __('User deleted'),
                'user.status_changed' => __('User status changed'),
                'user.role_changed' => __('User role changed'),
                default => __('System notification'),
            },

            'message' => match ($this->type) {
                'user.created' => __('A new user was created: :email', [
                    'email' => $this->payload['email'] ?? '',
                ]),
                'user.deleted' => __('User deleted: :email', [
                    'email' => $this->payload['email'] ?? '',
                ]),
                default => __('An action was performed in the system'),
            },

            'meta' => $this->payload,
            'type' => $this->type,
        ];
    }
}

