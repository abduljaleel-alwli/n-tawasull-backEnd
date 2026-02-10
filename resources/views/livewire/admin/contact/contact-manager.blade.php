<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;

new class extends Component {
    use AuthorizesRequests;

    public string $badge = '';
    public string $title = '';
    public string $description = '';
    public string $email_to = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $location = '';
    public string $working_time = '';
    public string $email_subject = '';

    public array $social_links = [];

    public function mount(SettingsService $settings): void
    {
        // Only admin & super-admin (super-admin bypass via Gate::before)
        $this->authorize('access-dashboard');

        $this->badge = (string) $settings->get('contact.badge', '');
        $this->title = (string) $settings->get('contact.title', '');
        $this->description = (string) $settings->get('contact.description', '');
        $this->email_to = (string) $settings->get('contact.email_to', '');
        $this->phone = (string) $settings->get('contact.phone', '');
        $this->whatsapp = (string) $settings->get('contact.whatsapp', '');
        $this->location = (string) $settings->get('contact.location', '');
        $this->working_time = (string) $settings->get('contact.working_time', '');
        $this->email_subject = (string) $settings->get('contact.email_subject', '');

        $this->social_links = (array) $settings->get('contact.social_links', []);
    }

    public function addSocial(): void
    {
        $this->social_links[] = [
            'platform' => '',
            'url' => '',
            'icon_type' => 'class',
            'icon_value' => '',
        ];
    }

    public function removeSocial(int $index): void
    {
        unset($this->social_links[$index]);
        $this->social_links = array_values($this->social_links);
    }

    public function save(SettingsService $settings): void
    {
        try {
            $this->validate([
                'badge' => ['required', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'email_to' => ['required', 'email'],
                'phone' => ['nullable', 'string', 'max:50'],
                'whatsapp' => ['nullable', 'string', 'max:50'],
                'location' => ['nullable', 'string', 'max:255'],
                'working_time' => ['nullable', 'string', 'max:255'],
                'email_subject' => ['required', 'string', 'max:255'],
                'social_links' => ['array'],
                'social_links.*.platform' => ['required', 'string', 'max:50'],
                'social_links.*.url' => ['required', 'string', 'max:500'],
                'social_links.*.icon_type' => ['required', 'in:class,svg'],
                'social_links.*.icon_value' => ['required', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->failedValidation();
            throw $e;
        }

        // Save only if validation passed
        $settings->set('contact.badge', $this->badge, 'string', 'contact');
        $settings->set('contact.title', $this->title, 'string', 'contact');
        $settings->set('contact.description', $this->description, 'text', 'contact');
        $settings->set('contact.email_to', $this->email_to, 'string', 'contact');
        $settings->set('contact.phone', $this->phone, 'string', 'contact');
        $settings->set('contact.whatsapp', $this->whatsapp, 'string', 'contact');
        $settings->set('contact.location', $this->location, 'string', 'contact');
        $settings->set('contact.working_time', $this->working_time, 'string', 'contact');
        $settings->set('contact.email_subject', $this->email_subject, 'string', 'contact');
        $settings->set('contact.social_links', $this->social_links, 'json', 'contact');

        $this->js(
            "
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" .
                __('Contact page updated successfully') .
                "'
                    }
                })
            );
        ",
        );
    }

    protected function failedValidation(): void
    {
        // Force Livewire to re-sync state after validation failure
        $this->social_links = array_values($this->social_links);
    }
};
?>

