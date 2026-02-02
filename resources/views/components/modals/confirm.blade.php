@props([
    'show' => false, // boolean: هل المودال ظاهر؟
    'title' => __('Confirm action'),
    'message' => null, // string|html
    'type' => 'warning', // danger|warning|info|success
    'confirmText' => __('Confirm'),
    'cancelText' => __('Cancel'),
    'confirmAction' => null, // string: wire:click="..."
    'cancelAction' => null, // string: wire:click="..."
    'confirmDisabled' => false, // boolean
    'confirmLoadingTarget' => null, // string: wire:target="methodName"
])

@php
    $typeStyles = [
        'danger' => [
            'ring' => 'ring-red-500/20',
            'iconBg' => 'bg-red-500/10',
            'iconText' => 'text-red-500',
            'button' => 'bg-red-600 hover:bg-red-700',
        ],
        'warning' => [
            'ring' => 'ring-amber-500/20',
            'iconBg' => 'bg-amber-500/10',
            'iconText' => 'text-amber-500',
            'button' => 'bg-amber-600 hover:bg-amber-700',
        ],
        'info' => [
            'ring' => 'ring-sky-500/20',
            'iconBg' => 'bg-sky-500/10',
            'iconText' => 'text-sky-500',
            'button' => 'bg-sky-600 hover:bg-sky-700',
        ],
        'success' => [
            'ring' => 'ring-emerald-500/20',
            'iconBg' => 'bg-emerald-500/10',
            'iconText' => 'text-emerald-500',
            'button' => 'bg-emerald-600 hover:bg-emerald-700',
        ],
    ];

    $s = $typeStyles[$type] ?? $typeStyles['warning'];
@endphp

@if ($show)
    <div class="fixed inset-0 z-50" x-data
        @if ($cancelAction) x-on:keydown.escape.window="{{ $cancelAction }}" @endif>
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
            @if ($cancelAction) {!! $cancelAction !!} @endif></div>

        {{-- Center wrapper --}}
        <div class="relative h-full w-full flex items-start justify-center px-4 py-6 sm:py-10">
            {{-- Modal container --}}
            <div
                class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800 shadow-xl
                    max-h-[90vh] flex flex-col overflow-hidden ring-1 {{ $s['ring'] }}">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $s['iconBg'] }}">
                            <span class="text-lg {{ $s['iconText'] }}">!</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ $title }}
                            </h3>
                        </div>
                    </div>

                    <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        @if ($cancelAction) {!! $cancelAction !!} @endif aria-label="Close">
                        ✕
                    </button>
                </div>

                {{-- Body (scrollable) --}}
                <div class="p-6 overflow-y-auto space-y-3">
                    @if ($message)
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {!! is_string($message) ? e($message) : $message !!}
                        </div>
                    @endif

                    {{-- Slot for extra content --}}
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                <div
                    class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button"
                        class="px-4 py-2 rounded-lg text-sm bg-slate-200 dark:bg-slate-800
                           text-slate-800 dark:text-slate-100 hover:opacity-80 transition"
                        @if ($cancelAction) {!! $cancelAction !!} @endif>
                        {{ $cancelText }}
                    </button>

                    <button type="button"
                        class="px-4 py-2 rounded-lg text-sm text-white transition {{ $s['button'] }}
                           disabled:opacity-60 disabled:cursor-not-allowed"
                        @if ($confirmAction) {!! $confirmAction !!} @endif
                        @if ($confirmLoadingTarget) wire:loading.attr="disabled" wire:target="{{ $confirmLoadingTarget }}" @endif
                        @disabled($confirmDisabled)>
                        @if ($confirmLoadingTarget)
                            <span wire:loading wire:target="{{ $confirmLoadingTarget }}">{{ __('Loading...') }}</span>
                            <span wire:loading.remove
                                wire:target="{{ $confirmLoadingTarget }}">{{ $confirmText }}</span>
                        @else
                            {{ $confirmText }}
                        @endif
                    </button>
                </div>

            </div>
        </div>
    </div>
@endif
