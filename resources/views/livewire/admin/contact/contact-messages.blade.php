<?php

use App\Actions\Contact\ReplyToContactMessage;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageReplied;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Component;

new class extends Component
{
    use AuthorizesRequests;

    public ?ContactMessage $selected = null;

    public ?int $selectedId = null;

    public array $selectedIds = [];

    public bool $selectAll = false;

    public string $filter = 'all'; // all | unread | read

    public string $tagFilter = 'all'; // all | sales | support | spam

    public string $search = '';

    public bool $showReplyModal = false;

    public string $replyMessage = '';

    public bool $showConfirmDelete = false;

    public bool $showConfirmDeleteSingle = false;

    public ?int $pendingDeleteId = null;

    public function mount(): void
    {
        $this->authorize('access-dashboard');
    }

    /* =====================
        Computed: Messages (NO public cache)
    ===================== */
    public function getMessagesProperty()
    {
        $query = ContactMessage::query()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($this->tagFilter !== 'all') {
            $query->where('tag', $this->tagFilter);
        }

        $q = trim($this->search);
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%");
            });
        }

        return $query->get();
    }

    private function visibleIds(): array
    {
        return $this->messages->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
    }

    /* =====================
        Computed: Stats
    ==================== */
    public function getStatsProperty(): array
    {
        return [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::whereNull('read_at')->count(),
            'read' => ContactMessage::whereNotNull('read_at')->count(),
            'sales' => ContactMessage::where('tag', 'sales')->count(),
            'support' => ContactMessage::where('tag', 'support')->count(),
            'spam' => ContactMessage::where('tag', 'spam')->count(),
        ];
    }

    /* =====================
        UI Key (forces list rebuild when filters change)
    ===================== */
    public function getListKeyProperty(): string
    {
        return md5($this->filter.'|'.$this->tagFilter.'|'.trim($this->search));
    }

    /* =====================
        Filters/Search hooks
        (official: use specific updatedX instead of updated($property))
    ===================== */
    public function updatedFilter(): void
    {
        $this->resetSelectionState();
    }

    public function updatedTagFilter(): void
    {
        $this->resetSelectionState();
    }

    public function updatedSearch(): void
    {
        $this->resetSelectionState();
    }

    /* =====================
        Viewer
    ===================== */
    public function viewMessage(int $id): void
    {
        $message = ContactMessage::findOrFail($id);

        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        $this->selected = $message->fresh();
        $this->selectedId = $message->id;
    }

    public function clearSelected(): void
    {
        $this->selected = null;
        $this->selectedId = null;
    }

    /* =====================
        Bulk selection (SYNCED)
    ===================== */
    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value ? $this->visibleIds() : [];
    }

    public function updatedSelectedIds(): void
    {
        // normalize to ints + remove duplicates
        $this->selectedIds = array_values(array_unique(array_map('intval', $this->selectedIds)));

        $visible = $this->visibleIds();

        // keep only ids that are visible now (important after filter/search)
        $this->selectedIds = array_values(array_intersect($this->selectedIds, $visible));

        // sync selectAll
        $this->selectAll = count($visible) > 0 && count($this->selectedIds) === count($visible);
    }

    /* =====================
        Bulk actions
    ===================== */
    public function markSelectedAsRead(): void
    {
        if (! count($this->selectedIds)) {
            return;
        }

        ContactMessage::whereIn('id', $this->selectedIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->resetSelectionOnly();
    }

    public function markAllAsRead(): void
    {
        ContactMessage::whereNull('read_at')->update(['read_at' => now()]);

        // لو كانت الفلترة "unread" راح تختفي الرسائل فورًا لأن messages Computed
        $this->resetSelectionOnly();
    }

    public function confirmDeleteSelected(): void
    {
        if (! count($this->selectedIds)) {
            return;
        }
        $this->showConfirmDelete = true;
    }

    public function deleteSelected(): void
    {
        ContactMessage::whereIn('id', $this->selectedIds)->delete();

        if ($this->selectedId && in_array($this->selectedId, $this->selectedIds, true)) {
            $this->clearSelected();
        }

        $this->resetSelectionOnly();
        $this->showConfirmDelete = false;
    }

    /* =====================
        Single delete
    ===================== */
    public function confirmDeleteSingle(int $id): void
    {
        $this->pendingDeleteId = $id;
        $this->showConfirmDeleteSingle = true;
    }

    public function deleteSingle(): void
    {
        if (! $this->pendingDeleteId) {
            $this->showConfirmDeleteSingle = false;

            return;
        }

        $id = (int) $this->pendingDeleteId;

        ContactMessage::where('id', $id)->delete();

        if ($this->selectedId === $id) {
            $this->clearSelected();
        }

        $this->pendingDeleteId = null;
        $this->showConfirmDeleteSingle = false;

        // remove from selection too
        $this->selectedIds = array_values(array_filter($this->selectedIds, fn ($v) => (int) $v !== $id));
        $this->updatedSelectedIds(); // re-sync selectAll
    }

    /* =====================
        Tags
    ===================== */
    public function setTag(int $id, ?string $tag): void
    {
        $allowed = ['sales', 'support', 'spam', null];
        if (! in_array($tag, $allowed, true)) {
            return;
        }

        ContactMessage::where('id', $id)->update(['tag' => $tag]);

        if ($this->selectedId === $id && $this->selected) {
            $this->selected->tag = $tag;
        }

        // selection remains correct because list is computed
        $this->updatedSelectedIds();
    }

    /* =====================
        Reply
    ===================== */
    public function openReply(): void
    {
        if (! $this->selected) {
            return;
        }

        $this->replyMessage = '';
        $this->resetValidation();
        $this->showReplyModal = true;
    }

    public function closeReply(): void
    {
        $this->showReplyModal = false;
        $this->replyMessage = '';
        $this->resetValidation();
    }

    public function sendReply(ReplyToContactMessage $action): void
    {
        if (! $this->selected) {
            return;
        }

        $data = $this->validate([
            'replyMessage' => ['required', 'string', 'min:5'],
        ]);

        // تنفيذ الرد (إرسال الإيميل)
        $action->execute($this->selected, $data['replyMessage']);

        //  تعليم الرسالة أنها تم الرد عليها
        $this->selected->update([
            'replied_at' => now(),
        ]);

        // 🔔 إشعار Laravel
        Notification::send(auth()->user(), new ContactMessageReplied($this->selected));

        // 🎉 Toast
        $this->dispatch('toast', message: __('Reply sent successfully'), type: 'success');

        $this->showReplyModal = false;
        $this->replyMessage = '';
    }

    /* =====================
        Helpers
    ===================== */
    private function resetSelectionOnly(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    private function resetSelectionState(): void
    {
        $this->resetSelectionOnly();
        $this->clearSelected();
    }
};
?>

<div class="space-y-8">
    {{-- Stats cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">

        {{-- Total --}}
        <div
            class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500">{{ __('Total') }}</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ $this->stats['total'] }}
                </p>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75M3 12.75l7.5 4.5 7.5-4.5" />
            </svg>
        </div>


        {{-- Unread --}}
        <button wire:click="$set('filter','unread')"
            class="text-left rounded-2xl p-4 bg-sky-500/10 hover:bg-sky-500/20 transition flex items-center justify-between">
            <div>
                <p class="text-xs text-sky-600">{{ __('Unread') }}</p>
                <p class="text-2xl font-semibold text-sky-700">
                    {{ $this->stats['unread'] }}
                </p>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75M21.75 6.75l-9.75 6-9.75-6" />
            </svg>
        </button>


        {{-- Read --}}
        <button wire:click="$set('filter','read')"
            class="text-left rounded-2xl p-4 bg-emerald-500/10 hover:bg-emerald-500/20 transition flex items-center justify-between">
            <div>
                <p class="text-xs text-emerald-600">{{ __('Read') }}</p>
                <p class="text-2xl font-semibold text-emerald-700">
                    {{ $this->stats['read'] }}
                </p>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 7.5l8.25 5.25L20.25 7.5M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75" />
            </svg>
        </button>


        {{-- Sales --}}
        <button wire:click="$set('tagFilter','sales')"
            class="text-left rounded-2xl p-4 bg-sky-500/10 hover:bg-sky-500/20 transition flex items-center justify-between">
            <div>
                <p class="text-xs text-sky-600">{{ __('Sales') }}</p>
                <p class="text-2xl font-semibold text-sky-700">
                    {{ $this->stats['sales'] }}
                </p>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6v12m0-12c-2.5 0-4 1.5-4 3s1.5 3 4 3 4 1.5 4 3-1.5 3-4 3" />
            </svg>
        </button>


        {{-- Support --}}
        <button wire:click="$set('tagFilter','support')"
            class="text-left rounded-2xl p-4 bg-emerald-500/10 hover:bg-emerald-500/20 transition flex items-center justify-between">
            <div>
                <p class="text-xs text-emerald-600">{{ __('Support') }}</p>
                <p class="text-2xl font-semibold text-emerald-700">
                    {{ $this->stats['support'] }}
                </p>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 6v3l2 2" />
            </svg>
        </button>


        {{-- Spam --}}
        <button wire:click="$set('tagFilter','spam')"
            class="text-left rounded-2xl p-4 bg-red-500/10 hover:bg-red-500/20 transition flex items-center justify-between">
            <div>
                <p class="text-xs text-red-600">{{ __('Spam') }}</p>
                <p class="text-2xl font-semibold text-red-700">
                    {{ $this->stats['spam'] }}
                </p>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v4m0 4h.01M10.29 3.86l-8 14A1.5 1.5 0 003.6 20h16.8a1.5 1.5 0 001.31-2.14l-8-14a1.5 1.5 0 00-2.62 0z" />
            </svg>
        </button>


    </div>

    {{-- Toolbar --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90
           p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- Left --}}
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                    {{-- Heroicon: Magnifying Glass --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35m1.6-5.4a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>

                <input wire:model.live="search" class="input w-full pl-9"
                    placeholder="{{ __('Search messages...') }}" />
            </div>


            <div class="flex flex-wrap items-center gap-2">
                @foreach (['all' => __('All'), 'sales' => __('Sales'), 'support' => __('Support'), 'spam' => __('Spam')] as $key => $label)
                    <button wire:click="$set('tagFilter','{{ $key }}')"
                        class="px-4 py-2 rounded-full text-sm transition
                {{ $tagFilter === $key
                    ? 'bg-accent text-white shadow'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:opacity-80' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <span class="mx-1 text-slate-400">|</span>

            <div class="flex items-center gap-2">
                @foreach (['all' => __('All'), 'unread' => __('Unread'), 'read' => __('Read')] as $key => $label)
                    <button wire:click="$set('filter','{{ $key }}')"
                        class="px-4 py-2 rounded-full text-sm transition
                {{ $filter === $key
                    ? 'bg-secondary text-white shadow'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:opacity-80' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Right --}}
        <div class="flex flex-wrap items-center gap-2 justify-end">
            <button wire:click="markAllAsRead"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm
           bg-slate-800 text-white hover:bg-slate-700 transition">

                {{-- Heroicon: Check --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>

                {{ __('Mark all as read') }}
            </button>


            @if (!empty($selectedIds))
                <div class="flex items-center gap-3">

                    <span class="text-xs text-slate-500">
                        {{ __('Selected') }}: {{ count($selectedIds) }}
                    </span>

                    <button wire:click="markSelectedAsRead"
                        class="px-4 py-2 rounded-lg text-sm
                   bg-slate-700 text-white hover:bg-slate-600 transition">
                        {{ __('Mark selected') }}
                    </button>

                    <button wire:click="confirmDeleteSelected"
                        class="px-4 py-2 rounded-lg text-sm
                   bg-red-600 text-white hover:bg-red-700 transition">
                        {{ __('Delete') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Inbox layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- List --}}
        <div
            class="lg:col-span-1 rounded-2xl overflow-hidden
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900/90">
            {{-- List header --}}
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <label class="flex items-center gap-2 text-xs text-slate-500">
                    <input type="checkbox" wire:model.live="selectAll" />
                    <span>{{ __('Select all') }}</span>
                </label>

                <button wire:click="clearSelected"
                    class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    {{ __('Clear') }}
                </button>
            </div>

            {{-- Items --}}
            <div wire:key="messages-list-{{ $this->listKey }}"
                class="max-h-[70vh] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($this->messages as $message)
                    @php
                        $isUnread = is_null($message->read_at);
                        $isActive = $selectedId === $message->id;
                        $isReplied = !is_null($message->replied_at);

                        $tag = $message->tag;
                        $tagBadge = match ($tag) {
                            'sales' => ['bg' => 'bg-sky-500/10', 'text' => 'text-sky-500', 'label' => __('Sales')],
                            'support' => [
                                'bg' => 'bg-emerald-500/10',
                                'text' => 'text-emerald-500',
                                'label' => __('Support'),
                            ],
                            'spam' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-500', 'label' => __('Spam')],
                            default => null,
                        };
                    @endphp

<div wire:key="message-{{ $message->id }}" class="px-4 py-3 flex gap-4 items-start transition-colors rounded-lg {{ $isActive ? 'bg-slate-50 dark:bg-slate-800/60' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
    <div class="flex-shrink-0 flex items-center justify-center">
        <input type="checkbox" wire:model.live="selectedIds" value="{{ $message->id }}" class="mt-1" />
    </div>

    <button type="button" wire:click="viewMessage({{ $message->id }})" class="w-full text-left">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate {{ $isUnread ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-200' }}">
                    {{ $message->name }}
                </p>
                <p class="text-xs text-slate-500 truncate">
                    {{ Str::limit($message->message, 60) }}
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if ($tagBadge)
                    <span class="px-2 py-1 rounded-full text-[11px] font-medium {{ $tagBadge['bg'] }} {{ $tagBadge['text'] }}">
                        {{ $tagBadge['label'] }}
                    </span>
                @endif

                @if ($isReplied)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-600 text-[11px] font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15l3.75-4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('Replied') }}
                    </span>
                @elseif ($isUnread)
                    <span class="mt-1 w-2 h-2 rounded-full bg-accent"></span>
                @endif
            </div>
        </div>

        <p class="mt-2 text-[11px] text-slate-400">
            {{ $message->created_at->diffForHumans() }}
        </p>
    </button>
</div>

                @empty
                    <div class="p-8 text-center text-sm text-slate-500">
                        {{ __('No messages found') }}
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Viewer --}}
        <div
            class="lg:col-span-2 rounded-2xl
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900/90
                   overflow-hidden">
            @if ($selected)
                {{-- Viewer header --}}
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 truncate">
                            {{ $selected->name }}
                        </h3>
                        <p class="text-sm text-slate-500 truncate">
                            {{ $selected->email }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 justify-end">
                        {{-- Tags quick actions --}}
                        <div class="flex items-center gap-2">
                            <button wire:click="setTag({{ $selected->id }}, 'sales')"
                                class="px-3 py-2 rounded-lg text-xs bg-sky-500/10 text-sky-600 dark:text-sky-400 hover:opacity-80 transition">
                                {{ __('Sales') }}
                            </button>
                            <button wire:click="setTag({{ $selected->id }}, 'support')"
                                class="px-3 py-2 rounded-lg text-xs bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 hover:opacity-80 transition">
                                {{ __('Support') }}
                            </button>
                            <button wire:click="setTag({{ $selected->id }}, 'spam')"
                                class="px-3 py-2 rounded-lg text-xs bg-red-500/10 text-red-600 dark:text-red-400 hover:opacity-80 transition">
                                {{ __('Spam') }}
                            </button>
                            <button wire:click="setTag({{ $selected->id }}, null)"
                                class="px-3 py-2 rounded-lg text-xs bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:opacity-80 transition">
                                {{ __('Clear tag') }}
                            </button>
                        </div>

                        <button wire:click="openReply"
                            class="px-4 py-2 rounded-lg text-sm bg-accent text-white hover:opacity-90 transition">
                            {{ __('Reply') }}
                        </button>

                        <button wire:click="confirmDeleteSingle({{ $selected->id }})"
                            class="px-4 py-2 rounded-lg text-sm bg-red-600 text-white hover:bg-red-700 transition">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>

                {{-- Viewer body --}}
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

                    {{-- Meta --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-slate-500">{{ __('Phone') }}</p>
                            <p class="text-slate-900 dark:text-slate-100">{{ $selected->phone ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">{{ __('IP address') }}</p>
                            <p class="text-slate-900 dark:text-slate-100">{{ $selected->ip_address ?? '—' }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs text-slate-500">{{ __('Date') }}</p>
                            <p class="text-slate-900 dark:text-slate-100">
                                {{ $selected->created_at->format('Y-m-d H:i') }}</p>
                        </div>

                        {{-- Project Type --}}
                        @if ($selected->project_type)
                            <div class="sm:col-span-2">
                                <p class="text-xs text-slate-500">{{ __('Project Type') }}</p>
                                <p class="text-slate-900 dark:text-slate-100">{{ $selected->project_type }}</p>
                            </div>
                        @endif

                        {{-- Services --}}
                        @if ($selected->services && count($selected->services) > 0)
                            <div class="sm:col-span-2">
                                <p class="text-xs text-slate-500">{{ __('Selected Services') }}</p>
                                <p class="text-slate-900 dark:text-slate-100">{{ implode(', ', $selected->services) }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Message --}}
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                        <p class="text-xs text-slate-500 mb-2">{{ __('Message') }}</p>
                        <div
                            class="rounded-xl bg-slate-50 dark:bg-slate-800
                                   p-4 text-sm leading-relaxed
                                   text-slate-700 dark:text-slate-200
                                   whitespace-pre-line">
                            {{ $selected->message }}
                        </div>
                    </div>

                    {{-- Attachment --}}
                    @if ($selected->attachment_path)
                        @php
                            $fullPath = storage_path('app/' . $selected->attachment_path);

                            $size = file_exists($fullPath) ? round(filesize($fullPath) / 1024, 2) : null;

                            $extension = strtolower(pathinfo($selected->attachment_path, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        @endphp

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                            <p class="text-xs text-slate-500 mb-2">
                                {{ __('Attachment') }}
                            </p>

                            <a href="{{ route('contact.attachments.download', $selected->id) }}" target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                   bg-slate-100 dark:bg-slate-800
                   text-slate-700 dark:text-slate-200
                   hover:bg-slate-200 dark:hover:bg-slate-700
                   transition text-sm">
                                {{-- Heroicon: Paper Clip --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l9.192-9.193a3 3 0 114.243 4.243l-9.193 9.193a1.5 1.5 0 01-2.121-2.121l7.693-7.693" />
                                </svg>

                                {{ __('Download attachment') }}

                                @if ($size)
                                    <span class="text-xs text-slate-500">
                                        ({{ $size }} KB)
                                    </span>
                                @endif
                            </a>
                            {{-- Image preview --}}
                            @if ($isImage)
                                <div class="mt-3">
                                    <p class="text-xs text-slate-500 mb-2">
                                        {{ __('Preview') }}
                                    </p>

                                    <img src="{{ route('contact.attachments.download', $selected->id) }}"
                                        alt="Attachment preview"
                                        class="max-w-full max-h-64 rounded-xl
                           border border-slate-200 dark:border-slate-700
                           shadow-sm" />
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            @else
                <div class="p-10 text-center text-sm text-slate-500">
                    {{ __('Select a message to view its content') }}
                </div>
            @endif
        </div>

    </div>

    {{-- Reply Modal --}}
    @if ($showReplyModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeReply"></div>

            <div class="relative h-full w-full flex items-start justify-center px-4 py-6 sm:py-10">
                <div
                    class="w-full max-w-2xl rounded-2xl
                           bg-white dark:bg-slate-900
                           border border-slate-200 dark:border-slate-800
                           shadow-2xl
                           max-h-[90vh]
                           flex flex-col overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-semibold">
                            {{ __('Reply to') }}: {{ $selected?->name }}
                        </h3>
                        <button type="button" wire:click="closeReply" wire:loading.attr="disabled"
                            wire:target="closeReply"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl
                               bg-slate-200 hover:bg-slate-300
                               dark:bg-slate-800 dark:hover:bg-slate-700
                               text-slate-900 dark:text-slate-100
                               inline-flex items-center justify-center gap-2
                               disabled:opacity-60 disabled:cursor-not-allowed" aria-label="{{ __('Close') }}">
                            <svg wire:loading wire:target="closeReply" class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span wire:loading.remove wire:target="closeReply">
                                ✕
                            </span>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 overflow-y-auto">
                        @if ($errors->any())
                            <div
                                class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/30 p-4 text-sm text-red-700 dark:text-red-400">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <p class="text-xs text-slate-500 mb-1">{{ __('To') }}</p>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ $selected?->email }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-500 mb-2">
                                {{ __('Message') }}
                            </label>

                            <textarea wire:model.defer="replyMessage" rows="7"
                                class="textarea w-full @error('replyMessage') ring-1 ring-red-500 @enderror"
                                placeholder="{{ __('Write your reply...') }}"></textarea>

                            @error('replyMessage')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div
                        class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button type="button" wire:click="closeReply" wire:loading.attr="disabled"
                            wire:target="closeReply"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl
                               bg-slate-200 hover:bg-slate-300
                               dark:bg-slate-800 dark:hover:bg-slate-700
                               text-slate-900 dark:text-slate-100
                               inline-flex items-center justify-center gap-2
                               disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="closeReply" class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span wire:loading.remove wire:target="closeReply">
                                {{ __('Cancel') }}
                            </span>
                            <span wire:loading wire:target="closeReply">
                                {{ __('Closing...') }}
                            </span>
                        </button>

                        <button type="button" wire:click="sendReply" wire:loading.attr="disabled"
                            wire:target="sendReply"
                            class="px-4 py-2 rounded-lg text-sm bg-accent text-white hover:opacity-90 transition">
                            <svg wire:loading wire:target="sendReply" class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span wire:loading.remove wire:target="sendReply">
                                {{ __('Send') }}
                            </span>
                            <span wire:loading wire:target="sendReply">
                                {{ __('Sending...') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirm Delete Selected --}}
    <x-modals.confirm :show="$showConfirmDelete" type="danger" :title="__('Delete selected messages')" :message="__('Are you sure you want to delete selected messages? This action cannot be undone.')"
        confirm-action='wire:click="deleteSelected"' cancel-action='wire:click="$set(\"showConfirmDelete\", false)"'
        confirm-text="{{ __('Delete') }}" cancel-text="{{ __('Cancel') }}" />

    {{-- Confirm Delete Single --}}
    <x-modals.confirm :show="$showConfirmDeleteSingle" type="danger" :title="__('Delete message')" :message="__('Are you sure you want to delete this message? This action cannot be undone.')"
        confirm-action='wire:click="deleteSingle"'
        cancel-action='wire:click="$set(\"showConfirmDeleteSingle\", false)"' confirm-text="{{ __('Delete') }}"
        cancel-text="{{ __('Cancel') }}" />

</div>
