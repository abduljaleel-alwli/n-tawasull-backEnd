<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject(
                settings('contact.email_subject', __('New contact message'))
            )
            ->view('emails.contact-message', [
                'contact' => $this->contactMessage,
            ])->with([
                    'messageData' => $this->contactMessage,
                    'downloadUrl' => $this->contactMessage->attachment_path
                        ? route('contact.attachments.download', $this->contactMessage->id)
                        : null,
                ]);
    }
}
