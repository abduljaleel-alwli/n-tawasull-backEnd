<?php

namespace App\Actions\Contact;

use App\Models\ContactMessage;

class StoreContactMessage
{
    /**
     * Store a new contact message.
     */
    public function execute(array $data, ?string $ipAddress = null): ContactMessage
    {
        // Public action – no auth required
        return ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'ip_address' => $ipAddress,
        ]);
    }
}
