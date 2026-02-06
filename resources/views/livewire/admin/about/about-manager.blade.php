<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Settings\SettingsService;

new class extends Component {
    use AuthorizesRequests;

    // Fields
    public string $title = '';
    public string $subtitle = '';
    public string $description = '';

    public bool $showIconPicker = false;
    public ?int $iconPickerIndex = null;

    // Repeatable features
    public array $features = [];

    public function mount(SettingsService $settings): void
    {
        // Only admin & super-admin (super-admin bypass via Gate::before)
        $this->authorize('access-dashboard');

        $this->title = (string) $settings->get('about.title', '');
        $this->subtitle = (string) $settings->get('about.subtitle', '');
        $this->description = (string) $settings->get('about.description', '');

        $this->features = (array) $settings->get('about.features', []);
    }

    public function addFeature(): void
    {
        $this->features[] = [
            'title' => '',
            'description' => '',
            'icon_type' => 'class',
            'icon_value' => '',
        ];
    }

    public function removeFeature(int $index): void
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function openIconPicker(int $index): void
    {
        $this->iconPickerIndex = $index;
        $this->showIconPicker = true;
    }

    public function selectIcon(string $icon): void
    {
        if ($this->iconPickerIndex === null) {
            return;
        }

        $this->features[$this->iconPickerIndex]['icon_type'] = 'flux';
        $this->features[$this->iconPickerIndex]['icon_value'] = $icon;

        $this->showIconPicker = false;
        $this->iconPickerIndex = null;
    }

    public function updatedFeatures($value, $key): void
    {
        if (str_ends_with($key, '.icon_type')) {
            $index = (int) explode('.', $key)[0];
            $this->features[$index]['icon_value'] = '';
        }
    }

    public function save(SettingsService $settings): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],

            'features' => ['array'],
            'features.*.title' => ['required', 'string', 'max:255'],
            'features.*.description' => ['required', 'string'],
            'features.*.icon_type' => ['required', 'in:class,svg,flux'],
            'features.*.icon_value' => ['nullable', 'string'],
        ]);

        $settings->set('about.title', $this->title, 'string', 'about');
        $settings->set('about.subtitle', $this->subtitle, 'string', 'about');
        $settings->set('about.description', $this->description, 'text', 'about');
        $settings->set('about.features', $this->features, 'json', 'about');

        // Toast (project-wide standard)
        $this->js(
            "
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" .
                __('About page updated successfully') .
                "'
                    }
                })
            );
        ",
        );
    }
};
?>

