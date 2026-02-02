@props([
    'title' => __('Settings'),
    'description' => __('Manage your profile and account settings'),
    'icon' => 'cog-6-tooth',
])

<div
    class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90
           p-6 mb-6
           flex items-center justify-between gap-4">

    {{-- Left --}}
    <div class="min-w-0">
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white truncate">
            {{ $title }}
        </h1>

        @if($description)
            <p class="mt-1 text-sm text-slate-500">
                {{ $description }}
            </p>
        @endif
    </div>

    {{-- Right icon --}}
    <flux:icon
        name="{{ $icon }}"
        class="w-8 h-8 text-slate-400 shrink-0"/>
</div>
