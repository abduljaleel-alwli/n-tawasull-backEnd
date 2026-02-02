@props([
    'title',
    'value',

    // Optional
    'icon' => null,
    'color' => 'slate',
    'trend' => null,
    'sparkline' => null, // [10,20,15,30]
    'href' => null, // route or url
])

@php
    $colors = [
        'slate' => ['bg' => 'bg-slate-500/10', 'text' => 'text-slate-600', 'stroke' => '#475569'],
        'sky' => ['bg' => 'bg-sky-500/10', 'text' => 'text-sky-600', 'stroke' => '#0284c7'],
        'emerald' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-600', 'stroke' => '#059669'],
        'rose' => ['bg' => 'bg-rose-500/10', 'text' => 'text-rose-600', 'stroke' => '#e11d48'],
        'amber' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-600', 'stroke' => '#d97706'],
        'indigo' => ['bg' => 'bg-indigo-500/10', 'text' => 'text-indigo-600', 'stroke' => '#4f46e5'],
        'violet' => ['bg' => 'bg-violet-500/10', 'text' => 'text-violet-600', 'stroke' => '#7c3aed'],
        'green' => ['bg' => 'bg-green-500/10', 'text' => 'text-green-600', 'stroke' => '#16a34a'],
    ];

    $c = $colors[$color] ?? $colors['slate'];
    $trendUp = is_numeric($trend) && $trend > 0;

    // Sparkline points
    $points = '';
    if (is_array($sparkline) && count($sparkline) > 1) {
        $max = max($sparkline);
        $min = min($sparkline);
        $range = max($max - $min, 1);

        foreach ($sparkline as $i => $val) {
            $x = ($i / (count($sparkline) - 1)) * 100;
            $y = 30 - (($val - $min) / $range) * 30;
            $points .= "{$x},{$y} ";
        }
    }
@endphp

@php
    $Tag = $href ? 'a' : 'div';
@endphp

<{{ $Tag }} @if ($href) href="{{ $href }}"
        wire:navigate @endif
    class="block relative rounded-2xl
    border border-slate-200 dark:border-slate-800
    bg-white dark:bg-slate-900/90
    p-4 sm:p-5
    overflow-hidden
           {{ $href ? 'cursor-pointer hover:ring-1 hover:ring-slate-300 dark:hover:ring-slate-700' : '' }}">


    {{-- Skeleton --}}
    <div wire:loading class="animate-pulse space-y-3">
        <div class="h-3 w-24 bg-slate-200 dark:bg-slate-700 rounded"></div>
        <div class="h-7 w-32 bg-slate-200 dark:bg-slate-700 rounded"></div>
        <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded"></div>
    </div>

    {{-- Content --}}
    <div wire:loading.remove class="space-y-2">

        <div class="flex items-start justify-between">

            {{-- Left --}}
            <div>
                <p class="text-xs text-slate-500">
                    {{ __($title) }}
                </p>

                <p class="mt-1 text-xl sm:text-2xl font-semibold">
                    {{ $value ?? 0 }}
                </p>

                {{-- Trend --}}
                @if (!is_null($trend))
                    <p
                        class="text-xs font-medium
                        {{ $trendUp ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $trendUp ? '▲' : '▼' }} {{ abs($trend) }}%
                    </p>
                @endif
            </div>

            {{-- Icon --}}
            @if ($icon)
                <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center
                           {{ $c['bg'] }} {{ $c['text'] }}">
                    <flux:icon :name="$icon" variant="solid" size="20" />
                </div>
            @endif
            @if ($href)
                <span class="absolute bottom-3 right-3 text-slate-400 hidden sm:block">
                    <flux:icon name="arrow-right" size="14" />
                </span>
            @endif
        </div>

        {{-- Sparkline --}}
        @if ($points)
            <svg viewBox="0 0 100 30" class="w-full h-6 sm:h-8 mt-2">
                <polyline fill="none" stroke="{{ $c['stroke'] }}" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" points="{{ trim($points) }}" />
            </svg>
        @endif

    </div>

    </{{ $Tag }}>
