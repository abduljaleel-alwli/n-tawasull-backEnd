 <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
    <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Manage')" class="grid">
                <flux:navlist.item icon="home" :href="route('admin.products')"
                    :current="request()->routeIs('admin.products')" wire:navigate>{{ __('Products') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('admin.categories')"
                    :current="request()->routeIs('admin.categories')" wire:navigate>{{ __('Categories') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('admin.users')" :current="request()->routeIs('admin.users')"
                    wire:navigate>{{ __('Users') }}</flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('admin.notifications')"
                    :current="request()->routeIs('admin.notifications')" wire:navigate>{{ __('Notifications') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('admin.contact-messages')"
                    :current="request()->routeIs('admin.contact-messages')" wire:navigate>{{ __('Messages') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('admin.audit-logs')"
                    :current="request()->routeIs('admin.audit-logs')" wire:navigate>{{ __('System Logs') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
 </header>
