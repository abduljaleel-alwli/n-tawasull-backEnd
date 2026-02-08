<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use App\Services\Settings\SettingsService;

new class extends Component {
    use WithFileUploads;
    use AuthorizesRequests;

    /* =========================
       UI State
    ========================= */
    public string $tab = 'general';

    /* =========================
       General
    ========================= */
    public string $site_name = '';
    public string $site_description = '';

    /* =========================
       Branding
    ========================= */
    public $logo;
    public $favicon;
    public ?string $current_logo = null;
    public ?string $current_favicon = null;

    /* =========================
       Colors
    ========================= */
    public string $secondary_color = '';
    public string $accent_color = '';
    public string $background_color = '';

    /* =========================
       SEO
    ========================= */
    public string $meta_title = '';
    public string $meta_description = '';
    public string $keywords = '';

    /* =========================
    Custom Scripts
    ========================= */
    public string $head_script = '';
    public string $footer_script = '';


    /* =========================
       Lifecycle
    ========================= */
    public function mount(SettingsService $settings): void
    {
        // Authorization (super-admin bypass handled globally)
        $this->authorize('access-dashboard');

        // General
        $this->site_name        = (string) $settings->get('site_name', '');
        $this->site_description = (string) $settings->get('site_description', '');

        // Colors
        $this->secondary_color  = (string) $settings->get('colors.secondary', '');
        $this->accent_color     = (string) $settings->get('colors.accent', '');
        $this->background_color = (string) $settings->get('colors.background', '');

        // SEO
        $this->meta_title       = (string) $settings->get('seo.meta_title', '');
        $this->meta_description = (string) $settings->get('seo.meta_description', '');
        $this->keywords         = (string) $settings->get('seo.keywords', '');

        // Branding (previews)
        $this->current_logo    = $settings->get('branding.logo');
        $this->current_favicon = $settings->get('branding.favicon');

        // Custom Scripts
        $this->head_script   = (string) $settings->get('scripts.head', '');
        $this->footer_script = (string) $settings->get('scripts.footer', '');
    }

    /* =========================
       Save All Settings
    ========================= */
    public function save(SettingsService $settings): void
    {
        // General
        $settings->set('site_name', $this->site_name, 'string', 'general');
        $settings->set('site_description', $this->site_description, 'text', 'general');

        // Branding
        if ($this->logo) {
            $path = $this->logo->store('branding', 'public');
            $settings->set('branding.logo', $path, 'image', 'branding');
            $this->current_logo = $path;
        }

        if ($this->favicon) {
            $path = $this->favicon->store('branding', 'public');
            $settings->set('branding.favicon', $path, 'image', 'branding');
            $this->current_favicon = $path;
        }

        // Colors
        $settings->set('colors.secondary', $this->secondary_color, 'color', 'colors');
        $settings->set('colors.accent', $this->accent_color, 'color', 'colors');
        $settings->set('colors.background', $this->background_color, 'color', 'colors');

        // SEO
        $settings->set('seo.meta_title', $this->meta_title, 'string', 'seo');
        $settings->set('seo.meta_description', $this->meta_description, 'text', 'seo');
        $settings->set('seo.keywords', $this->keywords, 'text', 'seo');

        // Custom Scripts
        $settings->set('scripts.head', $this->head_script, 'code', 'scripts');
        $settings->set('scripts.footer', $this->footer_script, 'code', 'scripts');

        // Toast (project standard)
        $this->dispatch(
            'toast',
            message: __('Settings have been saved successfully'),
            type: 'success'
        );
    }

    /* =========================
       System Tools
    ========================= */

    /**
     * php artisan storage:link
     */
    public function storageLink(): void
    {
        $this->authorize('access-dashboard');

        try {
            Artisan::call('storage:unlink');
            Artisan::call('storage:link');

            $this->toastSuccess(__('Storage link created successfully'));
        } catch (\Throwable $e) {
            $this->toastError($e->getMessage());
        }
    }

    /**
     * php artisan cache:clear (+ config & view)
     */
    public function clearCache(): void
    {
        $this->authorize('access-dashboard');

        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            $this->toastSuccess(__('Cache cleared successfully'));
        } catch (\Throwable $e) {
            $this->toastError($e->getMessage());
        }
    }

    /* =========================
       Toast Helpers
       (consistent with project)
    ========================= */
    protected function toastSuccess(string $message): void
    {
        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: { type: 'success', message: '{$message}' }
                })
            );
        ");
    }

    protected function toastError(string $message): void
    {
        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: { type: 'error', message: '{$message}' }
                })
            );
        ");
    }
};

