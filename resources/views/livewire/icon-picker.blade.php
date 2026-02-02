<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Modelable;


new class extends Component {

    #[Modelable]
    public ?string $model = null;

    public bool $open = false;
    public string $search = '';
    public int $perPage = 48;   // عدد الأيقونات المعروضة
    public int $step = 48;      // كم نزيد عند Load more


    protected array $icons = [
        'academic-cap',
        'adjustments-horizontal',
        'adjustments-vertical',
        'archive-box-arrow-down',
        'archive-box-x-mark',
        'archive-box',
        'arrow-down-circle',
        'arrow-down-left',
        'arrow-down-on-square-stack',
        'arrow-down-on-square',
        'arrow-down-right',
        'arrow-down-tray',
        'arrow-down',
        'arrow-left-circle',
        'arrow-left-end-on-rectangle',
        'arrow-left-on-rectangle',
        'arrow-left-start-on-rectangle',
        'arrow-left',
        'arrow-long-down',
        'arrow-long-left',
        'arrow-long-right',
        'arrow-long-up',
        'arrow-path-rounded-square',
        'arrow-path',
        'arrow-right-circle',
        'arrow-right-end-on-rectangle',
        'arrow-right-on-rectangle',
        'arrow-right-start-on-rectangle',
        'arrow-right',
        'arrow-small-down',
        'arrow-small-left',
        'arrow-small-right',
        'arrow-small-up',
        'arrow-top-right-on-square',
        'arrow-trending-down',
        'arrow-trending-up',
        'arrow-turn-down-left',
        'arrow-turn-down-right',
        'arrow-turn-left-down',
        'arrow-turn-left-up',
        'arrow-turn-right-down',
        'arrow-turn-right-up',
        'arrow-turn-up-left',
        'arrow-turn-up-right',
        'arrow-up-circle',
        'arrow-up-left',
        'arrow-up-on-square-stack',
        'arrow-up-on-square',
        'arrow-up-right',
        'arrow-up-tray',
        'arrow-up',
        'arrow-uturn-down',
        'arrow-uturn-left',
        'arrow-uturn-right',
        'arrow-uturn-up',
        'arrows-pointing-in',
        'arrows-pointing-out',
        'arrows-right-left',
        'arrows-up-down',
        'at-symbol',
        'backspace',
        'backward',
        'banknotes',
        'bars-2',
        'bars-3-bottom-left',
        'bars-3-bottom-right',
        'bars-3-center-left',
        'bars-3',
        'bars-4',
        'bars-arrow-down',
        'bars-arrow-up',
        'battery-0',
        'battery-100',
        'battery-50',
        'beaker',
        'bell-alert',
        'bell-slash',
        'bell-snooze',
        'bell',
        'bold',
        'bolt-slash',
        'bolt',
        'book-open',
        'bookmark-slash',
        'bookmark-square',
        'bookmark',
        'briefcase',
        'bug-ant',
        'building-library',
        'building-office-2',
        'building-office',
        'building-storefront',
        'cake',
        'calculator',
        'calendar-date-range',
        'calendar-days',
        'calendar',
        'camera',
        'chart-bar-square',
        'chart-bar',
        'chart-pie',
        'chat-bubble-bottom-center-text',
        'chat-bubble-bottom-center',
        'chat-bubble-left-ellipsis',
        'chat-bubble-left-right',
        'chat-bubble-left',
        'chat-bubble-oval-left-ellipsis',
        'chat-bubble-oval-left',
        'check-badge',
        'check-circle',
        'check',
        'chevron-double-down',
        'chevron-double-left',
        'chevron-double-right',
        'chevron-double-up',
        'chevron-down',
        'chevron-left',
        'chevron-right',
        'chevron-up-down',
        'chevron-up',
        'circle-stack',
        'clipboard-document-check',
        'clipboard-document-list',
        'clipboard-document',
        'clipboard',
        'clock',
        'cloud-arrow-down',
        'cloud-arrow-up',
        'cloud',
        'code-bracket-square',
        'code-bracket',
        'cog-6-tooth',
        'cog-8-tooth',
        'cog',
        'command-line',
        'computer-desktop',
        'cpu-chip',
        'credit-card',
        'cube-transparent',
        'cube',
        'currency-bangladeshi',
        'currency-dollar',
        'currency-euro',
        'currency-pound',
        'currency-rupee',
        'currency-yen',
        'cursor-arrow-rays',
        'cursor-arrow-ripple',
        'device-phone-mobile',
        'device-tablet',
        'divide',
        'document-arrow-down',
        'document-arrow-up',
        'document-chart-bar',
        'document-check',
        'document-currency-bangladeshi',
        'document-currency-dollar',
        'document-currency-euro',
        'document-currency-pound',
        'document-currency-rupee',
        'document-currency-yen',
        'document-duplicate',
        'document-magnifying-glass',
        'document-minus',
        'document-plus',
        'document-text',
        'document',
        'ellipsis-horizontal-circle',
        'ellipsis-horizontal',
        'ellipsis-vertical',
        'envelope-open',
        'envelope',
        'equals',
        'exclamation-circle',
        'exclamation-triangle',
        'eye-dropper',
        'eye-slash',
        'eye',
        'face-frown',
        'face-smile',
        'film',
        'finger-print',
        'fire',
        'flag',
        'folder-arrow-down',
        'folder-minus',
        'folder-open',
        'folder-plus',
        'folder',
        'forward',
        'funnel',
        'gif',
        'gift-top',
        'gift',
        'globe-alt',
        'globe-americas',
        'globe-asia-australia',
        'globe-europe-africa',
        'h1',
        'h2',
        'h3',
        'hand-raised',
        'hand-thumb-down',
        'hand-thumb-up',
        'hashtag',
        'heart',
        'home-modern',
        'home',
        'identification',
        'inbox-arrow-down',
        'inbox-stack',
        'inbox',
        'information-circle',
        'italic',
        'key',
        'language',
        'lifebuoy',
        'light-bulb',
        'link-slash',
        'link',
        'list-bullet',
        'lock-closed',
        'lock-open',
        'magnifying-glass-circle',
        'magnifying-glass-minus',
        'magnifying-glass-plus',
        'magnifying-glass',
        'map-pin',
        'map',
        'megaphone',
        'microphone',
        'minus-circle',
        'minus-small',
        'minus',
        'moon',
        'musical-note',
        'newspaper',
        'no-symbol',
        'numbered-list',
        'paint-brush',
        'paper-airplane',
        'paper-clip',
        'pause-circle',
        'pause',
        'pencil-square',
        'pencil',
        'percent-badge',
        'phone-arrow-down-left',
        'phone-arrow-up-right',
        'phone-x-mark',
        'phone',
        'photo',
        'play-circle',
        'play-pause',
        'play',
        'plus-circle',
        'plus-small',
        'plus',
        'power',
        'presentation-chart-bar',
        'presentation-chart-line',
        'printer',
        'puzzle-piece',
        'qr-code',
        'question-mark-circle',
        'queue-list',
        'radio',
        'receipt-percent',
        'receipt-refund',
        'rectangle-group',
        'rectangle-stack',
        'rocket-launch',
        'rss',
        'scale',
        'scissors',
        'server-stack',
        'server',
        'share',
        'shield-check',
        'shield-exclamation',
        'shopping-bag',
        'shopping-cart',
        'signal-slash',
        'signal',
        'slash',
        'sparkles',
        'speaker-wave',
        'speaker-x-mark',
        'square-2-stack',
        'square-3-stack-3d',
        'squares-2x2',
        'squares-plus',
        'star',
        'stop-circle',
        'stop',
        'strikethrough',
        'sun',
        'swatch',
        'table-cells',
        'tag',
        'ticket',
        'trash',
        'trophy',
        'truck',
        'tv',
        'underline',
        'user-circle',
        'user-group',
        'user-minus',
        'user-plus',
        'user',
        'users',
        'variable',
        'video-camera-slash',
        'video-camera',
        'view-columns',
        'viewfinder-circle',
        'wallet',
        'wifi',
        'window',
        'wrench-screwdriver',
        'wrench',
        'x-circle',
        'x-mark',
    ];

    public function openPicker(): void
    {
        $this->search = '';
        $this->open = true;
    }

    public function select(string $icon): void
    {
        $this->model = $icon;
        $this->open = false;
    }

    public function getFilteredIconsProperty(): array
    {
        $search = strtolower($this->search);

        return collect($this->icons)
            ->filter(
                fn($icon) =>
                $search === '' || str_contains($icon, $search)
            )
            ->take($this->perPage)
            ->values()
            ->all();
    }

    public function loadMore(): void
    {
        if ($this->perPage >= $this->totalIcons) {
            return;
        }

        $this->perPage += $this->step;
    }

    public function getTotalIconsProperty(): int
    {
        $search = strtolower($this->search);

        return collect($this->icons)
            ->filter(
                fn($icon) =>
                $search === '' || str_contains($icon, $search)
            )
            ->count();
    }


};
?>

