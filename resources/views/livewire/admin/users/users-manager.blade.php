<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Support\Auditable;
use App\Models\User;
use App\Actions\Users\ToggleUserStatus;
use Livewire\WithPagination;
use App\Actions\Users\CreateUser;
use App\Actions\Users\UpdateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\ChangeUserRole;
use App\Notifications\UserActionNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

new class extends Component {
    use Auditable;
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public bool $showConfirmDelete = false;
    public ?int $deleteUserId = null;

    /* =====================
       Lifecycle
    ===================== */

    public function mount(): void
    {
        // Authorization via Policy (super-admin bypass handled globally)
        $this->authorize('viewAny', User::class);
    }

    /* =====================
       State
    ===================== */

    public string $search = '';
    public bool $showCreateModal = false;

    public string $name = '';
    public string $email = '';
    public string $role = 'admin';
    public ?string $note = null;

    public bool $showEditModal = false;
    public ?User $editingUser = null;

    public string $editName = '';
    public string $editEmail = '';
    public ?string $editNote = null;
    public string $editRole = 'admin';

    /* =====================
       Modals Actions
    ===================== */
    public function confirmDeleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('delete', $user);

        $this->deleteUserId = $userId;
        $this->showConfirmDelete = true;
    }

    public function cancelDeleteUser(): void
    {
        $this->deleteUserId = null;
        $this->showConfirmDelete = false;
    }

    /* =====================
       Computed
    ===================== */

    public function users()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function editUser(User $user): void
    {
        $this->authorize('update', $user);

        $this->editingUser = $user;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editNote = $user->note;
        $this->editRole = $user->roles->first()?->name ?? 'admin';

        $this->showEditModal = true;
    }

    public function stats(): array
    {
        return [
            'total' => User::count(),
            'admins' => User::role('admin')->count(),
            'superAdmins' => User::role('super-admin')->count(),
        ];
    }

    /* =====================
       Actions
    ===================== */

    public function createUser(): void
    {
        $this->authorize('create', User::class);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,super-admin'],
        ]);

        $user = app(CreateUser::class)->execute([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'note' => $this->note,
        ]);

        // 🔐 Send reset password link
        Password::sendResetLink([
            'email' => $user->email,
        ]);

        // 🔹 Audit Log
        $this->audit('user.created', $user, [
            'role' => $this->role,
        ]);

        // 🔹 Push Notification
        auth()
            ->user()
            ->notify(
                new UserActionNotification('user.created', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]),
            );

        $this->resetForm();
        $this->resetPage();

        $this->js(
            "
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" .
                __('User created and password reset link sent successfully') .
                "'
                    }
                })
            );
        ",
        );
    }

    public function updateUser(): void
    {
        $this->authorize('update', $this->editingUser);

        $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editEmail' => ['required', 'email', 'unique:users,email,' . $this->editingUser->id],
            'editRole' => ['required', 'in:admin,super-admin'],
        ]);

        app(UpdateUser::class)->execute($this->editingUser, [
            'name' => $this->editName,
            'email' => $this->editEmail,
            'note' => $this->editNote,
        ]);

        if ($this->editingUser->roles->first()?->name !== $this->editRole) {
            app(ChangeUserRole::class)->execute($this->editingUser, $this->editRole);
        }

        // 🔹 Audit Log
        $this->audit('user.updated', $this->editingUser, [
            'fields' => ['name', 'email', 'note', 'role'],
        ]);

        // 🔹 Push Notification
        auth()
            ->user()
            ->notify(
                new UserActionNotification('user.updated', [
                    'user_id' => $this->editingUser->id,
                    'email' => $this->editingUser->email,
                ]),
            );

        $this->resetEditForm();
        $this->resetPage();

        $this->js(
            "
        window.dispatchEvent(
            new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: '" .
                __('User updated successfully') .
                "'
                }
            })
        );
    ",
        );
    }

    public function deleteUserConfirmed(): void
    {
        if (!$this->deleteUserId) {
            return;
        }

        $user = User::findOrFail($this->deleteUserId);

        $this->authorize('delete', $user);

        app(DeleteUser::class)->execute($user);

        // 🔹 Audit Log
        $this->audit('user.deleted', $user, [
            'email' => $user->email,
        ]);

        // 🔔 Notification
        auth()
            ->user()
            ->notify(
                new UserActionNotification('user.deleted', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]),
            );

        $this->cancelDeleteUser();
        $this->resetPage();

        $this->js(
            "
        window.dispatchEvent(
            new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: '" .
                __('User deleted successfully') .
                "'
                }
            })
        );
    ",
        );
    }

    public function toggleUserStatus(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('toggleActive', $user);

        app(ToggleUserStatus::class)->execute($user);

        $this->audit('user.toggled', $user, [
            'is_active' => $user->is_active,
        ]);

        // 🔹 Push Notification
        auth()
            ->user()
            ->notify(
                new UserActionNotification('user.status_changed', [
                    'user_id' => $user->id,
                    'is_active' => $user->is_active,
                ]),
            );

        $this->js(
            "
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" .
                ($user->is_active ? __('The user has been activated successfully') : __('The user has been successfully disabled')) .
                "'
                    }
                })
            );
        ",
        );
    }

    public function canActOn(User $user): bool
    {
        return auth()->user()->id !== $user->id;
    }

    public function cannotActReason(User $user): ?string
    {
        if (auth()->user()->id === $user->id) {
            return __('You cannot perform this action on your own account');
        }

        return null;
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'role', 'note', 'showCreateModal']);
    }

    public function resetEditForm(): void
    {
        $this->reset(['showEditModal', 'editingUser', 'editName', 'editEmail', 'editNote', 'editRole']);
    }
};
?>


