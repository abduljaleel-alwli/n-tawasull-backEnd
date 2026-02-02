<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Notifications\DatabaseNotification;

new class extends Component {
    use AuthorizesRequests;

    public string $filter = 'all'; // all | unread | read

    public function mount(): void
    {
        $this->authorize('access-dashboard');
    }

    /* =====================
        Actions
    ===================== */

    public function markAsRead(string $id): void
    {
        DatabaseNotification::where('id', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        DatabaseNotification::whereNull('read_at')->update(['read_at' => now()]);
    }

    /* =====================
        Data
    ===================== */

    public function notifications()
    {
        $query = DatabaseNotification::query();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->latest()->get();
    }

    public function stats(): array
    {
        return [
            'total' => DatabaseNotification::count(),
            'unread' => DatabaseNotification::whereNull('read_at')->count(),
            'read' => DatabaseNotification::whereNotNull('read_at')->count(),
        ];
    }
};
?>

<div class="space-y-8">

    {{-- ================= Stats cards ================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

        {{-- Total --}}
        <button wire:click="$set('filter', 'all')"
            class="text-left rounded-2xl p-4
               border border-slate-200 dark:border-slate-800
               bg-white dark:bg-slate-900/80
               transition
               {{ $filter === 'all' ? 'ring-2 ring-accent/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">
                        {{ __('Total notifications') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ $this->stats()['total'] }}
                    </p>
                </div>

                {{-- Heroicon: Bell --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.657A4.5 4.5 0 0112 18.75a4.5 4.5 0 01-2.857-1.093M6.75 9a5.25 5.25 0 0110.5 0v3.75l1.5 1.5H5.25l1.5-1.5V9z" />
                </svg>
            </div>
        </button>


        {{-- Unread --}}
        <button wire:click="$set('filter', 'unread')"
            class="text-left rounded-2xl p-4
               bg-sky-500/10 hover:bg-sky-500/20
               transition
               {{ $filter === 'unread' ? 'ring-2 ring-sky-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-sky-600">
                        {{ __('Unread notifications') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-sky-700">
                        {{ $this->stats()['unread'] }}
                    </p>
                </div>

                {{-- Heroicon: Envelope --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0l-9.75 6-9.75-6" />
                </svg>
            </div>
        </button>


        {{-- Read --}}
        <button wire:click="$set('filter', 'read')"
            class="text-left rounded-2xl p-4
               bg-emerald-500/10 hover:bg-emerald-500/20
               transition
               {{ $filter === 'read' ? 'ring-2 ring-emerald-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-emerald-600">
                        {{ __('Read notifications') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">
                        {{ $this->stats()['read'] }}
                    </p>
                </div>

                {{-- Heroicon: Check Circle --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-emerald-600" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15l3.75-4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </button>

    </div>



    {{-- ================= Toolbar ================= --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
               bg-white dark:bg-slate-900/90
               p-4 flex flex-col sm:flex-row gap-4
               justify-between items-center">

        {{-- Filters --}}
        <div class="flex items-center gap-2">
            @foreach (['all' => __('All'), 'unread' => __('Unread'), 'read' => __('Read')] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')"
                    class="px-4 py-2 rounded-full text-sm transition
                        {{ $filter === $key
                            ? 'bg-accent text-white shadow'
                            : 'bg-slate-100 dark:bg-slate-800
                                                                                                                                                       text-slate-700 dark:text-slate-200
                                                                                                                                                       hover:opacity-80' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Action --}}
        <button wire:click="markAllAsRead"
            class="inline-flex items-center gap-2
                   px-4 py-2 rounded-lg text-sm
                   bg-slate-800 text-white
                   hover:bg-slate-700 transition">
            {{-- Heroicon: Check --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>

            {{ __('Mark all as read') }}
        </button>
    </div>


    {{-- ================= Notifications list ================= --}}
    <div class="space-y-3">

        @forelse ($this->notifications() as $notification)
            {{-- ⚠️ IMPORTANT: logic untouched --}}
            <x-notifications.card :notification="$notification" />
        @empty

            {{-- Empty state --}}
            <div
                class="rounded-2xl border border-dashed
                       border-slate-300 dark:border-slate-700
                       bg-slate-50 dark:bg-slate-900
                       p-12 text-center space-y-2">
                <div class="text-4xl">🛰️</div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    {{ __('No notifications') }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ __('There are no notifications matching this filter.') }}
                </p>
            </div>
        @endforelse

    </div>
</div>
