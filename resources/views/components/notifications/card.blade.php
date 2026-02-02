@props(['notification'])

@php
    $isUnread = is_null($notification->read_at);

    // Core data
    $type = $notification->data['type'] ?? 'info';
    $channel = $notification->data['channel'] ?? 'system';

    // Styles
    $styles = [
        'success' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-500'],
        'error' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-500'],
        'warning' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-500'],
        'info' => ['bg' => 'bg-sky-500/10', 'text' => 'text-sky-500'],
    ];

    $s = $styles[$type] ?? $styles['info'];

    // Badges (semantic, not visual-only)
    $badges = [
        'contact_message' => ['bg' => 'bg-sky-500/10', 'text' => 'text-sky-600', 'label' => __('Contact')],
        'contact_reply' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-600', 'label' => __('Reply')],
        'user_action' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-600', 'label' => __('User')],
    ];

    $badge = $badges[$notification->data['type'] ?? null] ?? null;

    // Tooltip
    $channelLabels = [
        'system' => __('System notification'),
        'mail' => __('Email notification'),
        'security' => __('Security alert'),
    ];

    $tooltip = $channelLabels[$channel] ?? __('Notification');

    // Content resolution (same logic as rich version)
    $title = $notification->data['title'] ?? ($notification->data['subject'] ?? __('Notification'));

    $content =
        $notification->data['body'] ?? ($notification->data['message'] ?? ($notification->data['preview'] ?? null));

    $meta = $notification->data['meta'] ?? [];
@endphp

<div wire:key="notification-{{ $notification->id }}"
    class="px-6 py-4 flex items-start gap-4
           rounded-2xl
           border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90
           transition
           {{ $isUnread ? 'hover:bg-slate-50 dark:hover:bg-slate-800/40' : 'opacity-80 hover:opacity-100' }}">

    {{-- Icon --}}
    <div class="mt-1 w-10 h-10 rounded-xl flex items-center justify-center shrink-0
               {{ $s['bg'] }} text-accent"
        title="{{ $tooltip }}">
        @switch($channel)
            @case('mail')
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5H4.5A2.25 2.25 0 0 1 2.25 17.25V6.75l9.75 6 9.75-6z" />
                </svg>
            @break

            @case('security')
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.01M4.5 5.25L12 2.25l7.5 3v6.75
                             c0 5.385-3.75 8.25-7.5 9.75
                             -3.75-1.5-7.5-4.365-7.5-9.75V5.25z" />
                </svg>
            @break

            @default
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.657A4.5 4.5 0 0 1 12 18.75
                             a4.5 4.5 0 0 1-2.857-1.093
                             M6.75 9a5.25 5.25 0 0 1 10.5 0
                             v3.75l1.5 1.5H5.25l1.5-1.5V9z" />
                </svg>
        @endswitch

    </div>

    {{-- Content --}}
    <div class="flex-1 space-y-1 min-w-0">

        {{-- Title + badge --}}
        <div class="flex items-center gap-2">
            <h4
                class="text-sm font-medium truncate
                {{ $isUnread ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-300' }}">
                {{ $title }}
            </h4>

            @if ($badge)
                <span
                    class="px-2 py-0.5 rounded-full text-[11px] font-medium
                             {{ $badge['bg'] }} {{ $badge['text'] }}">
                    {{ $badge['label'] }}
                </span>
            @endif
        </div>

        {{-- Main content --}}
        <p class="text-xs text-slate-500 truncate">
            {{ $content ?: __('No details available') }}
        </p>

        {{-- Meta (rich but minimal UI) --}}
        @if (!empty($meta))
            <div class="mt-2 text-[11px] text-slate-400 space-y-0.5">
                @isset($meta['name'])
                    <div>{{ __('Name') }}: {{ $meta['name'] }}</div>
                @endisset

                @isset($meta['email'])
                    <div>{{ __('Email') }}: {{ $meta['email'] }}</div>
                @endisset
            </div>
        @endif

        {{-- Optional link --}}
        @if (!empty($notification->data['url']))
            <a href="{{ $notification->data['url'] }}"
                class="inline-block mt-1 text-[11px] font-medium text-accent hover:underline">
                {{ __('View details') }}
            </a>
        @endif

        <p class="text-[11px] text-slate-400 mt-2">
            {{ $notification->created_at->diffForHumans() }}
        </p>
    </div>

    {{-- Action --}}
    <div class="flex items-center gap-3 shrink-0">
        @if ($isUnread)
            <span class="mt-1 w-2 h-2 rounded-full bg-accent"></span>

            <button wire:click="markAsRead('{{ $notification->id }}')"
                class="text-xs px-3 py-1 rounded-full
                       bg-accent/10 text-accent
                       hover:bg-accent/20 transition">
                {{ __('Mark as read') }}
            </button>
        @else
            <span class="text-xs text-slate-400">
                {{ __('Read') }}
            </span>
        @endif
    </div>

</div>