?>
<div class="space-y-8">

    {{-- ================= HEADER ================= --}}
    @include('partials.settings-heading', [
    'title' => __('Settings'),
    'description' => __('Manage global site configuration'),
    'icon' => 'cog-6-tooth',
])


    {{-- ================= TABS ================= --}}
    <div class="flex flex-wrap gap-2">
        @foreach ([
            'general'  => ['label' => __('General'),  'icon' => 'adjustments-horizontal'],
            'branding' => ['label' => __('Branding'), 'icon' => 'photo'],
            'colors'   => ['label' => __('Colors'),   'icon' => 'paint-brush'],
            'seo'      => ['label' => __('SEO'),      'icon' => 'magnifying-glass'],
            'scripts'  => ['label' => __('Scripts'), 'icon' => 'code-bracket'],
            'system'   => ['label' => __('System'),   'icon' => 'server'],
        ] as $key => $t)
            <button
                wire:click="$set('tab','{{ $key }}')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm transition
                {{ $tab === $key
                    ? 'bg-accent text-white shadow'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:opacity-80' }}">

                <flux:icon name="{{ $t['icon'] }}" class="w-4 h-4"/>
                {{ $t['label'] }}
            </button>
        @endforeach
    </div>

    {{-- ================= CONTENT CARD ================= --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
               bg-white dark:bg-slate-900/90
               p-6 space-y-6">

        {{-- ========== GENERAL ========== --}}
        @if ($tab === 'general')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs text-slate-500">{{ __('Site name') }}</label>
                    <input wire:model.defer="site_name" class="input w-full mt-1">
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs text-slate-500">{{ __('Site description') }}</label>
                    <textarea wire:model.defer="site_description"
                              rows="3"
                              class="textarea w-full mt-1"></textarea>
                </div>
            </div>
        @endif

        {{-- ========== BRANDING ========== --}}
        @if ($tab === 'branding')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Logo --}}
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-800
                           p-4 space-y-3">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <flux:icon.photo class="w-4 h-4 text-slate-400"/>
                        {{ __('Logo') }}
                    </div>

                    <input type="file" wire:model="logo">

                    @if ($logo || $current_logo)
                        <div
                            class="mt-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800
                                   flex justify-center">
                            <img
                                src="{{ $logo ? $logo->temporaryUrl() : asset('storage/'.$current_logo) }}"
                                class="h-16 object-contain">
                        </div>
                    @endif
                </div>

                {{-- Favicon --}}
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-800
                           p-4 space-y-3">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <flux:icon.star class="w-4 h-4 text-slate-400"/>
                        {{ __('Favicon') }}
                    </div>

                    <input type="file" wire:model="favicon">

                    @if ($favicon || $current_favicon)
                        <div
                            class="mt-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800
                                   flex justify-center">
                            <img
                                src="{{ $favicon ? $favicon->temporaryUrl() : asset('storage/'.$current_favicon) }}"
                                class="h-10 w-10 object-contain">
                        </div>
                    @endif
                </div>

            </div>
        @endif

        {{-- ========== COLORS ========== --}}
        @if ($tab === 'colors')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div>
                    <label class="text-xs text-slate-500">{{ __('Secondary color') }}</label>
                    <input type="color" wire:model.defer="secondary_color"
                           class="w-full h-10 rounded-lg border mt-1">
                </div>

                <div>
                    <label class="text-xs text-slate-500">{{ __('Accent color') }}</label>
                    <input type="color" wire:model.defer="accent_color"
                           class="w-full h-10 rounded-lg border mt-1">
                </div>

                <div>
                    <label class="text-xs text-slate-500">{{ __('Background color') }}</label>
                    <input type="color" wire:model.defer="background_color"
                           class="w-full h-10 rounded-lg border mt-1">
                </div>

            </div>
        @endif

        {{-- ========== SEO ========== --}}
        @if ($tab === 'seo')
            <div class="space-y-4">

                <div>
                    <label class="text-xs text-slate-500">{{ __('Meta title') }}</label>
                    <input wire:model.defer="meta_title"
                           class="input w-full mt-1">
                </div>

                <div>
                    <label class="text-xs text-slate-500">{{ __('Meta description') }}</label>
                    <textarea wire:model.defer="meta_description"
                              rows="3"
                              class="textarea w-full mt-1"></textarea>
                </div>

                <div>
                    <label class="text-xs text-slate-500">{{ __('Keywords') }}</label>
                    <textarea wire:model.defer="keywords"
                              rows="2"
                              class="textarea w-full mt-1"
                              placeholder="keyword1, keyword2"></textarea>
                </div>

            </div>
        @endif

        {{-- ========== SCRIPTS ========== --}}
@if ($tab === 'scripts')
    <div class="space-y-6">

        {{-- Head Script --}}
        <div>
            <label class="text-xs text-slate-500">
                {{ __('Head scripts') }}
            </label>

            <textarea
                wire:model.defer="head_script"
                rows="6"
                class="textarea w-full mt-1 font-mono text-sm"
                placeholder="<!-- Google Analytics, Meta Pixel, etc -->"></textarea>

            <p class="text-xs text-slate-400 mt-1">
                {{ __('This code will be injected inside <head>') }}
            </p>
        </div>

        <hr class="hr">
        {{-- Footer Script --}}
        <div>
            <label class="text-xs text-slate-500">
                {{ __('Footer scripts') }}
            </label>

            <textarea
                wire:model.defer="footer_script"
                rows="6"
                class="textarea w-full mt-1 font-mono text-sm"
                placeholder="<!-- Chat widgets, tracking scripts -->"></textarea>

            <p class="text-xs text-slate-400 mt-1">
                {{ __('This code will be injected before </body>') }}
            </p>
        </div>

    </div>
@endif


        {{-- ========== SYSTEM ========== --}}
        @if ($tab === 'system')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <button
                    wire:click="storageLink"
                    class="flex items-center gap-4 p-5 rounded-2xl
                           bg-sky-500/10 hover:bg-sky-500/20 transition">
                    <flux:icon.folder-plus class="w-6 h-6 text-sky-600"/>
                    <div class="text-left">
                        <p class="font-medium">{{ __('Create storage link') }}</p>
                        <p class="text-xs text-slate-500">php artisan storage:link</p>
                    </div>
                </button>

                <button
                    wire:click="clearCache"
                    class="flex items-center gap-4 p-5 rounded-2xl
                           bg-amber-500/10 hover:bg-amber-500/20 transition">
                    <flux:icon.arrow-path class="w-6 h-6 text-amber-600"/>
                    <div class="text-left">
                        <p class="font-medium">{{ __('Clear cache') }}</p>
                        <p class="text-xs text-slate-500">cache · config · view</p>
                    </div>
                </button>

            </div>
        @endif

    </div>

    {{-- ================= SAVE BAR ================= --}}
    <div class="flex justify-end pt-2">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 px-6 py-2 rounded-lg
                   bg-accent text-white hover:opacity-90 transition
                   disabled:opacity-50">

            <flux:icon.check class="w-4 h-4"/>
            {{ __('Save settings') }}
        </button>
    </div>

</div>