<div class="space-y-6">

    {{-- Page heading --}}
    @include('partials.settings-heading', [
        'title' => __('Contact page'),
        'description' => __('Manage contact page information'),
        'icon' => 'phone',
    ])

    {{-- ================= Main Contact Info ================= --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
                bg-white dark:bg-slate-900/90 overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-accent/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15
                           A2.25 2.25 0 012.25 17.25V6.75
                           M21.75 6.75l-9.75 6-9.75-6" />
                </svg>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                        {{ __('Main contact information') }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ __('Basic details shown on the contact page') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-6">

            {{-- Badge --}}
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('Badge') }}</label>
                <input wire:model.defer="badge" type="text"
                    class="input w-full focus:ring-accent/40 @error('badge') ring-1 ring-red-500 @enderror"
                    placeholder="{{ __('Contact badge') }}" />
                @error('badge')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Title --}}
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('Title') }}</label>
                <input wire:model.defer="title" type="text"
                    class="input w-full focus:ring-accent/40 @error('title') ring-1 ring-red-500 @enderror"
                    placeholder="{{ __('Contact page title') }}" />
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('Description') }}</label>
                <textarea wire:model.defer="description"
                    class="textarea w-full focus:ring-accent/40 @error('description') ring-1 ring-red-500 @enderror" rows="3"
                    placeholder="{{ __('Short description shown on the contact page') }}"></textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('Email recipient') }}</label>
                    <input wire:model.defer="email_to"
                        class="input w-full @error('email_to') ring-1 ring-red-500 @enderror"
                        placeholder="info@example.com" />
                    @error('email_to')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

               

                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('Location') }}</label>
                    <input wire:model.defer="location"
                        class="input w-full @error('location') ring-1 ring-red-500 @enderror"
                        placeholder="{{ __('City, Country') }}" />
                    @error('location')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                 <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('Phone number') }}</label>
                    <input wire:model.defer="phone" class="input w-full @error('phone') ring-1 ring-red-500 @enderror"
                        placeholder="+970 599 000 000" />
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('WhatsApp number') }}</label>
                    <input wire:model.defer="whatsapp" class="input w-full @error('whatsapp') ring-1 ring-red-500 @enderror"
                        placeholder="+970 599 000 000" />
                    @error('whatsapp')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('Working time') }}</label>
                    <input wire:model.defer="working_time"
                        class="input w-full @error('working_time') ring-1 ring-red-500 @enderror"
                        placeholder="{{ __('Saturday - Thursday: 9:00 - 18:00') }}" />
                    @error('working_time')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('Email subject') }}</label>
                    <input wire:model.defer="email_subject"
                        class="input w-full @error('email_subject') ring-1 ring-red-500 @enderror"
                        placeholder="{{ __('New contact message') }}" />
                    @error('email_subject')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>
    </div>

    {{-- ================= Social Links ================= --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
                bg-white dark:bg-slate-900/90 overflow-hidden">

        {{-- Header --}}
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-slate-800
                    flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-accent/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 12a2.25 2.25 0 104.5 0
                           2.25 2.25 0 00-4.5 0zm6.75-4.5
                           a2.25 2.25 0 104.5 0
                           2.25 2.25 0 00-4.5 0zm0 9
                           a2.25 2.25 0 104.5 0
                           2.25 2.25 0 00-4.5 0z" />
                </svg>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ __('Social links') }}
                </h3>
            </div>

            <button wire:click="addSocial"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm
                       bg-accent text-white hover:opacity-90 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Add link') }}
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-4">
            @forelse ($social_links as $index => $social)
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-800
                            bg-slate-50 dark:bg-slate-800/40">

                    <div
                        class="flex items-center justify-between px-4 py-3 border-b
                                border-slate-200 dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                            {{ __('Social link') }} #{{ $index + 1 }}
                        </span>

                        <button wire:click="removeSocial({{ $index }})"
                            class="text-xs text-red-500 hover:text-red-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ __('Remove') }}
                        </button>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('Platform') }}</label>
                                <input wire:model.defer="social_links.{{ $index }}.platform"
                                    class="input w-full" placeholder="facebook / whatsapp" />
                            </div>

                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('URL') }}</label>
                                <input wire:model.defer="social_links.{{ $index }}.url" class="input w-full"
                                    placeholder="https://..." />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('Icon type') }}</label>
                                <select wire:model.live="social_links.{{ $index }}.icon_type"
                                    class="input w-full">
                                    <option value="class">{{ __('Font Awesome class') }}</option>
                                    <option value="svg">{{ __('SVG code') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('Icon value') }}</label>
                                <textarea wire:model.live="social_links.{{ $index }}.icon_value" class="textarea w-full font-mono text-xs"
                                    rows="2" placeholder="{{ __('Icon class or SVG code') }}"></textarea>
                            </div>
                        </div>

                        {{-- Icon Preview --}}
                        <div class="flex items-center gap-3 pt-2">
                            <span class="text-xs text-slate-500">
                                {{ __('Preview') }}
                            </span>

                            <div
                                class="w-10 h-10 rounded-lg
                flex items-center justify-center
                border border-slate-200 dark:border-slate-700
                bg-white dark:bg-slate-900">

                                @php
                                    $type = $social['icon_type'] ?? null;
                                    $value = $social['icon_value'] ?? null;
                                @endphp

                                @if ($type === 'class' && filled($value))
                                    <i class="{{ $value }} text-lg text-slate-700 dark:text-slate-200"></i>
                                @elseif ($type === 'svg' && filled($value))
                                    <div class="w-5 h-5 text-slate-700 dark:text-slate-200">
                                        {!! $value !!}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-6">
                    {{ __('No social links added yet') }}
                </p>
            @endforelse
        </div>
    </div>

    {{-- ================= Sticky Save ================= --}}
    <div
        class="sticky bottom-0 z-10
                bg-white/90 dark:bg-slate-900/90 backdrop-blur
                border-t border-slate-200 dark:border-slate-800
                px-6 py-4 flex justify-end">

        <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
            class="px-6 py-2 rounded-xl text-sm bg-accent text-white
                   hover:opacity-90 transition
                   disabled:opacity-50 disabled:cursor-not-allowed
                   flex items-center gap-2">

            <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25" />
                <path d="M12 2a10 10 0 0110 10" stroke-width="4" class="opacity-75" />
            </svg>

            <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
            <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
        </button>
    </div>

</div>