<!-- =====================
     UI
===================== -->
<div class="space-y-8">

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach ([
        [
            'label' => __('Total users'),
            'value' => $this->stats()['total'],
            'icon' => 'users',
            'color' => 'text-slate-400 dark:text-slate-300',
            'bg' => 'bg-slate-200/10 dark:bg-slate-800/60',
        ],
        [
            'label' => __('Admins'),
            'value' => $this->stats()['admins'],
            'icon' => 'shield-check',
            'color' => 'text-sky-600 dark:text-sky-400',
            'bg' => 'bg-sky-500/10 dark:bg-sky-500/20',
        ],
        [
            'label' => __('Super admins'),
            'value' => $this->stats()['superAdmins'],
            'icon' => 'star',
            'color' => 'text-amber-500 dark:text-amber-400',
            'bg' => 'bg-amber-500/10 dark:bg-amber-500/20',
        ],
    ] as $stat)
            <div
                class="rounded-2xl border border-slate-200 dark:border-slate-800
                   p-5
                   flex items-center justify-between {{ $stat['bg'] }}">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ $stat['value'] }}
                    </p>
                </div>

                {{-- Heroicon --}}
                @switch($stat['icon'])
                    @case('users')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 {{ $stat['color'] }}" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372
                                                       9.337 9.337 0 004.121-.952
                                                       4.125 4.125 0 00-7.533-2.493M15
                                                       19.128v-.003c0-1.113-.285-2.16-.786-3.07M15
                                                       19.128v.106A12.318 12.318 0 018.624
                                                       21c-2.331 0-4.512-.645-6.374-1.766
                                                       1.072-3.004 3.86-5.125 7.124-5.125
                                                       1.083 0 2.107.233 3.024.655M9
                                                       7.5a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0z" />
                        </svg>
                    @break

                    @case('shield-check')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 {{ $stat['color'] }}" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25L15 9.75
                                                       M12 3l7.5 4.5v5.25
                                                       c0 4.142-2.686 7.875-7.5 9
                                                       -4.814-1.125-7.5-4.858-7.5-9V7.5L12 3z" />
                        </svg>
                    @break

                    @case('star')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 {{ $stat['color'] }}" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0
                                                       l2.125 5.111a.563.563 0 00.475.345
                                                       l5.518.442c.499.04.701.663.321.988
                                                       l-4.204 3.602a.563.563 0 00-.182.557
                                                       l1.285 5.385a.562.562 0 01-.84.61
                                                       l-4.725-2.885a.563.563 0 00-.586 0
                                                       L6.982 20.54a.562.562 0 01-.84-.61
                                                       l1.285-5.386a.562.562 0 00-.182-.557
                                                       l-4.204-3.602a.563.563 0 01.321-.988
                                                       l5.518-.442a.563.563 0 00.475-.345
                                                       L11.48 3.5z" />
                        </svg>
                    @break
                @endswitch
            </div>
        @endforeach
    </div>


    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">

        {{-- Search --}}
        <div class="flex gap-2">
            <input type="text" wire:model.live="search" placeholder="{{ __('Search user...') }}"
                class="input w-64" />
        </div>

        {{-- Create --}}
        @can('create', App\Models\User::class)
            <button wire:click="$set('showCreateModal', true)"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                       bg-accent text-white text-sm font-medium
                       hover:opacity-90 transition">
                + {{ __('Create user') }}
            </button>
        @endcan
    </div>

    {{-- Users table --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90 shadow-sm overflow-hidden">

        <table class="w-full text-sm">
            <thead
                class="bg-slate-50 dark:bg-slate-800
                   text-slate-600 dark:text-slate-300
                   border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-5 py-4 text-left font-medium">{{ __('Name') }}</th>
                    <th class="px-5 py-4 text-left font-medium">{{ __('Email') }}</th>
                    <th class="px-5 py-4 text-left font-medium">{{ __('Role') }}</th>
                    <th class="px-5 py-4 text-left font-medium">{{ __('Status') }}</th>
                    <th class="px-5 py-4 text-left font-medium">{{ __('Note') }}</th>
                    <th class="px-5 py-4 text-right font-medium">{{ __('Actions') }}</th>
                    <th class="px-5 py-4 text-left font-medium">{{ __('Created') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                @foreach ($this->users() as $user)
                    <tr
                        class="group
                           hover:bg-slate-50 dark:hover:bg-slate-800/40
                           transition-colors">

                        {{-- Name --}}
                        <td class="px-5 py-4 font-medium text-slate-900 dark:text-white">
                            {{ $user->name }}
                        </td>

                        {{-- Email --}}
                        <td class="px-5 py-4 text-slate-500">
                            {{ $user->email }}
                        </td>

                        {{-- Role --}}
                        <td class="px-5 py-4">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs
                                   bg-secondary/10 text-secondary">
                                {{ $user->roles->first()?->name }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @if ($user->is_active)
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs
                                       bg-emerald-500/10 text-emerald-600">
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs
                                       bg-red-500/10 text-red-600">
                                    {{ __('Disabled') }}
                                </span>
                            @endif
                        </td>

                        {{-- Note --}}
                        <td class="px-5 py-4">
                            @if ($user->note)
                                <span
                                    class="inline-block max-w-[220px] truncate
                                       px-2 py-1 rounded-md text-xs
                                       bg-slate-100 text-slate-600
                                       dark:bg-slate-800 dark:text-slate-300">
                                    {{ $user->note }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">
                                    {{ __('N/A') }}
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">

                                <button wire:click="toggleUserStatus({{ $user->id }})" @disabled(!$this->canActOn($user))
                                    class="px-3 py-1 rounded-full text-xs
                                    {{ $user->is_active
                                        ? 'bg-red-500/10 text-red-600 hover:bg-red-500/20'
                                        : 'bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20' }}
                                    disabled:opacity-40"
                                    title="{{ $this->cannotActReason($user) }}">
                                    {{ $user->is_active ? __('Disable') : __('Enable') }}
                                </button>

                                <button wire:click="editUser({{ $user->id }})" @disabled(!$this->canActOn($user))
                                    class="px-3 py-1 rounded-full text-xs
                                       bg-slate-500/10 text-slate-700
                                       hover:bg-slate-500/20
                                       disabled:opacity-40"
                                    title="{{ $this->cannotActReason($user) }}">
                                    {{ __('Edit') }}
                                </button>

                                <button wire:click="confirmDeleteUser({{ $user->id }})"
                                    @disabled(!$this->canActOn($user))
                                    class="px-3 py-1 rounded-full text-xs
                                       bg-red-500/10 text-red-600
                                       hover:bg-red-500/20
                                       disabled:opacity-40"
                                    title="{{ $this->cannotActReason($user) }}">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </td>

                        {{-- Created --}}
                        <td class="px-5 py-4 text-slate-500 text-xs">
                            {{ $user->created_at->format('Y-m-d') }}
                        </td>

                    </tr>
                @endforeach

            </tbody>
        </table>

        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $this->users()->links() }}
        </div>
    </div>


    {{-- Modals --}}
    @includeWhen($showCreateModal, 'livewire.admin.users.create-modal')
    @includeWhen($showEditModal, 'livewire.admin.users.edit-modal')

    <x-modals.confirm :show="$showConfirmDelete" type="danger" :title="__('Delete user')" :message="__('Are you sure you want to delete this user? This action cannot be undone.')" :confirmText="__('Delete')"
        :cancelText="__('Cancel')" :confirmAction="'wire:click=deleteUserConfirmed'" :cancelAction="'wire:click=cancelDeleteUser'" confirmLoadingTarget="deleteUserConfirmed" />
</div>