<div class="flex items-center gap-3">

    {{-- Preview --}}
    <div class="w-10 h-10 rounded-lg flex items-center justify-center
               bg-slate-100 dark:bg-slate-800
               ring-1 ring-slate-200 dark:ring-slate-700">

        @if ($model)
            <flux:icon name="{{ $model }}" class="w-5 h-5 text-accent" />
        @else
            <span class="text-xs text-slate-400">—</span>
        @endif
    </div>

    {{-- Open --}}
    <button type="button" wire:click="openPicker" class="px-3 py-2 rounded-lg text-xs
               bg-slate-100 dark:bg-slate-800
               hover:opacity-80 transition">
        {{ __('Choose icon') }}
    </button>

    {{-- Clear --}}
    @if ($model)
        <button type="button" wire:click="$set('model', null)" class="text-xs text-red-500 hover:underline">
            {{ __('Clear') }}
        </button>
    @endif

    {{-- Modal --}}
    @if ($open)
        <div class="fixed inset-0 z-50" wire:keydown.escape.window="$set('open', false)">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('open', false)">
            </div>

            <div class="relative h-full w-full flex items-center justify-center p-4">
                <div class="w-full max-w-3xl h-[80vh] rounded-2xl
           bg-white dark:bg-slate-900
           border border-slate-200 dark:border-slate-800
           shadow-2xl
           flex flex-col overflow-hidden">


                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800
                                                           flex items-center justify-between">
                        <h3 class="text-base font-semibold">
                            {{ __('Choose an icon') }}
                        </h3>

                        <button wire:click="$set('open', false)" aria-label="Close"
                            class="text-slate-400 hover:text-slate-600">
                            ✕
                        </button>

                    </div>

                    {{-- Search --}}
                    <div class="px-6 pt-4">
                        <input type="text" wire:model.debounce.300ms="search" wire:keydown="$set('perPage', 48)"
                            placeholder="{{ __('Search icons...') }}" class="w-full px-4 py-2 rounded-xl
                       border border-slate-200 dark:border-slate-800
                       bg-white dark:bg-slate-900 text-sm">
                    </div>

                    {{-- Icons --}}
                    <div class="px-6 pb-2 text-xs text-slate-500">
                        {{ min($perPage, $this->totalIcons) }} / {{ $this->totalIcons }} icons
                    </div>


                    <div class="flex-1 overflow-y-auto px-6">
                        <div class="grid grid-cols-5 sm:grid-cols-6 gap-4 py-6
                   transition-opacity duration-200" wire:loading.class="opacity-60" wire:target="loadMore">

                            @forelse ($this->filteredIcons as $icon)
                                            <button wire:click="select('{{ $icon }}')" class="group w-12 h-12 rounded-xl flex items-center justify-center transition
                                                                                {{ $model === $icon
                                ? 'bg-accent/20 ring-2 ring-accent'
                                : 'bg-slate-100 dark:bg-slate-800 hover:bg-accent/10' }}">

                                                <div class="relative">
                                                    <flux:icon name="{{ $icon }}" class="w-6 h-6 text-slate-600 group-hover:text-accent" />

                                                    <span class="absolute -bottom-7 left-1/2 -translate-x-1/2
                                                                     opacity-0 group-hover:opacity-100 transition
                                                                     text-[10px] px-2 py-0.5 rounded
                                                                     bg-black text-white whitespace-nowrap">
                                                        {{ $icon }}
                                                    </span>
                                                </div>

                                            </button>
                            @empty
                                <div class="col-span-full text-center py-10">
                                    <p class="text-sm text-slate-500">
                                        {{ __('No icons match your search') }}
                                    </p>
                                </div>
                            @endforelse

                        </div>
                    </div>


                    @if ($perPage < $this->totalIcons)
                        <div class="border-t border-slate-200 dark:border-slate-800 p-4">

                            <div class="flex justify-center pt-4">

                                <button wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore" class="relative px-6 py-2 text-sm rounded-lg
                           bg-slate-100 dark:bg-slate-800
                           hover:bg-slate-200 dark:hover:bg-slate-700
                           transition
                           disabled:opacity-60 disabled:cursor-not-allowed">

                                    {{-- النص الافتراضي --}}
                                    <span wire:loading.remove wire:target="loadMore">
                                        {{ __('Load more') }}
                                    </span>

                                    {{-- حالة التحميل --}}
                                    <span wire:loading wire:target="loadMore" class="flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin text-slate-600" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                            </path>
                                        </svg>

                                        {{ __('Loading...') }}
                                    </span>

                                </button>

                            </div>
                        </div>
                    @endif




                </div>
            </div>
        </div>
    @endif
</div>