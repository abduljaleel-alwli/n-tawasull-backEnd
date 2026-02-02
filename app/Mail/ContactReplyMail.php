<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Mailable;


class ContactReplyMail extends Mailable
{
    public function __construct(
        public ContactMessage $contact,
        public string $reply
    ) {
    }

    public function build(): static
    {
        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);

        $logoPath = $settings->get('branding.logo');

        $logoUrl = $logoPath
            ? asset('storage/' . ltrim($logoPath, '/'))
            : null;

        return $this
            ->subject($settings->get('contact.email_subject', 'Reply'))
            ->view('emails.contact-reply-html', [
                'contact' => $this->contact,
                'reply' => $this->reply,
                'siteName' => $settings->get('site_name', config('app.name')),
                'accent' => $settings->get('colors.accent', '#22c55e'),
                'secondary' => $settings->get('colors.secondary', '#0ea5e9'),
                'location' => $settings->get('contact.location'),
                'phone' => $settings->get('contact.phone'),
                'logoUrl' => $logoUrl,
            ]);
    }
}
