<?php

namespace App\Actions\Contact;

use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ReplyToContactMessage
{
    public function execute(ContactMessage $message, string $reply): void
    {
        Mail::to($message->email)->send(
            new ContactReplyMail($message, $reply)
        );

        $message->update([
            'replied_at' => now(),
        ]);
    }
}
