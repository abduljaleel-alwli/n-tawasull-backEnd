<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Category;
use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\UpdateCategory;
use App\Actions\Categories\DeleteCategory;

new class extends Component {
    use AuthorizesRequests;

    public string $search = '';
    public string $statusFilter = 'all'; // all | with-products | empty

    public $categories;

    public bool $showModal = false;
    public ?Category $editing = null;

    public string $name = '';

    public bool $showConfirmDelete = false;
    public ?int $deleteId = null;

    public bool $showCannotDeleteModal = false;
    public int $linkedProductsCount = 0;

    public function mount(): void
    {
        $this->authorize('access-dashboard');
        $this->loadCategories();
    }

    public function updatedSearch(): void
    {
        $this->loadCategories();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadCategories();
    }


    public function loadCategories(): void
    {
        $this->categories = Category::query()
            ->withCount('products')
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->when(
                $this->statusFilter === 'with-products',
                fn($q) =>
                $q->having('products_count', '>', 0)
            )
            ->when(
                $this->statusFilter === 'empty',
                fn($q) =>
                $q->having('products_count', '=', 0)
            )
            ->orderBy('name')
            ->get();
    }


    public function getTotalCountProperty(): int
    {
        return Category::count();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->editing = $category;
        $this->name = $category->name;
        $this->showModal = true;
    }

    public function save(CreateCategory $create, UpdateCategory $update): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($this->editing) {
            $update->execute($this->editing, $data);
            $this->toast('success', __('Category updated successfully'));
        } else {
            $create->execute($data);
            $this->toast('success', __('Category created successfully'));
        }

        $this->closeModal();
        $this->loadCategories();
    }

    public function askDelete(int $id): void
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            $this->linkedProductsCount = $category->products_count;
            $this->showCannotDeleteModal = true;
            return;
        }

        $this->deleteId = $id;
        $this->showConfirmDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->showConfirmDelete = false;
    }

    public function confirmDelete(DeleteCategory $delete): void
    {
        if (!$this->deleteId) {
            return;
        }

        $delete->execute(Category::findOrFail($this->deleteId));

        $this->toast('success', __('Category deleted successfully'));

        $this->cancelDelete();
        $this->loadCategories();
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function closeCannotDeleteModal(): void
    {
        $this->showCannotDeleteModal = false;
        $this->linkedProductsCount = 0;
    }

    private function resetForm(): void
    {
        $this->reset(['editing', 'name']);
    }

    private function toast(string $type, string $message): void
    {
        $this->js("
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: '{$type}', message: '{$message}' }
            }));
        ");
    }
};
?>


