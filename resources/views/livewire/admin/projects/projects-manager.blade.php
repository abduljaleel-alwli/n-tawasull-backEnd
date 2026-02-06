<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Project;
use App\Models\Category;
use App\Actions\Projects\CreateProject;
use App\Actions\Projects\UpdateProject;
use App\Actions\Projects\DeleteProject;
use App\Actions\Projects\ToggleProjectStatus;
use App\Actions\Projects\ReorderProjects;

new class extends Component {
    use WithFileUploads;
    use AuthorizesRequests;

    /** Search */
    public string $search = '';
    public string $statusFilter = 'all'; // all | active | inactive
    public ?int $categoryFilter = null;

    /** Listing */
    public $projects;

    /** Form state */
    public bool $showModal = false;
    public ?Project $editing = null;

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public bool $showViewModal = false;
    public ?Project $viewing = null;

    public string $title = '';
    public string $description = '';
    public ?int $category_id = null;
    public $main_image = null;
    public array $images = [];
    public bool $is_active = true;
    public array $features = []; // New array for features
    public string $content = ''; // New string for content

    public array $videos = []; // New array for videos

    /** Data */
    public $categories = [];

    // Editor state
    public bool $contentEditorOpen = false;
    public ?int $contentEditorProjectId = null;
    public string $contentEditorContent = '';

    public function mount(): void
    {
        // Only admin & super-admin (super-admin bypass via Gate::before)
        $this->authorize('access-dashboard');

        $this->categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->loadProjects();
    }

    public function loadProjects(): void
    {
        $this->projects = Project::query()
            ->with('category:id,name')
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
        $this->loadProjects();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadProjects();
    }

    public function updatedCategoryFilter(): void
    {
        $this->loadProjects();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Project $project): void
    {
        $this->editing = $project;

        $this->title = $project->title;
        $this->description = (string) $project->description;
        $this->category_id = $project->category_id;
        $this->is_active = (bool) $project->is_active;

        $this->features = $project->features ?? []; // Load features if exists
        $this->content = (string) ($project->content ?? ''); // Load content if exists

        // Load videos if exists
        $this->videos = $project->videos ?? [];

        foreach ($this->videos as $i => $v) {
            if (!is_array($v)) {
                $v = [];
            }
            if (empty($v['_key'])) {
                $v['_key'] = (string) \Illuminate\Support\Str::uuid();
            }
            $this->videos[$i] = $v;
        }

        $this->display_order = (int) $project->display_order;

        $this->showModal = true;
    }

    public function save(CreateProject $create, UpdateProject $update): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'main_image' => ['nullable', 'image', 'max:10240'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:10240'],
            'is_active' => ['boolean'],
            'features' => ['nullable', 'array'], // Validation for features
            'content' => ['nullable', 'string'], // Validation for content

            // videos
            'videos' => ['nullable', 'array'],
            'videos.*.type' => ['nullable', 'in:url,iframe'],
            'videos.*.provider' => ['nullable', 'in:youtube,vimeo,other'],
            'videos.*.title' => ['nullable', 'string', 'max:255'],
            'videos.*.url' => ['nullable', 'string'],
            'videos.*.iframe' => ['nullable', 'string'],
        ]);

        // تنظيف المفاتيح المؤقتة
        if (isset($data['videos'])) {
            $data['videos'] = $this->cleanVideosForStorage($data['videos']);
            $data['videos'] = count($data['videos']) ? $data['videos'] : null;
        }

        if ($this->editing) {
            $update->execute($this->editing, $data);

            $this->toast('success', __('Project updated successfully'));
        } else {
            $create->execute($data);

            $this->toast('success', __('Project created successfully'));
        }

        $this->closeModal();
        $this->loadProjects();
    }

    public function addFeature(): void
    {
        $this->features[] = ['title' => '', 'description' => ''];
    }

    public function removeFeature(int $index): void
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features); // Reindex the array
    }

    public function addVideo(): void
    {
        $this->videos[] = [
            '_key' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'url',
            'provider' => 'youtube',
            'title' => '',
            'url' => '',
            'iframe' => '',
        ];
    }

    public function removeVideo(int $index): void
    {
        unset($this->videos[$index]);
        $this->videos = array_values($this->videos);
    }

    // Clean videos data before saving to database (remove _key and ensure structure)
    private function cleanVideosForStorage(array $videos): array
    {
        $clean = [];

        foreach ($videos as $video) {
            if (!is_array($video)) {
                continue;
            }

            // إزالة _key
            unset($video['_key']);

            // Trim + تحويل '' إلى null
            foreach (['type', 'provider', 'title', 'url', 'iframe'] as $k) {
                if (array_key_exists($k, $video) && is_string($video[$k])) {
                    $video[$k] = trim($video[$k]);
                    if ($video[$k] === '') {
                        $video[$k] = null;
                    }
                }
            }

            // Defaults
            $type = $video['type'] ?? 'url';
            $type = in_array($type, ['url', 'iframe'], true) ? $type : 'url';
            $video['type'] = $type;

            if ($type === 'url') {
                // إذا URL: لازم يوجد رابط، غير ذلك تجاهله
                if (empty($video['url'])) {
                    continue;
                }

                // provider افتراضي
                $video['provider'] = in_array($video['provider'] ?? 'other', ['youtube', 'vimeo', 'other'], true) ? $video['provider'] ?? 'other' : 'other';

                // iframe غير مطلوب
                $video['iframe'] = null;
            } else {
                // إذا IFRAME: لازم يوجد iframe، غير ذلك تجاهله
                if (empty($video['iframe'])) {
                    continue;
                }

                // provider غير مهم هنا
                $video['provider'] = $video['provider'] ?? 'other';
                $video['url'] = null;
            }

            $clean[] = $video;
        }

        return array_values($clean);
    }

    // Content Editor Methods
    public function openContentEditor(int $id): void
    {
        $project = Project::findOrFail($id);

        $this->contentEditorProjectId = $project->id;
        $this->contentEditorContent = (string) ($project->content ?? '');
        $this->contentEditorOpen = true;
    }

    public function closeContentEditor(): void
    {
        $this->contentEditorOpen = false;
        $this->contentEditorProjectId = null;
        $this->contentEditorContent = '';
    }

    public function saveContentOnly(): void
    {
        if (!$this->contentEditorProjectId) {
            return;
        }

        Project::whereKey($this->contentEditorProjectId)->update(['content' => $this->contentEditorContent]);

        $this->closeContentEditor();
    }

    public function toggle(ToggleProjectStatus $toggle, Project $project): void
    {
        $toggle->execute($project);
        $this->toast('success', $project->is_active ? __('Project activated successfully') : __('Project deactivated successfully'));

        $this->loadProjects();
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

    public function confirmDelete(DeleteProject $delete): void
    {
        if (!$this->deleteId) {
            return;
        }

        $project = Project::findOrFail($this->deleteId);
        $delete->execute($project);

        $this->toast('success', __('Project deleted successfully'));

        $this->cancelDelete();
        $this->loadProjects();
    }

    public function delete(DeleteProject $delete, Project $project): void
    {
        $delete->execute($project);

        $this->toast('success', __('Project deleted successfully'));
        $this->loadProjects();
    }

    public function reorder(array $ids): void
    {
        app(ReorderProjects::class)->execute($ids);

        $this->toast('success', __('Projects reordered successfully'));
        $this->loadProjects();
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = false;
    }

    public function view(Project $project): void
    {
        $this->viewing = $project;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->viewing = null;
        $this->showViewModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['editing', 'title', 'description', 'category_id', 'main_image', 'images', 'is_active', 'features', 'content', 'videos']);

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
                <p class="text-xs text-slate-500">{{ __('Total projects') }}</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ $projects->count() }}
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
                    {{ $projects->where('is_active', true)->count() }}
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
                    {{ $projects->where('is_active', false)->count() }}
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

            <input wire:model.live="search" type="text" placeholder="{{ __('Search projects...') }}"
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
                {{ __('Total') }}: {{ count($projects) }}
            </span>

            {{-- Add --}}
            <button wire:click="create"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                   bg-accent text-white text-sm font-medium
                   hover:opacity-90 transition">
                <flux:icon name="plus" class="w-4 h-4" />
                {{ __('Add project') }}
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

        @forelse ($projects as $project)
            <div
                class="rounded-2xl border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900 p-4 space-y-4">

                {{-- Header --}}
                <div class="flex items-start gap-4">
                    <div
                        class="w-16 h-16 rounded-xl overflow-hidden
                            bg-slate-100 dark:bg-slate-800
                            ring-1 ring-slate-200 dark:ring-slate-700">
                        @if ($project->main_image)
                            <img src="{{ asset('storage/' . $project->main_image) }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400">
                                —
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-slate-900 dark:text-white truncate">
                            {{ $project->title }}
                        </h3>
                        <p class="text-xs text-slate-500 truncate">
                            {{ $project->category->name ?? __('No category') }}
                        </p>
                    </div>

                    {{-- Status --}}
                    <button wire:click="toggle({{ $project->id }})"
                        class="px-3 py-1 rounded-full text-xs font-medium
                    {{ $project->is_active ? 'bg-emerald-500/15 text-emerald-600' : 'bg-slate-500/10 text-slate-500' }}">
                        {{ $project->is_active ? __('Active') : __('Inactive') }}
                    </button>
                </div>

                {{-- Meta --}}
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>{{ __('Order') }}: {{ $project->display_order }}</span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                    <button wire:click="view({{ $project->id }})"
                        class="p-2 rounded-lg text-accent hover:bg-accent/10">
                        <flux:icon name="eye" class="w-4 h-4" />
                    </button>

                    <button wire:click="edit({{ $project->id }})"
                        class="p-2 rounded-lg text-sky-600 hover:bg-sky-500/10">
                        <flux:icon name="pencil-square" class="w-4 h-4" />
                    </button>

                    <button wire:click="openContentEditor({{ $project->id }})"
                        class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-500/10 transition" title="Edit content">
                        <flux:icon name="document-text" class="w-4 h-4" />
                    </button>

                    <button wire:click="askDelete({{ $project->id }})"
                        class="p-2 rounded-lg text-red-500 hover:bg-red-500/10">
                        <flux:icon name="trash" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-slate-500">
                {{ __('No projects found') }}
            </div>
        @endforelse

    </div>

    {{-- projects table --}}
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

                    @forelse ($projects as $project)
                        <tr data-id="{{ $project->id }}" wire:key="project-{{ $project->id }}"
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
                                    @if ($project->main_image)
                                        <img src="{{ asset('storage/' . $project->main_image) }}"
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
                                {{ $project->title }}
                            </td>

                            {{-- Category --}}
                            <td class="px-4 py-3 text-slate-500">
                                {{ $project->category->name ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                <button wire:click="toggle({{ $project->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                                   text-xs font-medium transition
                                   {{ $project->is_active
                                       ? 'bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/30'
                                       : 'bg-slate-500/10 text-slate-500 ring-1 ring-slate-500/30' }}">
                                    @if ($project->is_active)
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
                                {{ $project->display_order }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">

                                    <button wire:click="view({{ $project->id }})"
                                        class="p-1.5 rounded-lg text-accent hover:bg-accent/10 transition">
                                        <flux:icon name="eye" class="w-4 h-4" />
                                    </button>

                                    <button wire:click="edit({{ $project->id }})"
                                        class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-500/10 transition">
                                        <flux:icon name="pencil-square" class="w-4 h-4" />
                                    </button>

                                    <button wire:click="openContentEditor({{ $project->id }})"
                                        class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-500/10 transition"
                                        title="Edit content">
                                        <flux:icon name="document-text" class="w-4 h-4" />
                                    </button>

                                    <button wire:click="askDelete({{ $project->id }})"
                                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-500/10 transition">
                                        <flux:icon name="trash" class="w-4 h-4" />
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                                {{ __('No projects found') }}
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- Content Editor Modal --}}
    @if ($contentEditorOpen)
        <div class="fixed inset-0 z-50">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" wire:click="closeContentEditor"></div>

            {{-- Dialog --}}
            <div class="relative z-10 flex min-h-full items-end sm:items-center justify-center p-2 sm:p-4">
                <div class="w-full sm:max-w-4xl lg:max-w-5xl
                       max-h-[92vh] sm:max-h-[88vh]
                       overflow-hidden
                       rounded-2xl bg-white dark:bg-slate-900
                       shadow-2xl ring-1 ring-black/10 dark:ring-white/10"
                    role="dialog" aria-modal="true">
                    {{-- Header --}}
                    <div
                        class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3 border-b border-slate-200/70 dark:border-slate-700/70">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                                {{ __('Edit content') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ __('Write your content, then save.') }}
                            </p>
                        </div>

                        <button type="button" wire:click="closeContentEditor" wire:loading.attr="disabled"
                            wire:target="closeContentEditor,saveContentOnly"
                            class="shrink-0 inline-flex items-center justify-center
                               h-9 w-9 rounded-xl
                               bg-slate-100 hover:bg-slate-200
                               dark:bg-slate-800 dark:hover:bg-slate-700
                               text-slate-700 dark:text-slate-200
                               disabled:opacity-60 disabled:cursor-not-allowed"
                            aria-label="Close">
                            <span wire:loading.remove wire:target="closeContentEditor,saveContentOnly">✕</span>

                            <svg wire:loading wire:target="closeContentEditor,saveContentOnly"
                                class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                        </button>
                    </div>

                    {{-- Body (scrollable) --}}
                    <div class="p-4 sm:p-6 overflow-y-auto max-h-[calc(92vh-120px)] sm:max-h-[calc(88vh-128px)]">
                        <x-rich-editor wire:model.defer="contentEditorContent" />
                    </div>

                    {{-- Footer --}}
                    <div
                        class="flex flex-col sm:flex-row gap-2 justify-end px-4 sm:px-6 py-3 border-t border-slate-200/70 dark:border-slate-700/70 bg-slate-50/60 dark:bg-slate-950/20">
                        <button type="button" wire:click="closeContentEditor" wire:loading.attr="disabled"
                            wire:target="closeContentEditor,saveContentOnly"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl
                               bg-slate-200 hover:bg-slate-300
                               dark:bg-slate-800 dark:hover:bg-slate-700
                               text-slate-900 dark:text-slate-100
                               inline-flex items-center justify-center gap-2
                               disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="closeContentEditor" class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span wire:loading.remove wire:target="closeContentEditor">
                                {{ __('Cancel') }}
                            </span>
                            <span wire:loading wire:target="closeContentEditor">
                                {{ __('Closing...') }}
                            </span>
                        </button>

                        <button type="button" wire:click="saveContentOnly" wire:loading.attr="disabled"
                            wire:target="saveContentOnly"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl
                               bg-accent hover:opacity-95
                               text-white font-medium shadow
                               inline-flex items-center justify-center gap-2
                               disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="saveContentOnly" class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span wire:loading.remove wire:target="saveContentOnly">
                                {{ __('Save') }}
                            </span>
                            <span wire:loading wire:target="saveContentOnly">
                                {{ __('Saving...') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif



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
                            {{ $editing ? __('Edit project') : __('Create project') }}
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

                        {{-- Validation summary --}}
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
                                            class="mt-3 w-60 h-40 rounded-xl object-cover
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

                            {{-- Features --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Features (Title and Description)') }}
                                </label>
                                <div class="space-y-2 mb-2">
                                    @foreach ($features as $index => $feature)
                                        <div class="flex gap-2">
                                            <input type="text"
                                                wire:model.defer="features.{{ $index }}.title"
                                                placeholder="Title" class="w-full rounded-lg input" />
                                            <textarea wire:model.defer="features.{{ $index }}.description" placeholder="Description"
                                                class="w-full rounded-lg textarea"></textarea>

                                            <!-- زر حذف -->
                                            <button type="button" wire:click="removeFeature({{ $index }})"
                                                class="bg-red-500 text-white hover:bg-red-700 rounded-lg px-4 py-2 transition-all">
                                                <flux:icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button wire:click="addFeature"
                                    class="bg-accent text-white hover:bg-accent-dark rounded-lg px-4 py-2 text-sm transition-all">
                                    {{ __('Add more features') }}
                                </button>
                            </div>

                            {{-- Videos --}}
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-500">
                                    {{ __('Videos') }}
                                </label>

                                <div class="space-y-3 mb-2">
                                    @foreach ($videos as $index => $video)
                                        <div wire:key="video-row-{{ $video['_key'] ?? $index }}"
                                            class="rounded-xl border border-slate-200 dark:border-slate-800 p-3 space-y-3"
                                            x-data="{
                                                type: @entangle('videos.' . $index . '.type').live,
                                                provider: @entangle('videos.' . $index . '.provider').live,
                                            
                                                get urlPlaceholder() {
                                                    if (this.provider === 'youtube') return 'https://www.youtube.com/watch?v=...';
                                                    if (this.provider === 'vimeo') return 'https://vimeo.com/123456';
                                                    return 'https://...';
                                                },
                                            
                                                init() {
                                                    this.$watch('type', (v) => {
                                                        if (v === 'url') {
                                                            $wire.set('videos.{{ $index }}.iframe', null); // ✅ امسح iframe
                                                            if (!this.provider) this.provider = 'youtube';
                                                        } else {
                                                            $wire.set('videos.{{ $index }}.url', null); // ✅ امسح url
                                                            this.provider = 'other';
                                                        }
                                                    });
                                                },
                                            }">
                                            <div class="grid sm:grid-cols-3 gap-2">
                                                {{-- Type --}}
                                                <div>
                                                    <label
                                                        class="block mb-1 text-xs text-slate-500">{{ __('Type') }}</label>
                                                    <select class="w-full rounded-lg select" x-model="type">
                                                        <option value="url">{{ __('URL') }}</option>
                                                        <option value="iframe">{{ __('Iframe') }}</option>
                                                    </select>
                                                </div>

                                                {{-- Provider (only for URL) --}}
                                                <div x-show="type === 'url'" x-cloak>
                                                    <label
                                                        class="block mb-1 text-xs text-slate-500">{{ __('Provider') }}</label>
                                                    <select class="w-full rounded-lg select" x-model="provider">
                                                        <option value="youtube">YouTube</option>
                                                        <option value="vimeo">Vimeo</option>
                                                        <option value="other">{{ __('Other') }}</option>
                                                    </select>
                                                </div>

                                                {{-- Title --}}
                                                <div :class="type === 'url' ? '' : 'sm:col-span-2'">
                                                    <label
                                                        class="block mb-1 text-xs text-slate-500">{{ __('Title (optional)') }}</label>
                                                    <input type="text"
                                                        wire:model.defer="videos.{{ $index }}.title"
                                                        class="w-full rounded-lg input" />
                                                </div>
                                            </div>

                                            {{-- URL Field --}}
                                            <div x-show="type === 'url'" x-cloak>
                                                <label class="block mb-1 text-xs text-slate-500">
                                                    {{ __('Video URL') }}
                                                    <span x-show="provider === 'youtube'">(YouTube)</span>
                                                    <span x-show="provider === 'vimeo'">(Vimeo)</span>
                                                </label>

                                                <input type="text"
                                                    wire:model.defer="videos.{{ $index }}.url"
                                                    :placeholder="urlPlaceholder" class="w-full rounded-lg input" />


                                                @error("videos.$index.url")
                                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- Iframe Field --}}
                                            <div x-show="type === 'iframe'" x-cloak>
                                                <label
                                                    class="block mb-1 text-xs text-slate-500">{{ __('Iframe embed code') }}</label>

                                                <textarea wire:model.defer="videos.{{ $index }}.iframe" rows="4" placeholder="<iframe ...></iframe>"
                                                    class="w-full rounded-lg textarea"></textarea>

                                                @error("videos.$index.iframe")
                                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="flex justify-end">
                                                <button type="button" wire:click="removeVideo({{ $index }})"
                                                    class="bg-red-500 text-white hover:bg-red-700 rounded-lg px-4 py-2 transition-all">
                                                    <flux:icon name="trash" class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>

                                <button type="button" wire:click="addVideo"
                                    class="bg-accent text-white hover:bg-accent-dark rounded-lg px-4 py-2 text-sm transition-all">
                                    {{ __('Add video') }}
                                </button>
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
                            <button type="button" wire:click="closeModal" wire:loading.attr="disabled"
                                wire:target="closeModal,save"
                                class="w-full sm:w-auto px-4 py-2 rounded-xl
               bg-slate-200 hover:bg-slate-300
               dark:bg-slate-800 dark:hover:bg-slate-700
               text-slate-900 dark:text-slate-100
               inline-flex items-center justify-center gap-2
               disabled:opacity-60 disabled:cursor-not-allowed">
                                <svg wire:loading wire:target="closeModal" class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                    </path>
                                </svg>

                                <span wire:loading.remove wire:target="closeModal"> {{ __('Cancel') }}</span>
                                <span wire:loading wire:target="closeModal"> {{ __('Canceling...') }}</span>
                            </button>



                            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                                class="w-full sm:w-auto px-4 py-2 rounded-xl
               bg-accent hover:opacity-95
               text-white font-medium shadow
               inline-flex items-center justify-center gap-2
               disabled:opacity-60 disabled:cursor-not-allowed">
                                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" viewBox="0 0 24 24"
                                    fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                    </path>
                                </svg>

                                <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
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
            <div wire:click="closeViewModal" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

            {{-- Center wrapper (scroll-safe) --}}
            <div class="relative z-10 flex min-h-full items-end sm:items-center justify-center p-2 sm:p-6">
                {{-- Modal --}}
                <div class="w-full sm:max-w-3xl lg:max-w-4xl
                       max-h-[92vh] sm:max-h-[88vh]
                       overflow-hidden
                       rounded-2xl bg-white dark:bg-slate-900
                       shadow-2xl ring-1 ring-black/10 dark:ring-white/10"
                    role="dialog" aria-modal="true">
                    {{-- Header (sticky look) --}}
                    <div
                        class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3
                            border-b border-slate-200/70 dark:border-slate-700/70">
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-100 truncate">
                                {{ __('Project details') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $viewing->title }}
                            </p>
                        </div>

                        <button type="button" wire:click="closeViewModal" wire:loading.attr="disabled"
                            wire:target="closeViewModal"
                            class="shrink-0 inline-flex items-center justify-center gap-2
                               h-9 rounded-xl px-3
                               bg-slate-100 hover:bg-slate-200
                               dark:bg-slate-800 dark:hover:bg-slate-700
                               text-slate-700 dark:text-slate-200
                               disabled:opacity-60 disabled:cursor-not-allowed"
                            aria-label="Close">
                            <svg wire:loading wire:target="closeViewModal" class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span wire:loading.remove wire:target="closeViewModal">✕</span>
                            <span wire:loading wire:target="closeViewModal" class="text-xs">
                                {{ __('Closing...') }}
                            </span>
                        </button>
                    </div>

                    {{-- Body (scrollable) --}}
                    <div
                        class="p-4 sm:p-6 overflow-y-auto min-w-0
                            max-h-[calc(92vh-60px)] sm:max-h-[calc(88vh-64px)]">

                        <div class="space-y-6">
                            {{-- Main Image --}}
                            @if ($viewing->main_image)
                                <div
                                    class="relative overflow-hidden rounded-2xl ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                                    <div class="aspect-[16/9] sm:aspect-[21/9] bg-slate-100 dark:bg-slate-800">
                                        <img src="{{ asset('storage/' . $viewing->main_image) }}"
                                            alt="{{ $viewing->title }}" class="h-full w-full object-cover"
                                            loading="lazy" />
                                    </div>

                                    {{-- subtle gradient for premium look --}}
                                    <div
                                        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent">
                                    </div>

                                    {{-- status pill --}}
                                    <div class="absolute top-3 left-3">
                                        <span
                                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
                                                 bg-white/90 text-slate-900 ring-1 ring-black/10
                                                 dark:bg-slate-900/80 dark:text-slate-100 dark:ring-white/10">
                                            <span
                                                class="inline-block h-2 w-2 rounded-full {{ $viewing->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            {{ $viewing->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Gallery (other images) --}}
                            @php
                                $gallery = $viewing->images ?? [];
                                if (is_string($gallery)) {
                                    $decoded = json_decode($gallery, true);
                                    $gallery = is_array($decoded) ? $decoded : [];
                                }
                                $gallery = is_array($gallery) ? $gallery : [];
                                $gallery = array_values(
                                    array_filter($gallery, function ($path) use ($viewing) {
                                        return $path && $path !== $viewing->main_image;
                                    }),
                                );
                            @endphp

                            @if (count($gallery))
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                            {{ __('More images') }}
                                        </h4>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ count($gallery) }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                        @foreach ($gallery as $img)
                                            <div
                                                class="group relative overflow-hidden rounded-2xl
                                                    bg-slate-100 dark:bg-slate-800
                                                    ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                                                <div class="aspect-[4/3]">
                                                    <img src="{{ asset('storage/' . $img) }}" alt="image"
                                                        class="h-full w-full object-cover
                                                           group-hover:scale-110 transition-transform duration-300"
                                                        loading="lazy" />
                                                </div>
                                                <div
                                                    class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition
                                                        bg-gradient-to-t from-black/30 via-transparent to-transparent">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Info cards --}}
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div
                                    class="rounded-2xl p-4 ring-1 ring-slate-200/70 dark:ring-slate-700/70 bg-white dark:bg-slate-900">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Title') }}</p>
                                    <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100 break-words">
                                        {{ $viewing->title }}</p>
                                </div>

                                <div
                                    class="rounded-2xl p-4 ring-1 ring-slate-200/70 dark:ring-slate-700/70 bg-white dark:bg-slate-900">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Category') }}</p>
                                    <p class="mt-1 font-medium text-slate-900 dark:text-slate-100">
                                        {{ $viewing->category->name ?? '—' }}</p>
                                </div>

                                <div
                                    class="rounded-2xl p-4 ring-1 ring-slate-200/70 dark:ring-slate-700/70 bg-white dark:bg-slate-900">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Status') }}</p>
                                    <p class="mt-1 font-medium text-slate-900 dark:text-slate-100">
                                        {{ $viewing->is_active ? __('Active') : __('Inactive') }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl p-4 ring-1 ring-slate-200/70 dark:ring-slate-700/70 bg-white dark:bg-slate-900">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Order') }}</p>
                                    <p class="mt-1 font-medium text-slate-900 dark:text-slate-100">
                                        {{ $viewing->display_order }}</p>
                                </div>
                            </div>

                            {{-- Description --}}
                            @if ($viewing->description)
                                <div
                                    class="rounded-2xl p-4 ring-1 ring-slate-200/70 dark:ring-slate-700/70 bg-slate-50/60 dark:bg-slate-950/20">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Description') }}</p>
                                    <p class="mt-2 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                                        {{ $viewing->description }}
                                    </p>
                                </div>
                            @endif
                            {{-- Features + Content --}}
                            @php
                                // features: قد تكون array أو JSON string
                                $vFeatures = $viewing->features ?? [];

                                if (is_string($vFeatures)) {
                                    $decoded = json_decode($vFeatures, true);
                                    $vFeatures = is_array($decoded) ? $decoded : [];
                                }

                                $vFeatures = is_array($vFeatures) ? $vFeatures : [];
                                // نظّف العناصر الفاضية
                                $vFeatures = array_values(
                                    array_filter($vFeatures, function ($f) {
                                        $t = is_array($f) ? trim((string) ($f['title'] ?? '')) : '';
                                        $d = is_array($f) ? trim((string) ($f['description'] ?? '')) : '';
                                        return $t !== '' || $d !== '';
                                    }),
                                );

                                $vContent = (string) ($viewing->content ?? '');

                                // videos: قد تكون array أو JSON string
                                $vVideos = $viewing->videos ?? [];
                                if (is_string($vVideos)) {
                                    $decoded = json_decode($vVideos, true);
                                    $vVideos = is_array($decoded) ? $decoded : [];
                                }
                                $vVideos = is_array($vVideos) ? $vVideos : [];

                            @endphp

                            {{-- Features --}}
                            @if (count($vFeatures))
                                <div
                                    class="rounded-2xl p-4 sm:p-5 ring-1 ring-slate-200/70 dark:ring-slate-700/70 bg-white dark:bg-slate-900">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ __('Features') }}
                                        </h4>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ count($vFeatures) }}
                                        </span>
                                    </div>

                                    <div class="grid sm:grid-cols-2 gap-3">
                                        @foreach ($vFeatures as $feature)
                                            <div
                                                class="rounded-2xl p-4
                            bg-slate-50/70 dark:bg-slate-950/20
                            ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                    {{ $feature['title'] ?? '—' }}
                                                </p>

                                                @if (!empty($feature['description']))
                                                    <p
                                                        class="mt-1 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                                                        {{ $feature['description'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Content (HTML from TinyMCE) --}}
                            @if (trim(strip_tags($vContent)) !== '')
                                <div
                                    class="rounded-2xl p-4 sm:p-5 ring-1 ring-slate-200/70 dark:ring-slate-700/70 bg-slate-50/60 dark:bg-slate-950/20">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ __('Content') }}
                                        </h4>

                                        {{-- optional hint --}}
                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            HTML
                                        </span>
                                    </div>

                                    {{-- عرض المحتوى كما هو (محرر TinyMCE) --}}
                                    <div
                                        class="prose prose-slate dark:prose-invert max-w-none
                    prose-headings:font-semibold
                    prose-a:text-accent
                    prose-table:w-full
                    prose-img:rounded-xl
                    prose-blockquote:border-r-4 prose-blockquote:pr-4
                    [&_*]:direction-rtl">
                                        {!! $vContent !!}
                                    </div>
                                </div>
                            @endif
{{-- Videos --}}
@if (count($vVideos))
    <div
        class="rounded-2xl p-4 sm:p-5
               ring-1 ring-slate-200/70 dark:ring-slate-700/70
               bg-white dark:bg-slate-900">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <flux:icon name="play-circle" class="w-4 h-4 text-accent" />
                {{ __('Videos') }}
            </h4>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                {{ count($vVideos) }}
            </span>
        </div>

        {{-- Videos Grid --}}
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($vVideos as $vid)
                @php
                    $type = $vid['type'] ?? 'url';
                    $title = $vid['title'] ?? null;
                    $url = $vid['url'] ?? null;
                    $iframe = $vid['iframe'] ?? null;

                    // Detect YouTube / Vimeo
                    $embedUrl = null;

                    if ($type === 'url' && $url) {
                        // YouTube
                        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([^&?/]+)~', $url, $m)) {
                            $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                        }

                        // Vimeo
                        elseif (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
                            $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
                        }
                    }
                @endphp

                <div
                    class="group rounded-2xl overflow-hidden
                           bg-slate-50/70 dark:bg-slate-950/30
                           ring-1 ring-slate-200/70 dark:ring-slate-700/70
                           hover:shadow-lg transition">

                    {{-- Video Frame --}}
                    <div class="relative aspect-video bg-black/5 dark:bg-black/30">
                        @if ($type === 'iframe' && $iframe)
                            {{-- Raw iframe (trusted only) --}}
                            <div class="absolute inset-0">
                                {!! $iframe !!}
                            </div>

                        @elseif ($embedUrl)
                            {{-- YouTube / Vimeo embed --}}
                            <iframe
                                src="{{ $embedUrl }}"
                                class="absolute inset-0 w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>

                        @elseif ($url)
                            {{-- Fallback link --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <a href="{{ $url }}" target="_blank"
                                   class="inline-flex items-center gap-2
                                          px-4 py-2 rounded-xl
                                          bg-accent text-white text-sm font-medium
                                          hover:opacity-90 transition">
                                    <flux:icon name="arrow-top-right-on-square" class="w-4 h-4" />
                                    {{ __('Open video') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="p-3 space-y-1">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                            {{ $title ?: __('Untitled video') }}
                        </p>

                        <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <flux:icon name="film" class="w-3.5 h-3.5" />
                            {{ ucfirst($type) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif



                        </div>
                    </div>

                    {{-- Optional footer (nice spacing on mobile) --}}
                    <div
                        class="px-4 sm:px-6 py-3 border-t border-slate-200/70 dark:border-slate-700/70 bg-slate-50/60 dark:bg-slate-950/20">
                        <button type="button" wire:click="closeViewModal" wire:loading.attr="disabled"
                            wire:target="closeViewModal"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                               px-4 py-2 rounded-xl
                               bg-slate-900 text-white hover:opacity-95
                               dark:bg-white dark:text-slate-900
                               disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="closeViewModal">{{ __('Close') }}</span>
                            <span wire:loading wire:target="closeViewModal">{{ __('Closing...') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif



    {{-- Delete Confirmation Modal --}}
    <x-modals.confirm :show="$showDeleteModal" type="danger" :title="__('Delete project')" :message="__('Are you sure you want to delete this project? This action cannot be undone.')" :confirmAction="'wire:click=confirmDelete'"
        :cancelAction="'wire:click=cancelDelete'" confirmLoadingTarget="confirmDelete" :confirmText="__('Yes, delete')" />
</div>