<div class="space-y-6">

    {{-- Page header --}}
    @include('partials.settings-heading', [
        'title' => __('About page'),
        'description' => __('Manage the content displayed on the About page'),
        'icon' => 'information-circle',
    ])

    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90
           p-6 space-y-6">

        <div class="flex items-center gap-2">
            <flux:icon name="information-circle" class="w-5 h-5 text-accent" />
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                {{ __('About content') }}
            </h3>
        </div>

        {{-- Title --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">
                {{ __('Title') }}
            </label>

            <input type="text" wire:model.defer="title"
                class="input w-full @error('title') ring-1 ring-red-500 @enderror"
                placeholder="{{ __('About page title') }}" />

            @error('title')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Subtitle --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">
                {{ __('Subtitle') }}
                <span class="text-slate-400">({{ __('Optional') }})</span>
            </label>

            <input type="text" wire:model.defer="subtitle"
                class="input w-full @error('subtitle') ring-1 ring-red-500 @enderror"
                placeholder="{{ __('Short subtitle under the title') }}" />
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">
                {{ __('Description') }}
            </label>

            <textarea wire:model.defer="description" rows="5"
                class="textarea w-full @error('description') ring-1 ring-red-500 @enderror"
                placeholder="{{ __('Main description of the About page') }}"></textarea>

            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>




    <div class="card space-y-4">
        <div
            class="rounded-2xl border border-slate-200 dark:border-slate-800
            bg-white dark:bg-slate-900/90 overflow-hidden">

            <div
                class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white/90 dark:bg-slate-900/80
           backdrop-blur overflow-hidden">

                {{-- Header --}}
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-800
           flex items-center justify-between">

                    <div class="flex items-center gap-2">
                        <flux:icon name="sparkles" class="w-5 h-5 text-accent" />
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            {{ __('Features') }}
                        </h3>
                    </div>

                    <button wire:click="addFeature"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm
               bg-accent text-white hover:opacity-90 transition">
                        <flux:icon name="plus" class="w-4 h-4" />
                        {{ __('Add feature') }}
                    </button>
                </div>




                <div class="p-6 space-y-4">
                    @forelse ($features as $index => $feature)
                        @php
                            $featureHasError =
                                $errors->has("features.$index.title") ||
                                $errors->has("features.$index.description") ||
                                $errors->has("features.$index.icon_type") ||
                                $errors->has("features.$index.icon_value");
                        @endphp

                        <div
                            class="rounded-xl border p-5 space-y-4 transition
    {{ $featureHasError
        ? 'border-red-300 bg-red-50 dark:bg-red-950/30 ring-1 ring-red-500'
        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }}">

                            {{-- Feature header --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                                    <flux:icon name="puzzle-piece" class="w-4 h-4" />
                                    {{ __('Feature') }} #{{ $index + 1 }}
                                </div>

                                <button wire:click="removeFeature({{ $index }})"
                                    class="inline-flex items-center gap-1 text-xs text-red-500 hover:underline">
                                    <flux:icon name="trash" class="w-3.5 h-3.5" />
                                    {{ __('Remove') }}
                                </button>
                            </div>

                            {{-- Title --}}
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">
                                    {{ __('Feature title') }}
                                </label>
                                <input type="text" wire:model.defer="features.{{ $index }}.title"
                                    class="input w-full @error('features.' . $index . '.title') ring-1 ring-red-500 @enderror" />
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">
                                    {{ __('Feature description') }}
                                </label>
                                <textarea wire:model.defer="features.{{ $index }}.description"
                                    class="textarea w-full @error('features.' . $index . '.description') ring-1 ring-red-500 @enderror" rows="3"></textarea>
                            </div>

                            {{-- Icon --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">
                                        {{ __('Icon type') }}
                                    </label>
                                    <select wire:model.live="features.{{ $index }}.icon_type"
                                        class="input w-full">
                                        <option value="class">{{ __('Fontawesome') }}</option>
                                        <option value="svg">{{ __('SVG') }}</option>
                                        <option value="flux">{{ __('Flux') }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">
                                        {{ __('Icon value') }}
                                    </label>

                                    @if (($features[$index]['icon_type'] ?? null) === 'class')
                                        <input type="text"
                                            wire:model.defer="features.{{ $index }}.icon_value"
                                            class="input w-full" placeholder="fa-solid fa-star" />
                                    @elseif (($features[$index]['icon_type'] ?? null) === 'flux')
                                        <livewire:icon-picker wire:model="features.{{ $index }}.icon_value"
                                            :key="'icon-picker-' . $index" />
                                    @else
                                        <textarea wire:model.defer="features.{{ $index }}.icon_value" class="textarea w-full font-mono text-xs"
                                            rows="3" placeholder="<svg>...</svg>"></textarea>
                                    @endif
                                </div>

                            </div>
                        </div>

                    @empty
                        <div class="text-sm text-slate-500 text-center py-6">
                            {{ __('No features added yet') }}
                        </div>
                    @endforelse

                </div>

            </div>
        </div>

        <div
            class="sticky bottom-0 z-10
           bg-white/80 dark:bg-slate-900/80
           backdrop-blur
           border-t border-slate-200 dark:border-slate-800
           px-6 py-4 flex justify-end">

            <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="px-6 py-2 rounded-xl text-sm
               bg-accent text-white
               hover:opacity-90 transition
               disabled:opacity-50 disabled:cursor-not-allowed
               flex items-center gap-2">

                <span wire:loading.remove wire:target="save">
                    {{ __('Save changes') }}
                </span>

                <span wire:loading wire:target="save">
                    {{ __('Saving...') }}
                </span>
            </button>
        </div>

    </div>