<div class="space-y-6">

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total --}}
        <button wire:click="$set('statusFilter','all')" class="text-left rounded-2xl p-4
           border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900
           flex items-center justify-between
           transition
           {{ $statusFilter === 'all'
    ? 'ring-2 ring-accent/40'
    : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">

            <div>
                <p class="text-xs text-slate-500">{{ __('Total categories') }}</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ $this->totalCount }}
                </p>
            </div>

            <flux:icon name="squares-2x2" class="w-8 h-8 text-slate-400" />
        </button>


        {{-- With products --}}
        <button wire:click="$set('statusFilter','with-products')" class="text-left rounded-2xl p-4
           bg-sky-500/10
           flex items-center justify-between
           transition
           {{ $statusFilter === 'with-products'
    ? 'ring-2 ring-sky-500/40'
    : 'hover:bg-sky-500/20' }}">

            <div>
                <p class="text-xs text-sky-600">{{ __('With products') }}</p>
                <p class="text-2xl font-semibold text-sky-700">
                    {{ $categories->where('products_count', '>', 0)->count() }}
                </p>
            </div>

            <flux:icon name="cube" class="w-8 h-8 text-sky-600" />
        </button>


        {{-- Empty --}}
        <button wire:click="$set('statusFilter','empty')" class="text-left rounded-2xl p-4
           bg-slate-100 dark:bg-slate-800
           flex items-center justify-between
           transition
           {{ $statusFilter === 'empty'
    ? 'ring-2 ring-slate-400/40'
    : 'hover:bg-slate-200 dark:hover:bg-slate-700' }}">

            <div>
                <p class="text-xs text-slate-500">{{ __('Empty') }}</p>
                <p class="text-2xl font-semibold text-slate-700 dark:text-slate-200">
                    {{ $categories->where('products_count', 0)->count() }}
                </p>
            </div>

            <flux:icon name="archive-box" class="w-8 h-8 text-slate-400" />
        </button>


    </div>


    {{-- Header + Actions --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90
           p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- Search --}}
        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                <flux:icon name="magnifying-glass" class="w-4 h-4" />
            </span>

            <input wire:model.live="search" type="text" placeholder="{{ __('Search categories...') }}" class="w-full pl-9 pr-4 py-2 rounded-xl
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900
                   text-sm
                   focus:ring-2 focus:ring-accent/40
                   focus:outline-none">
        </div>

        {{-- Actions --}}
        <button wire:click="create" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
               bg-accent text-white text-sm font-medium
               hover:opacity-90 transition">
            <flux:icon name="plus" class="w-4 h-4" />
            {{ __('Add category') }}
        </button>

    </div>


    {{-- Counter --}}
    <div class="text-sm text-slate-500 dark:text-slate-400">
        {{ __('Total categories') }}
        <span class="ml-1 inline-flex items-center rounded-md bg-slate-200/50 dark:bg-slate-800/60
               px-2 py-0.5 text-xs font-semibold text-slate-900 dark:text-white">
            {{ $this->totalCount }}
        </span>
    </div>


    {{-- Table --}}
    <div class="lg:col-span-1 rounded-2xl overflow-hidden
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900/90">
        <table class="w-full text-sm">
            <thead class="bg-slate-100/70 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200">

                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Products') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($categories as $category)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                    {{ $category->name }}
                                </td>

                                {{-- Badge --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium
                                {{ $category->products_count > 0
                    ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400'
                    : 'bg-slate-200/40 text-slate-500 dark:bg-slate-700/40 dark:text-slate-400' }}">
                                        <flux:icon name="cube" class="w-3.5 h-3.5" />


                                        {{ $category->products_count }}
                                    </span>
                                </td>


                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">

                                        {{-- Edit --}}
                                        <button wire:click="edit({{ $category->id }})" class="p-1.5 rounded-lg text-sky-600 dark:text-sky-400
                       hover:bg-sky-500/10 transition" title="{{ __('Edit') }}">
                                            <flux:icon name="pencil-square" class="w-4 h-4" />
                                        </button>


                                        {{-- Delete --}}
                                        <button wire:click="askDelete({{ $category->id }})" class="p-1.5 rounded-lg text-red-500 dark:text-red-400
                       hover:bg-red-500/10 transition" title="{{ __('Delete') }}">
                                            <flux:icon name="trash" class="w-4 h-4" />
                                        </button>


                                    </div>
                                </td>

                            </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            {{ __('No categories found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modals --}}
    @if ($showModal)
        @include('livewire.admin.categories.form-modal')
    @endif

    <x-modals.confirm :show="$showConfirmDelete" type="danger" :title="__('Delete category')" :message="__('Are you sure you want to delete this category? This action cannot be undone.')" :confirmAction="'wire:click=confirmDelete'"
        :cancelAction="'wire:click=cancelDelete'" confirmLoadingTarget="confirmDelete" :confirmText="__('Delete')" />

    <x-modals.confirm :show="$showCannotDeleteModal" type="warning" :title="__('Cannot delete category')" :message="__(
        'This category is linked to :count product(s). You must remove the category from those products before deleting it.',
        ['count' => $linkedProductsCount],
    )" :confirmText="__('OK')"
        :confirmAction="'wire:click=closeCannotDeleteModal'" :cancelAction="'wire:click=closeCannotDeleteModal'" />

</div>