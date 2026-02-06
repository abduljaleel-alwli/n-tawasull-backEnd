<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Product;
use App\Models\Category;
use App\Actions\Products\CreateProduct;
use App\Actions\Products\UpdateProduct;
use App\Actions\Products\DeleteProduct;
use App\Actions\Products\ToggleProductStatus;
use App\Actions\Products\ReorderProducts;

new class extends Component {
    use WithFileUploads;
    use AuthorizesRequests;

    /** Search */
    public string $search = '';
    public string $statusFilter = 'all'; // all | active | inactive
    public ?int $categoryFilter = null;

    /** Listing */
    public $products;

    /** Form state */
    public bool $showModal = false;
    public ?Product $editing = null;

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public bool $showViewModal = false;
    public ?Product $viewing = null;

    public string $title = '';
    public string $description = '';
    public ?int $category_id = null;
    public $main_image = null;
    public array $images = [];
    public bool $is_active = true;
    public array $tags = []; // تعريفها كمصفوفة فارغة

    /** Data */
    public $categories = [];

    public function mount(): void
    {
        // Only admin & super-admin (super-admin bypass via Gate::before)
        $this->authorize('access-dashboard');

        $this->categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        // جلب الـ tags المضافة مسبقًا من جميع المنتجات
        $this->tags = Product::pluck('tags')
            ->flatten() // Flatten لجعلها مصفوفة مسطحة
            ->unique() // إزالة التكرار
            ->toArray(); // تحويلها إلى مصفوفة عادية
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $this->products = Product::query()
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
            })
            ->when($this->statusFilter === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->orderBy('display_order')
            ->get();
    }

    public function updatedSearch(): void
    {
        $this->loadProducts();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadProducts();
    }

    public function updatedCategoryFilter(): void
    {
        $this->loadProducts();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;

        // استخدام dispatch لإطلاق الحدث عند فتح الـ modal
        $this->dispatch('modalOpened');
    }

    public function edit(Product $product): void
    {
        $this->editing = $product;

        $this->title = $product->title;
        $this->description = (string) $product->description;
        $this->category_id = $product->category_id;
        $this->is_active = (bool) $product->is_active;
        $this->display_order = (int) $product->display_order;

        // تهيئة الـ tags بشكل صحيح
        $this->tags = $product->tags ? json_decode($product->tags, true) : [];

        $this->showModal = true;

        // استخدام dispatch لإطلاق الحدث عند فتح الـ modal
        $this->dispatch('modalOpened');
    }

    public function save(CreateProduct $create, UpdateProduct $update): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'main_image' => ['nullable', 'image', 'max:10240'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:10240'],
            'is_active' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'string', 'max:50'],
        ]);

        dd($this->tags);

        // التأكد من أن الـ tags ليست فارغة
        if (!empty($this->tags)) {
            $data['tags'] = $this->tags; // استخدام الـ tags كما هي
        }

        if ($this->editing) {
            $update->execute($this->editing, $data);

            $this->toast('success', __('Product updated successfully'));
        } else {
            $create->execute($data);

            $this->toast('success', __('Product created successfully'));
        }

        $this->closeModal();
        $this->loadProducts();
    }

    public function toggle(ToggleProductStatus $toggle, Product $product): void
    {
        $toggle->execute($product);

        $this->toast('success', $product->is_active ? __('Product activated successfully') : __('Product deactivated successfully'));

        $this->loadProducts();
    }

    // Delete with confirmation modal

    public function askDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->showDeleteModal = false;
    }

    public function confirmDelete(DeleteProduct $delete): void
    {
        if (!$this->deleteId) {
            return;
        }

        $product = Product::findOrFail($this->deleteId);
        $delete->execute($product);

        $this->toast('success', __('Product deleted successfully'));

        $this->cancelDelete();
        $this->loadProducts();
    }

    public function delete(DeleteProduct $delete, Product $product): void
    {
        $delete->execute($product);

        $this->toast('success', __('Product deleted successfully'));
        $this->loadProducts();
    }

    public function reorder(array $ids): void
    {
        app(ReorderProducts::class)->execute($ids);

        $this->toast('success', __('Products reordered successfully'));
        $this->loadProducts();
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = false;
    }

    public function view(Product $product): void
    {
        $this->viewing = $product;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->viewing = null;
        $this->showViewModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['editing', 'title', 'description', 'category_id', 'main_image', 'images', 'is_active']);

        $this->is_active = true;
        $this->display_order = 0;
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

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total --}}
        <button wire:click="$set('statusFilter','all')"
            class="text-left rounded-2xl p-4
               bg-white dark:bg-slate-900
               border border-slate-200 dark:border-slate-800
               flex items-center justify-between transition
               {{ $statusFilter === 'all' ? 'ring-2 ring-accent/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">

            <div>
                <p class="text-xs text-slate-500">{{ __('Total products') }}</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ $products->count() }}
                </p>
            </div>

            <flux:icon name="shopping-bag" class="w-8 h-8 text-slate-400" />
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
                    {{ $products->where('is_active', true)->count() }}
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
                    {{ $products->where('is_active', false)->count() }}
                </p>
            </div>

            <flux:icon name="x-circle" class="w-8 h-8 text-slate-400" />
        </button>

    </div>

    {{-- Header + Actions --}}
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90
           p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- Search --}}
        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                <flux:icon name="magnifying-glass" class="w-4 h-4" />
            </span>

            <input wire:model.live="search" type="text" placeholder="{{ __('Search products...') }}"
                class="w-full pl-9 pr-4 py-2 rounded-xl
                   border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900
                   text-sm
                   focus:ring-2 focus:ring-accent/40
                   focus:outline-none">
        </div>

        {{-- Right --}}
        <div class="flex items-center gap-3 justify-end">

            {{-- Counter --}}
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                   text-xs font-medium
                   bg-slate-100 dark:bg-slate-800
                   text-slate-600 dark:text-slate-300">
                <flux:icon name="shopping-bag" class="w-4 h-4" />
                {{ __('Total') }}: {{ count($products) }}
            </span>

            {{-- Add --}}
            <button wire:click="create"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                   bg-accent text-white text-sm font-medium
                   hover:opacity-90 transition">
                <flux:icon name="plus" class="w-4 h-4" />
                {{ __('Add product') }}
            </button>
        </div>

    </div>

    {{-- Category filter --}}
    <div class="flex flex-col gap-3">

        {{-- Mobile dropdown --}}
        <div class="sm:hidden">
            <select wire:model.live="categoryFilter"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900 text-sm">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Desktop chips --}}
        <div class="hidden sm:flex flex-wrap gap-2">
            <button wire:click="$set('categoryFilter', null)"
                class="px-4 py-2 rounded-full text-sm transition
            {{ is_null($categoryFilter) ? 'bg-accent text-white shadow' : 'bg-slate-100 dark:bg-slate-800 hover:opacity-80' }}">
                {{ __('All') }}
            </button>

            @foreach ($categories as $cat)
                <button wire:click="$set('categoryFilter', {{ $cat->id }})"
                    class="px-4 py-2 rounded-full text-sm transition
                {{ $categoryFilter === $cat->id
                    ? 'bg-accent text-white shadow'
                    : 'bg-slate-100 dark:bg-slate-800 hover:opacity-80' }}">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

    </div>

    {{-- Mobile cards --}}
    <div class="md:hidden space-y-4">

        @forelse ($products as $product)
            <div
                class="rounded-2xl border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900 p-4 space-y-4">

                {{-- Header --}}
                <div class="flex items-start gap-4">
                    <div
                        class="w-16 h-16 rounded-xl overflow-hidden
                            bg-slate-100 dark:bg-slate-800
                            ring-1 ring-slate-200 dark:ring-slate-700">
                        @if ($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400">
                                —
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-slate-900 dark:text-white truncate">
                            {{ $product->title }}
                        </h3>
                        <p class="text-xs text-slate-500 truncate">
                            {{ $product->category->name ?? __('No category') }}
                        </p>
                    </div>

                    {{-- Status --}}
                    <button wire:click="toggle({{ $product->id }})"
                        class="px-3 py-1 rounded-full text-xs font-medium
                    {{ $product->is_active ? 'bg-emerald-500/15 text-emerald-600' : 'bg-slate-500/10 text-slate-500' }}">
                        {{ $product->is_active ? __('Active') : __('Inactive') }}
                    </button>
                </div>

                {{-- Meta --}}
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>{{ __('Order') }}: {{ $product->display_order }}</span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                    <button wire:click="view({{ $product->id }})"
                        class="p-2 rounded-lg text-accent hover:bg-accent/10">
                        <flux:icon name="eye" class="w-4 h-4" />
                    </button>

                    <button wire:click="edit({{ $product->id }})"
                        class="p-2 rounded-lg text-sky-600 hover:bg-sky-500/10">
                        <flux:icon name="pencil-square" class="w-4 h-4" />
                    </button>

                    <button wire:click="askDelete({{ $product->id }})"
                        class="p-2 rounded-lg text-red-500 hover:bg-red-500/10">
                        <flux:icon name="trash" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-slate-500">
                {{ __('No products found') }}
            </div>
        @endforelse

    </div>

    {{-- Products table --}}
    <div wire:loading.remove wire:target="search,statusFilter,categoryFilter" class="hidden md:block">

        <div
            class="rounded-2xl overflow-hidden
           border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90">

            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800
                   text-slate-700 dark:text-slate-200">
                    <tr>
                        <th class="px-3 py-3"></th>
                        <th class="px-4 py-3">{{ __('Image') }}</th>
                        <th class="px-4 py-3">{{ __('Title') }}</th>
                        <th class="px-4 py-3">{{ __('Category') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Order') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody x-data x-init="new Sortable($el, {
                    handle: '[data-drag-handle]',
                    animation: 150,
                    onEnd() {
                        const ids = [...$el.children].map(el => el.dataset.id)
                        $wire.reorder(ids)
                    }
                })" class="divide-y divide-slate-100 dark:divide-slate-800">

                    @forelse ($products as $product)
                        <tr data-id="{{ $product->id }}" wire:key="product-{{ $product->id }}"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">

                            {{-- Drag --}}
                            <td class="px-3 text-slate-400 cursor-move" data-drag-handle>
                                <flux:icon name="bars-3" class="w-5 h-5" />
                            </td>

                            {{-- Image --}}
                            <td class="px-4 py-3">
                                <div
                                    class="w-14 h-14 rounded-lg overflow-hidden
                                   bg-slate-100 dark:bg-slate-800
                                   ring-1 ring-slate-200 dark:ring-slate-700">
                                    @if ($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}"
                                            class="w-full h-full object-cover
                                           hover:scale-110 transition-transform">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                                            —
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Title --}}
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                {{ $product->title }}
                            </td>

                            {{-- Category --}}
                            <td class="px-4 py-3 text-slate-500">
                                {{ $product->category->name ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                <button wire:click="toggle({{ $product->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                                   text-xs font-medium transition
                                   {{ $product->is_active
                                       ? 'bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/30'
                                       : 'bg-slate-500/10 text-slate-500 ring-1 ring-slate-500/30' }}">
                                    @if ($product->is_active)
                                        <flux:icon name="check" class="w-3.5 h-3.5" />
                                        {{ __('Active') }}
                                    @else
                                        <flux:icon name="x-mark" class="w-3.5 h-3.5" />
                                        {{ __('Inactive') }}
                                    @endif
                                </button>
                            </td>

                            {{-- Order --}}
                            <td class="px-4 py-3 text-slate-500">
                                {{ $product->display_order }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">

                                    <button wire:click="view({{ $product->id }})"
                                        class="p-1.5 rounded-lg text-accent hover:bg-accent/10 transition">
                                        <flux:icon name="eye" class="w-4 h-4" />
                                    </button>

                                    <button wire:click="edit({{ $product->id }})"
                                        class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-500/10 transition">
                                        <flux:icon name="pencil-square" class="w-4 h-4" />
                                    </button>

                                    <button wire:click="askDelete({{ $product->id }})"
                                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-500/10 transition">
                                        <flux:icon name="trash" class="w-4 h-4" />
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                                {{ __('No products found') }}
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal" wire:loading.remove
                wire:target="save">
            </div>

            {{-- Center Wrapper --}}
            <div class="relative h-full w-full flex items-start justify-center
                    px-4 py-6 sm:py-10">

                {{-- Modal Container --}}
                <div
                    class="w-full max-w-2xl
           rounded-2xl
           bg-white dark:bg-slate-900
           border border-slate-200 dark:border-slate-800
           shadow-2xl
           max-h-[90vh]
           flex flex-col overflow-hidden
           animate-in fade-in zoom-in duration-150">

                    {{-- Header (Sticky) --}}
                    <div
                        class="sticky top-0 z-10
                           px-6 py-4
                           bg-white dark:bg-slate-900
                           border-b border-slate-200 dark:border-slate-800
                           flex items-center justify-between">

                        <h3 class="text-lg font-semibold tracking-tight">
                            {{ $editing ? __('Edit product') : __('Create product') }}
                        </h3>

                        <button wire:click="closeModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300
                               transition"
                            aria-label="{{ __('Close') }}">
                            ✕
                        </button>
                    </div>

                    {{-- Body (Scrollable) --}}
                    <div class="p-6 space-y-6 overflow-y-auto">

                        {{-- Validation summary (ERROR COLORS ONLY) --}}
                        @if ($errors->any())
                            <div
                                class="rounded-xl
                                   border border-red-200
                                   bg-red-50 dark:bg-red-950/30
                                   p-4 text-sm
                                   text-red-700 dark:text-red-400">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Form --}}
                        <div class="space-y-5">

                            {{-- Title --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Title') }}
                                </label>
                                <input type="text" wire:model.defer="title"
                                    class="w-full rounded-lg input
                                       @error('title') ring-1 ring-red-500 @enderror" />
                                @error('title')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Description') }}
                                </label>
                                <textarea wire:model.defer="description" rows="3"
                                    class="w-full rounded-lg textarea
                                       @error('description') ring-1 ring-red-500 @enderror"></textarea>
                                @error('description')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Category') }}
                                </label>
                                <select wire:model.defer="category_id"
                                    class="w-full rounded-lg select
                                       @error('category_id') ring-1 ring-red-500 @enderror">
                                    <option value="">{{ __('No category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tags --}}
                            <div class="h-[200px]">
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Tags') }}
                                </label>
                                <select wire:model.defer="tags" id="tags" multiple
                                    class="selectize-input w-full rounded-lg  h-[160px] @error('tags') ring-1 ring-red-500 @enderror">
                                    <!-- عرض الـ tags الموجودة في الـ select -->
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag }}"
                                            {{ in_array($tag, $tags) ? 'selected' : '' }}>{{ $tag }}</option>
                                    @endforeach
                                </select>
                                @error('tags')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>



                            {{-- Images --}}
                            <div class="space-y-6">

                                {{-- Main Image --}}
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-slate-500">
                                        {{ __('Main image') }}
                                    </label>

                                    <input type="file" wire:model="main_image" class="text-sm text-slate-500" />

                                    @if ($main_image)
                                        <img src="{{ $main_image->temporaryUrl() }}"
                                            class="mt-3 w-40 h-40 rounded-xl object-cover
                                               ring-1 ring-slate-200 dark:ring-slate-700" />
                                    @elseif ($editing && $editing->main_image)
                                        <img src="{{ asset('storage/' . $editing->main_image) }}"
                                            class="mt-3 w-40 h-40 rounded-xl object-cover
                                               ring-1 ring-slate-200 dark:ring-slate-700" />
                                    @endif

                                    @error('main_image')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Gallery Images --}}
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-slate-500">
                                        {{ __('Gallery images') }}
                                    </label>

                                    <input type="file" wire:model="images" multiple
                                        class="text-sm text-slate-500" />

                                    @if ($editing && $editing->images)
                                        <div class="space-y-2">
                                            <p class="text-xs text-slate-500">
                                                {{ __('Current images') }}
                                            </p>

                                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                                @foreach ($editing->images as $img)
                                                    <div
                                                        class="relative w-full h-24 rounded-xl overflow-hidden
                           ring-1 ring-slate-200 dark:ring-slate-700">

                                                        <img src="{{ asset('storage/' . $img) }}"
                                                            class="w-full h-full object-cover
                               hover:scale-110 transition-transform" />

                                                        {{-- Overlay --}}
                                                        <div
                                                            class="absolute inset-0 bg-black/0 hover:bg-black/20 transition">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-3">
                                        @if ($images)
                                            <div class="space-y-2">
                                                <p class="text-xs text-slate-500">
                                                    {{ __('New images') }}
                                                </p>

                                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                                    @foreach ($images as $img)
                                                        <div
                                                            class="w-full h-24 rounded-xl overflow-hidden
                           ring-1 ring-accent/40">
                                                            <img src="{{ $img->temporaryUrl() }}"
                                                                class="w-full h-full object-cover
                               hover:scale-110 transition-transform" />
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                    </div>

                                    @error('images.*')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- Footer (Sticky) --}}
                    <div
                        class="sticky bottom-0
                           px-6 py-4
                           bg-white dark:bg-slate-900
                           border-t border-slate-200 dark:border-slate-800
                           flex items-center justify-between">

                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model.defer="is_active" />
                            {{ __('Active') }}
                        </label>

                        <div class="flex gap-2">
                            <button wire:click="closeModal" wire:loading.attr="disabled" wire:target="save"
                                class="px-4 py-2 rounded-lg text-sm
           bg-slate-200 dark:bg-slate-800
           hover:opacity-80 transition
           disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ __('Cancel') }}
                            </button>


                            <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                                class="relative inline-flex items-center justify-center gap-2
           px-5 py-2.5 rounded-lg text-sm font-medium
           bg-accent text-white
           transition
           hover:opacity-90
           disabled:opacity-60 disabled:cursor-not-allowed">

                                {{-- الحالة العادية --}}
                                <span wire:loading.remove wire:target="save">
                                    {{ __('Save') }}
                                </span>

                                {{-- حالة التحميل --}}
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>
                                    </svg>
                                    {{ __('Saving...') }}
                                </span>
                            </button>

                        </div>
                    </div>

                </div>
            </div>



        </div>
    @endif

    {{-- View Modal --}}
    @if ($showViewModal && $viewing)
        <div class="fixed inset-0 z-50">
            {{-- Overlay --}}
            <div wire:click="closeViewModal" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

            {{-- Center wrapper (scroll-safe) --}}
            <div class="relative h-full w-full flex items-start justify-center py-10 px-4">
                {{-- Modal --}}
                <div
                    class="w-full max-w-3xl rounded-xl
                       bg-white dark:bg-slate-900
                       border border-slate-200 dark:border-slate-800
                       shadow-xl overflow-hidden">

                    {{-- Header ثابت --}}
                    <div
                        class="flex justify-between items-center px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="text-lg font-semibold">
                            {{ __('Product details') }}
                        </h3>

                        <button wire:click="closeViewModal" class="text-slate-400 hover:text-slate-600">
                            ✕
                        </button>
                    </div>

                    {{-- Body قابل للسكرول --}}
                    <div class="p-6 space-y-6 max-h-[85vh] overflow-y-auto">

                        {{-- Main Image --}}
                        @if ($viewing->main_image)
                            <div
                                class="w-full h-64 rounded-lg overflow-hidden ring-1 ring-slate-200 dark:ring-slate-800">
                                <img src="{{ asset('storage/' . $viewing->main_image) }}"
                                    alt="{{ $viewing->title }}" class="w-full h-full object-cover" />
                            </div>
                        @endif

                        {{-- Gallery (other images) --}}
                        @php
                            $gallery = $viewing->images ?? [];
                            // fallback لو كانت JSON string
                            if (is_string($gallery)) {
                                $decoded = json_decode($gallery, true);
                                $gallery = is_array($decoded) ? $decoded : [];
                            }
                            $gallery = is_array($gallery) ? $gallery : [];
                            // استبعاد main_image لو كانت مكررة
                            $gallery = array_values(
                                array_filter($gallery, function ($path) use ($viewing) {
                                    return $path && $path !== $viewing->main_image;
                                }),
                            );
                        @endphp

                        @if (count($gallery))
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-slate-500">
                                        {{ __('More images') }}
                                    </h4>
                                    <span class="text-xs text-slate-400">
                                        {{ count($gallery) }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach ($gallery as $img)
                                        <div
                                            class="group relative rounded-lg overflow-hidden
                                                bg-slate-100 dark:bg-slate-800
                                                ring-1 ring-slate-200 dark:ring-slate-700">
                                            <img src="{{ asset('storage/' . $img) }}" alt="image"
                                                class="w-full h-24 object-cover
                                                   group-hover:scale-110 transition-transform duration-300" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Info --}}
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-slate-400">{{ __('Title') }}</span>
                                <p class="font-medium">{{ $viewing->title }}</p>
                            </div>

                            <div>
                                <span class="text-slate-400">{{ __('Category') }}</span>
                                <p>{{ $viewing->category->name ?? '—' }}</p>
                            </div>

                            {{-- Tags --}}
                            <div>
                                <span class="text-slate-400 text-sm">{{ __('Tags') }}</span>
                                <p class="mt-1 text-slate-600 dark:text-slate-300">
                                    @if ($viewing->tags)
                                        {{ implode(', ', json_decode($viewing->tags)) }}
                                    @else
                                        {{ __('No tags') }}
                                    @endif
                                </p>
                            </div>

                            <div>
                                <span class="text-slate-400">{{ __('Status') }}</span>
                                <p>{{ $viewing->is_active ? __('Active') : __('Inactive') }}</p>
                            </div>

                            <div>
                                <span class="text-slate-400">{{ __('Order') }}</span>
                                <p>{{ $viewing->display_order }}</p>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if ($viewing->description)
                            <div>
                                <span class="text-slate-400 text-sm">{{ __('Description') }}</span>
                                <p class="mt-1 text-slate-600 dark:text-slate-300">
                                    {{ $viewing->description }}
                                </p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- Delete Confirmation Modal --}}
    <x-modals.confirm :show="$showDeleteModal" type="danger" :title="__('Delete product')" :message="__('Are you sure you want to delete this product? This action cannot be undone.')" :confirmAction="'wire:click=confirmDelete'"
        :cancelAction="'wire:click=cancelDelete'" confirmLoadingTarget="confirmDelete" :confirmText="__('Yes, delete')" />

    {{-- Selectize --}}
    <script>
        // الاستماع لحدث 'modalOpened' الذي تم إطلاقه من Livewire
        window.addEventListener('modalOpened', function() {
            // تأكد من أن jQuery و Selectize.js تم تحميلهما بشكل صحيح
            if (window.jQuery) {
                // تهيئة Selectize بعد فتح الـ modal
                $('#tags').selectize({
                    plugins: ['remove_button'],
                    delimiter: ',',
                    persist: false,
                    create: true,
                    maxItems: null,
                    valueField: 'value',
                    labelField: 'label',
                    searchField: 'label',
                    items: @this.entangle('tags').defer, // تأكد من الربط الصحيح
                    onChange: function(value) {
                        @this.set('tags', value); // تأكد من أن الـ tags يتم تحديثها عند التغيير
                    }
                });
            } else {
                console.error("jQuery is not loaded correctly. Ensure jQuery is loaded before Selectize.");
            }
        });
    </script>
</div>
