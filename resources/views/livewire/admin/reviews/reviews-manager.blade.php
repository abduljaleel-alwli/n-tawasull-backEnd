<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;
use Livewire\WithFileUploads;

new class extends Component {
    use AuthorizesRequests;
    use WithFileUploads;

    // Header
    public string $badge = 'التقييمات';
    public string $title = 'قصص نجاح.';
    public string $description = '';

    // Summary / CTA
    public string $ratingText = '4.9/5';
    public string $note = '';
    public array $summaryAvatars = []; // array of urls/paths
    public int $stars = 5;
    public string $trustTitle = '';
    public string $trustSubtitle = '';
    public string $ctaLabel = 'اترك تقييماً';
    public string $ctaUrl = '';

    // Reviews list
    public array $items = [];

    // upload temp avatars per item index
    public array $newAvatars = []; // index => TemporaryUploadedFile

    public function mount(SettingsService $settings): void
    {
        $this->authorize('access-dashboard');

        $this->badge = (string) $settings->get('reviews.badge', 'التقييمات');
        $this->title = (string) $settings->get('reviews.title', 'قصص نجاح.');
        $this->description = (string) $settings->get('reviews.description', '');

        $summary = (array) $settings->get('reviews.summary', []);
        $cta = (array) $settings->get('reviews.cta', []);

        $this->ratingText = (string) ($summary['rating_text'] ?? '4.9/5');
        $this->note = (string) ($summary['note'] ?? '');
        $this->summaryAvatars = (array) ($summary['avatars'] ?? []);
        $this->stars = (int) ($summary['stars'] ?? 5);
        $this->trustTitle = (string) ($summary['trust_title'] ?? '');
        $this->trustSubtitle = (string) ($summary['trust_subtitle'] ?? '');

        $this->ctaLabel = (string) ($cta['label'] ?? 'اترك تقييماً');
        $this->ctaUrl = (string) ($cta['url'] ?? '');

        $this->items = (array) $settings->get('reviews.items', []);
        $this->items = array_values(array_map(function ($row) {
            return [
                'rating' => (float) ($row['rating'] ?? 5),
                'quote'  => (string) ($row['quote'] ?? ''),
                'name'   => (string) ($row['name'] ?? ''),
                'role'   => (string) ($row['role'] ?? ''),
                'avatar' => $row['avatar'] ?? null,
            ];
        }, $this->items));
    }

    public function addItem(): void
    {
        $this->items[] = [
            'rating' => 5,
            'quote' => '',
            'name' => '',
            'role' => '',
            'avatar' => null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->newAvatars[$index]);
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function clearItemAvatar(int $index): void
    {
        $this->items[$index]['avatar'] = null;
        unset($this->newAvatars[$index]);
    }

    public function addSummaryAvatar(): void
    {
        $this->summaryAvatars[] = '';
    }

    public function removeSummaryAvatar(int $index): void
    {
        unset($this->summaryAvatars[$index]);
        $this->summaryAvatars = array_values($this->summaryAvatars);
    }

    public function save(SettingsService $settings): void
    {
        $this->validate([
            'badge' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'ratingText' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:500'],
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'trustTitle' => ['nullable', 'string', 'max:255'],
            'trustSubtitle' => ['nullable', 'string', 'max:255'],
            'ctaLabel' => ['required', 'string', 'max:60'],
            'ctaUrl' => ['nullable', 'string', 'max:2048'],

            'summaryAvatars' => ['array'],
            'summaryAvatars.*' => ['nullable', 'string', 'max:2048'],

            'items' => ['array'],
            'items.*.rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'items.*.quote' => ['required', 'string', 'max:600'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.role' => ['nullable', 'string', 'max:255'],
            'items.*.avatar' => ['nullable', 'string', 'max:2048'],
        ]);

        // Keep using the URL provided for the avatar and don't attempt to upload images
        $summary = [
            'rating_text' => $this->ratingText,
            'note' => $this->note,
            'avatars' => array_values(array_filter($this->summaryAvatars, fn($x) => (string)$x !== '')),
            'stars' => $this->stars,
            'trust_title' => $this->trustTitle,
            'trust_subtitle' => $this->trustSubtitle,
        ];

        $cta = [
            'label' => $this->ctaLabel,
            'url' => $this->ctaUrl,
        ];

        $items = array_values(array_map(function ($row) {
            return [
                'rating' => (float) $row['rating'],
                'quote' => trim((string) $row['quote']),
                'name' => trim((string) $row['name']),
                'role' => trim((string) ($row['role'] ?? '')),
                'avatar' => $row['avatar'] ?: null, // Keep the avatar URL
            ];
        }, $this->items));

        // Store the settings
        $settings->set('reviews.badge', $this->badge, 'string', 'reviews');
        $settings->set('reviews.title', $this->title, 'string', 'reviews');
        $settings->set('reviews.description', $this->description, 'text', 'reviews');
        $settings->set('reviews.summary', $summary, 'json', 'reviews');
        $settings->set('reviews.cta', $cta, 'json', 'reviews');
        $settings->set('reviews.items', $items, 'json', 'reviews');

        $this->js("window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: '" . __('Reviews updated successfully') . "' }}));");
    }

};
?>

<div class="space-y-6">

    @include('partials.settings-heading', [
        'title' => __('Reviews'),
        'description' => __('Manage testimonials, rating summary, and CTA'),
        'icon' => 'star',
    ])

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="star" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Section header') }}</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Badge') }}</label>
                <input wire:model.defer="badge" class="input w-full @error('badge') ring-1 ring-red-500 @enderror" />
                @error('badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Title') }}</label>
                <input wire:model.defer="title" class="input w-full @error('title') ring-1 ring-red-500 @enderror" />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Description') }}</label>
            <textarea wire:model.defer="description" rows="3"
                      class="textarea w-full @error('description') ring-1 ring-red-500 @enderror"></textarea>
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Summary + CTA --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="sparkles" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Summary & CTA') }}</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Rating text') }}</label>
                <input wire:model.defer="ratingText" class="input w-full @error('ratingText') ring-1 ring-red-500 @enderror" />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Stars') }}</label>
                <select wire:model.defer="stars" class="input w-full @error('stars') ring-1 ring-red-500 @enderror">
                    @for($i=1;$i<=5;$i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('CTA label') }}</label>
                <input wire:model.defer="ctaLabel" class="input w-full @error('ctaLabel') ring-1 ring-red-500 @enderror" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Note') }}</label>
                <textarea wire:model.defer="note" rows="2" class="textarea w-full"></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('CTA URL') }}</label>
                <input wire:model.defer="ctaUrl" class="input w-full" placeholder="https://..." />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Trust title') }}</label>
                <input wire:model.defer="trustTitle" class="input w-full" />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Trust subtitle') }}</label>
                <input wire:model.defer="trustSubtitle" class="input w-full" />
            </div>
        </div>

        {{-- Summary avatars urls --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-medium text-slate-500">{{ __('Summary avatars (URLs or storage paths)') }}</label>
                <button wire:click="addSummaryAvatar" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition">
                    + {{ __('Add avatar') }}
                </button>
            </div>

            <div class="space-y-2">
                @foreach($summaryAvatars as $i => $url)
                    <div class="flex gap-2">
                        <input wire:model.defer="summaryAvatars.{{ $i }}" class="input w-full"
                               placeholder="https://... OR reviews/avatars/xxx.webp" />
                        <button wire:click="removeSummaryAvatar({{ $i }})" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm bg-red-500 text-white hover:opacity-90 transition">
                            {{ __('Remove') }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Reviews list --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:icon name="message" class="w-5 h-5 text-accent" />
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Reviews') }}</h3>
            </div>

            <button wire:click="addItem"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition">
                <flux:icon name="plus" class="w-4 h-4" />
                {{ __('Add review') }}
            </button>
        </div>

        <div class="p-6 space-y-4">
            @forelse($items as $index => $item)
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">

                    <div class="flex items-center justify-between">
                        <div class="text-xs font-medium text-slate-500">
                            {{ __('Review') }} #{{ $index + 1 }}
                        </div>
                        <button wire:click="removeItem({{ $index }})" class="text-xs text-red-500 hover:underline">
                            {{ __('Remove') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Rating') }}</label>
                            <input type="number" step="0.1" min="1" max="5"
                                   wire:model.defer="items.{{ $index }}.rating"
                                   class="input w-full @error("items.$index.rating") ring-1 ring-red-500 @enderror" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Quote') }}</label>
                            <textarea rows="3" wire:model.defer="items.{{ $index }}.quote"
                                      class="textarea w-full @error("items.$index.quote") ring-1 ring-red-500 @enderror"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Name') }}</label>
                            <input wire:model.defer="items.{{ $index }}.name"
                                   class="input w-full @error("items.$index.name") ring-1 ring-red-500 @enderror" />
                        </div>

                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Role') }}</label>
                            <input wire:model.defer="items.{{ $index }}.role" class="input w-full" />
                        </div>

                        <div class="space-y-2">
                    <label class="block text-xs text-slate-500">{{ __('Avatar URL') }}</label>
                    <input wire:model.defer="items.{{ $index }}.avatar" class="input w-full" placeholder="https://example.com/avatar.jpg" />
                    
                    <!-- عند حفظ البيانات، إذا كان URL تم إدخاله في الحقل -->
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-500">{{ __('No avatar uploaded') }}</span>
                    </div>

                    <!-- عرض الصورة باستخدام الرابط المضاف -->
                    @if(!empty($item['avatar']))
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 p-3 flex items-center justify-center min-h-[80px]">
                            <img src="{{ $item['avatar'] }}" class="w-12 h-12 rounded-full object-cover" alt="Avatar" />
                        </div>
                    @else
                        <span class="text-xs text-slate-500">{{ __('No avatar') }}</span>
                    @endif
                </div>
                    </div>

                </div>
            @empty
                <div class="text-sm text-slate-500 text-center py-10">{{ __('No reviews added yet') }}</div>
            @endforelse
        </div>

        <div class="sticky bottom-0 z-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-t border-slate-200 dark:border-slate-800 px-6 py-4 flex justify-end">
            <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="px-6 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            </button>
        </div>
    </div>

</div>
