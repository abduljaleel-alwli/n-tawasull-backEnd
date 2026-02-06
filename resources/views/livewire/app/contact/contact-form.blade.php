<?php

use Livewire\Volt\Component;
use App\Actions\Contact\StoreContactMessage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageMail;
use App\Notifications\NewContactMessageNotification;
use App\Models\User;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public bool $submitting = false;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $message = '';

    // attachment (single file)
    public $attachment = null;

    public bool $success = false;

    public function submit(StoreContactMessage $store): void
    {
        if ($this->submitting) {
            return;
        }

        $this->submitting = true;

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],

            // file validation
            'attachment' => [
                'nullable',
                'file',
                'max:15360', // 15MB
                'mimes:pdf,jpg,jpeg,png,doc,docx',
            ],
        ]);

        $attachmentPath = null;

        // dd($this->attachment);

        if ($this->attachment) {
            $attachmentPath = $this->attachment->store(
                'contact-attachments',
                'private', // أو 'public' حسب قرارك لاحقًا
            );
        }

        $contactMessage = $store->execute(
            array_merge($data, [
                'attachment_path' => $attachmentPath,
            ]),
            request()->ip(),
        );

        app(\App\Services\Analytics\AnalyticsService::class)->track('contact_submitted', [
            'entity_type' => 'contact_message',
            'entity_id' => $contactMessage->id,
            'source' => 'contact_form',
        ]);

        // Notify admins
        User::role(['admin', 'super-admin'])
            ->get()
            ->each(fn($user) => $user->notify(new NewContactMessageNotification($contactMessage)));

        // Send email to admin
        if ($to = settings('contact.email_to')) {
            Mail::to($to)->send(new ContactMessageMail($contactMessage));
        }

        $this->reset(['name', 'email', 'phone', 'message', 'attachment']);

        $this->submitting = false;
        $this->success = true;
    }
};
?>

<div class="af-card af-form-card">
    <h2 class="af-title">تواصل معنا</h2>
    <p class="af-sub">
        اترك بياناتك وسنتواصل معك بأقرب وقت لتجهيز عرض سعر مناسب لطلبك.
    </p>

    <form wire:submit.prevent="submit" enctype="multipart/form-data" class="af-form">
        <div class="af-field">
            <label class="ms-3" for="name">{{ __('Name') }}</label>
            <input type="text" wire:model.defer="name" placeholder="{{ __('Your name') }}"
                class="@error('name') is-invalid @enderror" required>

            @error('name')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="af-field">
            <label class="ms-3" for="email">{{ __('Email') }}</label>
            <input type="email" wire:model.defer="email" placeholder="{{ __('Your email') }}"
                class="@error('email') is-invalid @enderror" required>
            @error('email')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="af-field">
            <label class="ms-3" for="phone">{{ __('Phone') }}</label>
            <input type="phone" wire:model.defer="phone" placeholder="{{ __('05xxxxxxxx') }}"
                class="@error('phone') is-invalid @enderror" required>
            @error('phone')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="af-field">
            <label class="ms-3" for="message">{{ __('Message') }}</label>
            <textarea wire:model.defer="message" placeholder="{{ __('Your message') }}"
                class="@error('message') is-invalid @enderror" required></textarea>

            @error('message')
                <div class="af-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="af-submit-row">
            <div class="af-field af-upload">

                <label class="ms-3">
                    {{ __('Attachment (optional)') }}
                </label>

                <input type="file" id="attachment" wire:model="attachment" class="af-file-input"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">

                <label for="attachment" class="af-upload-box">
                    <div class="af-upload-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>

                    <div class="af-upload-text">
                        <strong>{{ __('Upload a file') }}</strong>
                        <span>{{ __('PDF, Images, or Documents') }}</span>
                    </div>

                    <div class="af-upload-btn">
                        {{ __('Choose file') }}
                    </div>
                </label>

                {{-- Loading --}}
                <div wire:loading wire:target="attachment" class="af-hint">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i>
                    {{ __('Uploading file...') }}
                </div>

                {{-- File name --}}
                @if ($attachment)
                    <div class="af-file-name">
                        <i class="fa-solid fa-paperclip me-1"></i>
                        {{ $attachment->getClientOriginalName() }}
                    </div>
                @endif

                @error('attachment')
                    <div class="af-error">{{ $message }}</div>
                @enderror
            </div>



            <div class="flex items-center gap-3 mt-2">

                <button type="submit" class="af-btn" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">
                        {{ __('Send message') }}
                    </span>

                    <span wire:loading wire:target="submit" class="af-loading">
                        {{ __('Sending...') }}
                    </span>
                </button>

                {{-- Success message --}}
                @if ($success)
                    <span class="af-success">
                        {{ __('Your message has been sent successfully') }}
                    </span>
                @endif

            </div>
        </div>

    </form>

</div>
