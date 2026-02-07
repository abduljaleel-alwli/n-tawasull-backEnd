<?php

use Livewire\Volt\Component;
use App\Models\NewsletterEmail;
use App\Actions\Newsletter\DeleteNewsletterEmail;
use App\Actions\Newsletter\ChangeEmailStatus;

new class extends Component {
    /** Search and Filters */
    public string $search = '';
    public string $statusFilter = 'all'; // all | active | inactive

    /** Listing */
    public $emails;

    /** Modal states */
    public bool $showModal = false;
    public $viewing = null;

    /** Data */
    public function mount(): void
    {
        $this->loadEmails();
    }

    public function loadEmails(): void
    {
        $this->emails = NewsletterEmail::query()
            ->when($this->search, function ($q) {
                $q->where('email', 'like', "%{$this->search}%");
            })
            ->when($this->statusFilter === 'active', fn($q) => $q->where('is_subscribed', true))
            ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_subscribed', false))
            ->orderBy('subscribed_at', 'desc') // Order by subscription date
            ->get();
    }

    public function updatedSearch(): void
    {
        $this->loadEmails();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadEmails();
    }

    public function view(NewsletterEmail $email): void
    {
        $this->viewing = $email;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->viewing = null;
    }

    public function delete(DeleteNewsletterEmail $delete, NewsletterEmail $email): void
    {
        $delete->execute($email);
        $this->loadEmails();
        $this->toast('success', __('Email deleted successfully.'));
    }

    // Toggle subscription status (active/inactive)
    public function toggleStatus(NewsletterEmail $email)
    {
        // استدعاء Action لتغيير الحالة
        $changeStatusAction = new ChangeEmailStatus();
        $changeStatusAction->execute($email); // فقط تمرير البريد الإلكتروني

        $this->loadEmails(); // إعادة تحميل البيانات بعد التغيير
        $this->toast('success', __('Email status changed successfully.'));
    }

    private function toast(string $type, string $message): void
    {
        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: { type: '{$type}', message: '{$message}' }
                })
            );
        ");
    }
};
?>

<div class="space-y-8">

    {{-- Stats and Filters --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total --}}
        <button wire:click="$set('statusFilter','all')"
            class="text-left rounded-2xl p-4
               bg-white dark:bg-slate-900
               border border-slate-200 dark:border-slate-800
               flex items-center justify-between transition
               {{ $statusFilter === 'all' ? 'ring-2 ring-accent/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">

            <div>
                <p class="text-xs text-slate-500">{{ __('Total emails') }}</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ $emails->count() }}
                </p>
            </div>

            <flux:icon name="envelope" class="w-8 h-8 text-slate-400" />
        </button>

        {{-- Active --}}
        <button wire:click="$set('statusFilter','active')"
            class="text-left rounded-2xl p-4
               bg-emerald-500/10
               flex items-center justify-between transition
               {{ $statusFilter === 'active' ? 'ring-2 ring-emerald-500/40' : 'hover:bg-emerald-500/20' }}">

            <div>
                <p class="text-xs text-emerald-600">{{ __('Active') }}</p>
                <p class="text-2xl font-semibold text-emerald-700">
                    {{ $emails->where('is_subscribed', true)->count() }}
                </p>
            </div>

            <flux:icon name="check-circle" class="w-8 h-8 text-emerald-600" />
        </button>

        {{-- Inactive --}}
        <button wire:click="$set('statusFilter','inactive')"
            class="text-left rounded-2xl p-4
               bg-slate-100 dark:bg-slate-800
               flex items-center justify-between transition
               {{ $statusFilter === 'inactive' ? 'ring-2 ring-slate-400/40' : 'hover:bg-slate-200 dark:hover:bg-slate-700' }}">

            <div>
                <p class="text-xs text-slate-500">{{ __('Inactive') }}</p>
                <p class="text-2xl font-semibold text-slate-700 dark:text-slate-200">
                    {{ $emails->where('is_subscribed', false)->count() }}
                </p>
            </div>

            <flux:icon name="x-circle" class="w-8 h-8 text-slate-400" />
        </button>

    </div>

    {{-- Search and Actions --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90
           p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- Search --}}
        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                <flux:icon name="magnifying-glass" class="w-4 h-4" />
            </span>

            <input wire:model.live="search" type="text" placeholder="{{ __('Search emails...') }}"
                class="w-full pl-9 pr-4 py-2 rounded-xl
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900
                   text-sm
                   focus:ring-2 focus:ring-accent/40
                   focus:outline-none">
        </div>

        {{-- Total Count --}}
        <div class="flex items-center gap-3 justify-end">
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                   text-xs font-medium
                   bg-slate-100 dark:bg-slate-800
                   text-slate-600 dark:text-slate-300">
                <flux:icon name="envelope" class="w-4 h-4" />
                {{ __('Total') }}: {{ $emails->count() }}
            </span>
        </div>
    </div>

    {{-- Emails List --}}
    <div
        class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                <tr>
                    <th class="px-3 py-3">{{ __('Email') }}</th>
                    <th class="px-3 py-3">{{ __('Subscribed at') }}</th>
                    <th class="px-3 py-3">{{ __('Status') }}</th>
                    <th class="px-3 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($emails as $email)
                    <tr wire:key="email-{{ $email->id }}"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                        <td class="px-4 py-3">{{ $email->email }}</td>
                        <td class="px-4 py-3">{{ $email->subscribed_at->diffForHumans() }}</td>
<td class="px-4 py-3">
    <button wire:click="toggleStatus({{ $email->id }})"
            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium 
                   {{ $email->is_subscribed ? 'bg-emerald-500/15 text-emerald-600' : 'bg-rose-500/15 text-rose-600' }}
                   hover:bg-opacity-20 focus:outline-none">
        {{ $email->is_subscribed ? __('Subscribed') : __('Unsubscribed') }}
    </button>
</td>

                        <td class="px-4 py-3 text-right">
                            <button wire:click="view({{ $email->id }})"
                                class="p-2 rounded-lg text-accent hover:bg-accent/10">
                                <flux:icon name="eye" class="w-4 h-4" />
                            </button>
                            <button wire:click="delete({{ $email->id }})"
                                class="p-2 rounded-lg text-red-500 hover:bg-red-500/10">
                                <flux:icon name="trash" class="w-4 h-4" />
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Email Details Modal --}}
    @if ($showModal && $viewing)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div
                    class="w-full sm:max-w-2xl lg:max-w-3xl max-h-[90vh] overflow-hidden rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">
                    <div
                        class="flex justify-between items-center px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h4 class="font-semibold">{{ __('Email Details') }}</h4>
                        <button wire:click="closeModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <flux:icon name="x-circle" class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <strong>{{ __('Email') }}:</strong>
                            <p>{{ $viewing->email }}</p>
                        </div>
                        <div>
                            <strong>{{ __('Subscribed at') }}:</strong>
                            <p>{{ $viewing->subscribed_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <strong>{{ __('Status') }}:</strong>
                            <p>{{ $viewing->is_subscribed ? __('Subscribed') : __('Unsubscribed') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
