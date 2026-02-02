@if (request()->is('admin/*') || request()->is('settings/*'))
    <x-layouts.app.admin :title="$title ?? null">
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.admin>
@else
    <x-layouts.app.app :title="$title ?? null">
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.app>
@endif
<x-toast />
