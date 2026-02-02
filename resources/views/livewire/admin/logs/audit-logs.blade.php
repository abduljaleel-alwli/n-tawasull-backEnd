<?php

use Livewire\Volt\Component;
use App\Models\AuditLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\WithPagination;

new class extends Component {
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';
    public string $cardFilter = 'all';

    public function mount(): void
    {
        $this->authorize('viewAny', \App\Models\User::class);
    }

    /* =====================
        Computed
    ===================== */

    public function logs()
    {
        $query = AuditLog::with('actor')->latest();

        if ($this->cardFilter === 'today') {
            $query->whereDate('created_at', today());
        }

        if ($this->cardFilter === 'system') {
            $query->whereNull('actor_id');
        }

        if ($this->cardFilter === 'users') {
            $query->whereNotNull('actor_id');
        }

        return $query->paginate(10);
    }

    public function stats(): array
    {
        return [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'system' => AuditLog::whereNull('actor_id')->count(),
            'users' => AuditLog::whereNotNull('actor_id')->count(),
        ];
    }

    public function setCardFilter(string $filter): void
    {
        $this->cardFilter = $filter;
        $this->resetPage(); // pagination reset
    }
};
?>

@php
    $active = 'ring-2 ring-accent ring-offset-2 ring-offset-white dark:ring-offset-slate-900';
@endphp

<div class="space-y-8">
    {{-- Stats cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total --}}
        <button wire:click="setCardFilter('all')" wire:loading.class="opacity-70"
            class="text-left rounded-2xl p-4 transition
           bg-white dark:bg-slate-900
           border border-slate-200 dark:border-slate-800
           hover:bg-slate-50 dark:hover:bg-slate-800/40
           {{ $cardFilter === 'all' ? $active : '' }}">

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">{{ __('Total logs') }}</p>
                    <p class="text-2xl font-semibold">
                        {{ $this->stats()['total'] }}
                    </p>
                </div>

                <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6M7.5 3h9A2.25 2.25 0 0 1 18.75 5.25v13.5A2.25 2.25 0 0 1 16.5 21h-9A2.25 2.25 0 0 1 5.25 18.75V5.25A2.25 2.25 0 0 1 7.5 3Z" />
                </svg>
            </div>
        </button>


        {{-- Today --}}
        <button wire:click="setCardFilter('today')"
            class="text-left rounded-2xl p-4 transition
           bg-sky-500/10 hover:bg-sky-500/20
           {{ $cardFilter === 'today' ? 'ring-2 ring-sky-500' : '' }}">

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-sky-600">{{ __('Today') }}</p>
                    <p class="text-2xl font-semibold text-sky-700">
                        {{ $this->stats()['today'] }}
                    </p>
                </div>

                <svg class="w-8 h-8 text-sky-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18" />
                </svg>
            </div>
        </button>


        {{-- User actions --}}
        <button wire:click="setCardFilter('users')"
            class="text-left rounded-2xl p-4 transition
           bg-emerald-500/10 hover:bg-emerald-500/20
           {{ $cardFilter === 'users' ? 'ring-2 ring-emerald-500' : '' }}">

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-emerald-600">{{ __('User actions') }}</p>
                    <p class="text-2xl font-semibold text-emerald-700">
                        {{ $this->stats()['users'] }}
                    </p>
                </div>

                <svg class="w-8 h-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0M4.5 20.25a7.5 7.5 0 0 1 15 0" />
                </svg>
            </div>
        </button>


        {{-- System --}}
        {{-- System --}}
        <button wire:click="setCardFilter('system')"
            class="text-left rounded-2xl p-4 transition
           bg-red-500/10 hover:bg-red-500/20
           {{ $cardFilter === 'system' ? 'ring-2 ring-red-500' : '' }}">

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-600">{{ __('System actions') }}</p>
                    <p class="text-2xl font-semibold text-red-700">
                        {{ $this->stats()['system'] }}
                    </p>
                </div>

                {{-- Heroicon: Cpu Chip (outline) --}}
                <svg class="w-8 h-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2.25M15 3v2.25
                   M9 18.75V21M15 18.75V21
                   M3 9h2.25M3 15h2.25
                   M18.75 9H21M18.75 15H21
                   M6.75 6.75h10.5v10.5H6.75V6.75Z" />
                </svg>
            </div>
        </button>
    </div>


    {{-- Logs table --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
               bg-white dark:bg-slate-900/90 overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Actor') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Action') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Target') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Details') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Date') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($this->logs() as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">

                        {{-- Actor --}}
                        <td class="px-4 py-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0a3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                            </svg>
                            <span class="font-medium">
                                {{ $log->actor->name ?? __('System') }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex px-2 py-1 rounded-md text-xs font-mono
                                       bg-slate-100 dark:bg-slate-800
                                       text-slate-700 dark:text-slate-200">
                                {{ $log->action }}
                            </span>
                        </td>

                        {{-- Target --}}
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                            {{ class_basename($log->target_type) }}
                            <span class="text-xs text-slate-400">
                                #{{ $log->target_id }}
                            </span>
                        </td>

                        {{-- Metadata --}}
                        <td class="px-4 py-3">
                            @if (!empty($log->metadata))
                                <details class="group">
                                    <summary class="cursor-pointer text-xs text-secondary hover:underline">
                                        {{ __('View details') }}
                                    </summary>
                                    <pre
                                        class="mt-2 max-w-xl overflow-auto rounded-lg
                                               bg-slate-50 dark:bg-slate-800
                                               p-3 text-xs text-slate-700 dark:text-slate-200">
{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                    </pre>
                                </details>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="px-4 py-3 flex items-center gap-2 text-xs text-slate-500">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18" />
                            </svg>
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                            {{ __('No audit logs found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $this->logs()->links() }}
    </div>

</div>
