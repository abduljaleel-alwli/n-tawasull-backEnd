<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;

new class extends Component {
    use AuthorizesRequests;

    public string $badge = 'الأسئلة الشائعة';
    public string $title = 'استفسارات.';
    public string $description = '';

    public array $items = [];

    public function mount(SettingsService $settings): void
    {
        $this->authorize('access-dashboard');

        $this->badge = (string) $settings->get('faqs.badge', 'الأسئلة الشائعة');
        $this->title = (string) $settings->get('faqs.title', 'استفسارات.');
        $this->description = (string) $settings->get('faqs.description', '');

        $this->items = (array) $settings->get('faqs.items', []);
        $this->items = array_values(array_map(function ($row) {
            return [
                'question' => (string) ($row['question'] ?? ''),
                'answer'   => (string) ($row['answer'] ?? ''),
            ];
        }, $this->items));
    }

    public function addItem(): void
    {
        $this->items[] = ['question' => '', 'answer' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(SettingsService $settings): void
    {
        $this->validate([
            'badge' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],

            'items' => ['array'],
            'items.*.question' => ['required', 'string', 'max:255'],
            'items.*.answer' => ['required', 'string', 'max:2000'],
        ]);

        $items = array_values(array_map(function ($row) {
            return [
                'question' => trim((string) $row['question']),
                'answer'   => trim((string) $row['answer']),
            ];
        }, $this->items));

        $settings->set('faqs.badge', $this->badge, 'string', 'faqs');
        $settings->set('faqs.title', $this->title, 'string', 'faqs');
        $settings->set('faqs.description', $this->description, 'text', 'faqs');
        $settings->set('faqs.items', $items, 'json', 'faqs');

        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: '" . __('FAQs updated successfully') . "' }
            }));
        ");
    }
};
?>

<div class="space-y-6">

    @include('partials.settings-heading', [
        'title' => __('FAQs'),
        'description' => __('Manage frequently asked questions section'),
        'icon' => 'book-open',
    ])

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="question-mark-circle" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                {{ __('Section header') }}
            </h3>
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

    {{-- Items --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:icon name="list-bullet" class="w-5 h-5 text-accent" />
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Questions') }}</h3>
            </div>

            <button wire:click="addItem"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition">
                <flux:icon name="plus" class="w-4 h-4" />
                {{ __('Add question') }}
            </button>
        </div>

        <div class="p-6 space-y-4">
            @forelse($items as $index => $item)
                @php
                    $hasError = $errors->has("items.$index.question") || $errors->has("items.$index.answer");
                @endphp

                <div class="rounded-xl border p-5 space-y-4
                    {{ $hasError
                        ? 'border-red-300 bg-red-50 dark:bg-red-950/30 ring-1 ring-red-500'
                        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }}">

                    <div class="flex items-center justify-between">
                        <div class="text-xs font-medium text-slate-500">
                            {{ __('FAQ') }} #{{ $index + 1 }}
                        </div>

                        <button wire:click="removeItem({{ $index }})" class="text-xs text-red-500 hover:underline">
                            {{ __('Remove') }}
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('Question') }}</label>
                        <input wire:model.defer="items.{{ $index }}.question"
                               class="input w-full @error("items.$index.question") ring-1 ring-red-500 @enderror" />
                        @error("items.$index.question") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('Answer') }}</label>
                        <textarea rows="4" wire:model.defer="items.{{ $index }}.answer"
                                  class="textarea w-full @error("items.$index.answer") ring-1 ring-red-500 @enderror"></textarea>
                        @error("items.$index.answer") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-500 text-center py-10">{{ __('No questions added yet') }}</div>
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
