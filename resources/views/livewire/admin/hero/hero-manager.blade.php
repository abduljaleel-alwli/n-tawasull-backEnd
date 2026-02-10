<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;

new class extends Component {
    use AuthorizesRequests;

    // Hero header
    public string $badge = 'نقطة تواصل - مكة المكرمة';
    public string $title = 'الحل التسويقي الاستراتيجي الأول في المملكة';
    public string $description = 'انطلقنا من جوار بيت الله الحرام...';

    // CTA Buttons
    public array $ctas = [];

    // Hero images
    public array $images = [];

    // Scroll text
    public string $scrollText = 'اسحب للأسفل';
    public bool $scrollIndicator = true;

    public function mount(SettingsService $settings): void
    {
        $this->authorize('access-dashboard');

        $this->badge = (string) $settings->get('hero.badge', 'نقطة تواصل - مكة المكرمة');
        $this->title = (string) $settings->get('hero.title', 'الحل التسويقي الاستراتيجي الأول في المملكة');
        $this->description = (string) $settings->get('hero.description', 'انطلقنا من جوار بيت الله الحرام...');

        $this->ctas = (array) $settings->get('hero.ctas', []);
        $this->images = (array) $settings->get('hero.images', []);
        $this->scrollText = (string) $settings->get('hero.scroll_text', 'اسحب للأسفل');
        $this->scrollIndicator = (bool) $settings->get('hero.scroll_indicator', true);
    }

    // CTA
    public function addCTA(): void
    {
        $this->ctas[] = ['label' => '', 'url' => ''];
    }

    public function removeCTA(int $index): void
    {
        unset($this->ctas[$index]);
        $this->ctas = array_values($this->ctas);
    }

    // Images
    public function addImage(): void
    {
        $this->images[] = ['url' => '', 'alt' => ''];
    }

    public function removeImage(int $index): void
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function save(SettingsService $settings): void
    {
        $this->validate([
            'badge' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],

            'ctas' => ['array'],
            'ctas.*.label' => ['required', 'string', 'max:100'],
            'ctas.*.url' => ['nullable', 'string', 'max:2048'],

            'images' => ['array'],
            'images.*.url' => ['nullable', 'url', 'max:2048'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],

            'scrollText' => ['nullable', 'string', 'max:255'],
            'scrollIndicator' => ['boolean'],
        ]);

        $ctas = array_map(fn ($cta) => [
            'label' => trim($cta['label']),
            'url' => trim($cta['url']),
        ], $this->ctas);

        $images = array_map(fn ($img) => [
            'url' => trim($img['url']),
            'alt' => trim($img['alt']),
        ], $this->images);

        $settings->set('hero.badge', $this->badge, 'string', 'hero');
        $settings->set('hero.title', $this->title, 'string', 'hero');
        $settings->set('hero.description', $this->description, 'text', 'hero');
        $settings->set('hero.ctas', $ctas, 'json', 'hero');
        $settings->set('hero.images', $images, 'json', 'hero');
        $settings->set('hero.scroll_text', $this->scrollText, 'string', 'hero');
        $settings->set('hero.scroll_indicator', $this->scrollIndicator, 'boolean', 'hero');

        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: '" . __('Hero updated successfully') . "' }
            }));
        ");
    }
};
?>

<div class="space-y-6">

    @include('partials.settings-heading', [
        'title' => __('Hero Section'),
        'description' => __('Manage the hero section including text, buttons, and images'),
        'icon' => 'fire',
    ])

    {{-- Hero Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="fire" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Header') }}</h3>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Badge') }}</label>
            <input wire:model.defer="badge" class="input w-full @error('badge') ring-1 ring-red-500 @enderror" />
            @error('badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Title') }}</label>
            <input wire:model.defer="title" class="input w-full @error('title') ring-1 ring-red-500 @enderror" />
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Description') }}</label>
            <textarea wire:model.defer="description" rows="3"
                class="textarea w-full @error('description') ring-1 ring-red-500 @enderror"></textarea>
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Hero CTAs --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="phone-arrow-up-right" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Call to Action Buttons') }}</h3>
        </div>

        <div class="space-y-3">
            @foreach($ctas as $index => $cta)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Button Label') }}</label>
                        <input wire:model.defer="ctas.{{ $index }}.label" class="input w-full" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Button URL') }}</label>
                        <input wire:model.defer="ctas.{{ $index }}.url" class="input w-full" />
                    </div>

                    <div class="flex gap-2 justify-end items-center">
                        <button wire:click="removeCTA({{ $index }})" class="text-xs text-red-500 hover:underline">
                            {{ __('Remove Button') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end">
            <button wire:click="addCTA" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition">
                <flux:icon name="plus" class="w-4 h-4" />
                {{ __('Add CTA') }}
            </button>
        </div>
    </div>

    {{-- Hero Images --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="photo" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Images') }}</h3>
        </div>

        <div class="space-y-3">
            @foreach($images as $index => $image)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Image URL') }}</label>
                        <input wire:model.defer="images.{{ $index }}.url" class="input w-full" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Image Alt Text') }}</label>
                        <input wire:model.defer="images.{{ $index }}.alt" class="input w-full" />
                    </div>

                    <div class="flex gap-2 justify-end items-center">
                        <button wire:click="removeImage({{ $index }})" class="text-xs text-red-500 hover:underline">
                            {{ __('Remove Image') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end">
            <button wire:click="addImage" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition">
                <flux:icon name="plus" class="w-4 h-4" />
                {{ __('Add Image') }}
            </button>
        </div>
    </div>

    {{-- Scroll text --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="arrow-down" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Scroll Text') }}</h3>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Scroll Text') }}</label>
            <input wire:model.defer="scrollText" class="input w-full" />
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Show Scroll Indicator') }}</label>
            <input type="checkbox" wire:model.defer="scrollIndicator" class="toggle" />
        </div>
    </div>

    {{-- Save button --}}
    <div class="flex justify-end">
        <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="px-6 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition disabled:opacity-50">
            <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
            <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
        </button>
    </div>

</div>
