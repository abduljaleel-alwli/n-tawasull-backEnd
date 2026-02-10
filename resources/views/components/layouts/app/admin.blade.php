<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.admin-head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800" {{ app()->getLocale() === 'ar' ? 'dir=rtl' : '' }}>
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        {{-- Logo --}}
        <a href="{{ route('admin.dashboard') }}"
            class="me-5 flex items-center space-x-2 rtl:space-x-reverse admin-logo-box" wire:navigate>
            <x-app-logo-icon style="width: 100%; height: auto;" />
        </a>

        {{-- Platform --}}
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="rocket-launch" :href="route('admin.dashboard')"
                    :current="request()->routeIs('admin.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        {{-- Manage --}}
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Manage')" class="grid">

                <flux:navlist.item icon="cube-transparent" :href="route('admin.services')"
                    :current="request()->routeIs('admin.services')" wire:navigate>
                    {{ __('Services') }}
                </flux:navlist.item>

                <flux:navlist.item icon="cube" :href="route('admin.projects')"
                    :current="request()->routeIs('admin.projects')" wire:navigate>
                    {{ __('Projects') }}
                </flux:navlist.item>

                <flux:navlist.item icon="tag" :href="route('admin.categories')"
                    :current="request()->routeIs('admin.categories')" wire:navigate>
                    {{ __('Categories') }}
                </flux:navlist.item>

                <flux:navlist.item icon="bell" :href="route('admin.notifications')"
                    :current="request()->routeIs('admin.notifications')" wire:navigate>

                    <div class="relative flex items-center gap-2">
                        <span>{{ __('Notifications') }}</span>

                        <x-notifications.notification-badge :count="\Illuminate\Notifications\DatabaseNotification::whereNull('read_at')->count()" />
                    </div>
                </flux:navlist.item>

                <flux:navlist.item icon="envelope" :href="route('admin.contact-messages')"
                    :current="request()->routeIs('admin.contact-messages')" wire:navigate>

                    <div class="relative flex items-center gap-2">
                        <span>{{ __('Messages') }}</span>

                        <x-notifications.notification-badge :count="\App\Models\ContactMessage::whereNull('read_at')->count()" />
                    </div>

                </flux:navlist.item>

                <flux:navlist.item icon="megaphone" :href="route('admin.newsletter')"
                    :current="request()->routeIs('admin.newsletter')" wire:navigate>
                    {{ __('Newsletter') }}
                </flux:navlist.item>

            </flux:navlist.group>
        </flux:navlist>

        {{-- Pages --}}
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Pages')" class="grid">

                <flux:navlist.item icon="fire" :href="route('admin.hero')"
                    :current="request()->routeIs('admin.hero')" wire:navigate>
                    {{ __('Hero') }}
                </flux:navlist.item>

                <flux:navlist.item icon="sparkles" :href="route('admin.features')"
                    :current="request()->routeIs('admin.features')" wire:navigate>
                    {{ __('Features') }}
                </flux:navlist.item>

                <flux:navlist.item icon="users" :href="route('admin.partners')"
                    :current="request()->routeIs('admin.partners')" wire:navigate>
                    {{ __('Partners') }}
                </flux:navlist.item>

                <flux:navlist.item icon="star" :href="route('admin.reviews')"
                    :current="request()->routeIs('admin.reviews')" wire:navigate>
                    {{ __('Reviews') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open" :href="route('admin.faqs')"
                    :current="request()->routeIs('admin.faqs')" wire:navigate>
                    {{ __('FAQs') }}
                </flux:navlist.item>

                <flux:navlist.item icon="phone" :href="route('admin.contact')"
                    :current="request()->routeIs('admin.contact')" wire:navigate>
                    {{ __('Contact') }}
                </flux:navlist.item>

            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        {{-- System --}}
        <flux:navlist variant="outline">

            @role('super-admin')
                <flux:navlist.item icon="users" :href="route('admin.users')"
                    :current="request()->routeIs('admin.users')" wire:navigate>
                    {{ __('Users') }}
                </flux:navlist.item>

                <flux:navlist.item icon="clipboard-document-list" :href="route('admin.audit-logs')"
                    :current="request()->routeIs('admin.audit-logs')" wire:navigate>
                    {{ __('System Logs') }}
                </flux:navlist.item>
            @endrole

            <flux:navlist.item icon="cog-6-tooth" :href="route('admin.settings')"
                :current="request()->routeIs('admin.settings')" wire:navigate>
                {{ __('Settings') }}
            </flux:navlist.item>

            @php
                $developerSupportCta = data_get($platformConfig, 'cta.developer_support.actions.0');
                $locale = app()->getLocale();
            @endphp

            @if ($developerSupportCta && ($developerSupportCta['enabled'] ?? false))
                <flux:navlist.item icon="chat-bubble-left-right" href="{{ $developerSupportCta['action']['url'] }}"
                    target="_blank">
                    {{ data_get($developerSupportCta, "label.$locale") ??
                        (data_get($developerSupportCta, 'label.ar') ?? __('Dev Support')) }}
                </flux:navlist.item>
            @else
                {{-- Skeleton Placeholder --}}
                <div class="flex items-center gap-2 px-3 py-2">
                    <x-skeleton.line w="w-5" h="h-5" />
                    <x-skeleton.line w="w-28" h="h-4" />
                </div>
            @endif


        </flux:navlist>

        {{-- User menu --}}
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevron-up-down" />

            <flux:menu class="w-[220px]">

                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg
                                     bg-neutral-200 dark:bg-neutral-700">
                                {{ auth()->user()->initials() }}
                            </span>

                            <div class="grid flex-1 leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
                    {{ __('Settings') }}
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>

            </flux:menu>
        </flux:dropdown>

    </flux:sidebar>


    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
    @stack('scripts')

</body>

</html>
