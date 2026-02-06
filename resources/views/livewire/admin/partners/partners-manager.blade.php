<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;
use Livewire\WithFileUploads;

new class extends Component {
    use AuthorizesRequests;
    use WithFileUploads;

    public string $badge = '';

    /** @var array<int, array{name:string,url:string,image:string|null}> */
    public array $items = [];

    /** رفع صور مؤقتة لكل عنصر */
    public array $newImages = []; // key = index => TemporaryUploadedFile

    public function mount(SettingsService $settings): void
    {
        $this->authorize('access-dashboard');

        $this->badge = (string) $settings->get('partner.badge', 'Partners');

        $this->items = (array) $settings->get('partner.items', []);
        $this->items = array_values(array_map(function ($row) {
            return [
                'name'  => (string) ($row['name'] ?? ''),
                'url'   => (string) ($row['url'] ?? ''),
                'image' => $row['image'] ?? null,
            ];
        }, $this->items));
    }

    public function addItem(): void
    {
        $this->items[] = ['name' => '', 'url' => '', 'image' => null];
    }

    public function removeItem(int $index): void
    {
        // لو في صورة جديدة مرفوعة ولم تُحفظ، امسحها من الذاكرة
        unset($this->newImages[$index]);

        unset($this->items[$index]);
        $this->items = array_values($this->items);

        // اعادة فهرسة صور الرفع المؤقتة لتطابق الفهارس الجديدة
        $reindexed = [];
        foreach ($this->newImages as $i => $file) {
            if (is_numeric($i)) $reindexed[(int)$i] = $file;
        }
        $this->newImages = array_values($reindexed);
    }

    public function clearImage(int $index): void
    {
        $this->items[$index]['image'] = null;
        unset($this->newImages[$index]);
    }

    public function save(SettingsService $settings): void
    {
        $this->validate([
            'badge' => ['required', 'string', 'max:255'],

            'items' => ['array'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.url' => ['nullable', 'url', 'max:2048'],
            'items.*.image' => ['nullable', 'string', 'max:2048'],

            // صور جديدة (اختيارية)
            'newImages' => ['array'],
            'newImages.*' => ['nullable', 'image', 'max:2048'], // 2MB
        ]);

        // حفظ الصور الجديدة وحقن مساراتها داخل items
        foreach ($this->newImages as $index => $file) {
            if (!$file) continue;
            if (!isset($this->items[$index])) continue;

            $path = $file->store('partners', 'public'); // storage/app/public/partners
            $this->items[$index]['image'] = $path;
        }

        // تنظيف: تأكد القيم صحيحة
        $items = array_values(array_map(function ($row) {
            return [
                'name'  => trim((string) ($row['name'] ?? '')),
                'url'   => trim((string) ($row['url'] ?? '')),
                'image' => $row['image'] ?: null,
            ];
        }, $this->items));

        $settings->set('partner.badge', $this->badge, 'string', 'partner');
        $settings->set('partner.items', $items, 'json', 'partner');

        $this->newImages = [];

        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: '" . __('Partners updated successfully') . "' }
            }));
        ");
    }
};
?>

<div class="space-y-6">

    {{-- Page header --}}
    @include('partials.settings-heading', [
        'badge' => __('Partners'),
        'description' => __('Manage partners section (name, logo, link)'),
        'icon' => 'users',
    ])

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-6 space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon name="users" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                {{ __('Section content') }}
            </h3>
        </div>

        {{-- badge --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('badge') }}</label>
            <input type="text" wire:model.defer="badge"
                   class="input w-full @error('badge') ring-1 ring-red-500 @enderror"
                   placeholder="{{ __('Partners badge') }}" />
            @error('badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:icon name="sparkles" class="w-5 h-5 text-accent" />
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                    {{ __('Partners list') }}
                </h3>
            </div>

            <button wire:click="addItem"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition">
                <flux:icon name="plus" class="w-4 h-4" />
                {{ __('Add partner') }}
            </button>
        </div>

        <div class="p-6 space-y-4">
            @forelse ($items as $index => $item)
                @php
                    $hasError =
                        $errors->has("items.$index.name") ||
                        $errors->has("items.$index.url") ||
                        $errors->has("newImages.$index");
                @endphp

                <div class="rounded-xl border p-5 space-y-4 transition
                    {{ $hasError
                        ? 'border-red-300 bg-red-50 dark:bg-red-950/30 ring-1 ring-red-500'
                        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }}">

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <flux:icon name="briefcase" class="w-4 h-4" />
                            {{ __('Partner') }} #{{ $index + 1 }}
                        </div>

                        <button wire:click="removeItem({{ $index }})"
                            class="inline-flex items-center gap-1 text-xs text-red-500 hover:underline">
                            <flux:icon name="trash" class="w-3.5 h-3.5" />
                            {{ __('Remove') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        {{-- Name --}}
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Name') }}</label>
                            <input type="text" wire:model.defer="items.{{ $index }}.name"
                                   class="input w-full @error("items.$index.name") ring-1 ring-red-500 @enderror"
                                   placeholder="Google" />
                            @error("items.$index.name") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- URL --}}
                        <div class="lg:col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Link') }}</label>
                            <input type="url" wire:model.defer="items.{{ $index }}.url"
                                   class="input w-full @error("items.$index.url") ring-1 ring-red-500 @enderror"
                                   placeholder="https://example.com" />
                            @error("items.$index.url") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Image --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                        <div class="lg:col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Logo') }}</label>

                            <input type="file" accept="image/*" wire:model="newImages.{{ $index }}"
                                   class="input w-full" />

                            @error("newImages.$index") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                            <div class="mt-2 flex items-center gap-3">
                                <button type="button" wire:click="clearImage({{ $index }})"
                                        class="text-xs text-red-500 hover:underline">
                                    {{ __('Remove logo') }}
                                </button>

                                <div wire:loading wire:target="newImages.{{ $index }}" class="text-xs text-slate-500">
                                    {{ __('Uploading...') }}
                                </div>
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div class="lg:col-span-1">
                            <label class="block text-xs text-slate-500 mb-1">{{ __('Preview') }}</label>

                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 p-4 flex items-center justify-center min-h-[110px]">
                                @if (isset($newImages[$index]) && $newImages[$index])
                                    <img src="{{ $newImages[$index]->temporaryUrl() }}"
                                         class="max-h-16 object-contain" alt="preview" />
                                @elseif (!empty($items[$index]['image']))
                                    <img src="{{ asset('storage/' . $items[$index]['image']) }}"
                                         class="max-h-16 object-contain" alt="preview" />
                                @else
                                    <div class="text-xs text-slate-500">{{ __('No logo') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            @empty
                <div class="text-sm text-slate-500 text-center py-10">
                    {{ __('No partners added yet') }}
                </div>
            @endforelse
        </div>

        {{-- Sticky Save --}}
        <div class="sticky bottom-0 z-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-t border-slate-200 dark:border-slate-800 px-6 py-4 flex justify-end">
            <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="px-6 py-2 rounded-xl text-sm bg-accent text-white hover:opacity-90 transition
                       disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            </button>
        </div>
    </div>
</div>
