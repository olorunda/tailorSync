<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>

                @if(auth()->user()->hasPermission('view_clients') || auth()->user()->hasPermission('view_orders') || auth()->user()->hasPermission('view_appointments') || auth()->user()->hasPermission('view_messages'))
                    <flux:navlist.group :heading="__('Client Management')" class="grid">
                        @if(auth()->user()->hasPermission('view_clients'))
                            <flux:navlist.item icon="users" :href="route('clients.index')" :current="request()->routeIs('clients.*')" wire:navigate>{{ __('Clients') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_orders'))
                            <flux:navlist.item icon="clipboard-document-list" :href="route('orders.index')" :current="request()->routeIs('orders.*')" wire:navigate>{{ __('Orders') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_appointments'))
                            <flux:navlist.item icon="calendar" :href="route('appointments.index')" :current="request()->routeIs('appointments.*')" wire:navigate>{{ __('Appointments') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_messages'))
                            <flux:navlist.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>{{ __('Messages') }}</flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                @endif

                @if(auth()->user()->hasPermission('view_designs') || auth()->user()->hasPermission('view_inventory'))
                    <flux:navlist.group :heading="__('Design & Inventory')" class="grid">
                        @if(auth()->user()->hasPermission('view_designs'))
                            <flux:navlist.item icon="squares-plus" :href="route('designs.index')" :current="request()->routeIs('designs.*')" wire:navigate>{{ __('Design Board') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_inventory'))
                            <flux:navlist.item icon="cube" :href="route('inventory.index')" :current="request()->routeIs('inventory.*')" wire:navigate>{{ __('Inventory') }}</flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                @endif

                @if(auth()->user()->hasPermission('view_invoices') || auth()->user()->hasPermission('view_payments') || auth()->user()->hasPermission('view_expenses'))
                    <flux:navlist.group :heading="__('Finance')" class="grid">
                        @if(auth()->user()->hasPermission('view_invoices'))
                            <flux:navlist.item icon="document-text" :href="route('invoices.index')" :current="request()->routeIs('invoices.*')" wire:navigate>{{ __('Invoices') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_payments'))
                            <flux:navlist.item icon="banknotes" :href="route('payments.index')" :current="request()->routeIs('payments.*')" wire:navigate>{{ __('Payments') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_expenses'))
                            <flux:navlist.item icon="receipt-percent" :href="route('expenses.index')" :current="request()->routeIs('expenses.*')" wire:navigate>{{ __('Expenses') }}</flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                @endif

                @if(auth()->user()->hasPermission('view_team') || auth()->user()->hasPermission('view_tasks'))
                    <flux:navlist.group :heading="__('Team')" class="grid">
                        @if(auth()->user()->hasPermission('view_team'))
                            <flux:navlist.item icon="user-group" :href="route('team.index')" :current="request()->routeIs('team.*')" wire:navigate>{{ __('Team Members') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_tasks'))
                            <flux:navlist.item icon="clipboard-document-check" :href="route('tasks.index')" :current="request()->routeIs('tasks.*')" wire:navigate>{{ __('Tasks') }}</flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
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
                        @if(auth()->user()->hasPermission('view_profile') || auth()->user()->hasPermission('change_password') || auth()->user()->hasPermission('manage_appearance') || auth()->user()->hasPermission('manage_roles_permissions'))
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        @endif
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo-icon />
                <span class="text-lg font-semibold">{{ config('app.name', 'TailorFit') }}</span>
            </a>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
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

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

            {{ $slot }}


        <!-- Mobile Bottom Navigation -->
        <div  class=" fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-zinc-200 dark:bg-zinc-900 dark:border-zinc-700 lg:hidden">
            <div class="grid h-full grid-cols-5 mx-auto">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('dashboard') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }}" wire:navigate>
                    <flux:icon name="home" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ __('Home') }}</span>
                </a>

                @if(auth()->user()->hasPermission('view_clients'))
                <a href="{{ route('clients.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('clients.*') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }}" wire:navigate>
                    <flux:icon name="users" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ __('Clients') }}</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_orders'))
                <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('orders.*') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }}" wire:navigate>
                    <flux:icon name="clipboard-document-list" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ __('Orders') }}</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_appointments'))
                <a href="{{ route('appointments.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('appointments.*') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }}" wire:navigate>
                    <flux:icon name="calendar" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ __('Calendar') }}</span>
                </a>
                @endif

                <flux:dropdown position="top" align="end" class="flex flex-col items-center justify-center">
                    <button type="button" class="flex flex-col items-center justify-center w-full h-full text-zinc-500 dark:text-zinc-400">
                        <flux:icon name="ellipsis-horizontal" class="w-6 h-6" />
                        <span class="text-xs mt-1">{{ __('More') }}</span>
                    </button>

                    <flux:menu class="mb-2 w-48">
                        @if(auth()->user()->hasPermission('view_invoices'))
                        <flux:menu.item :href="route('invoices.index')" icon="document-text" wire:navigate>{{ __('Invoices') }}</flux:menu.item>
                        @endif

                        @if(auth()->user()->hasPermission('view_inventory'))
                        <flux:menu.item :href="route('inventory.index')" icon="cube" wire:navigate>{{ __('Inventory') }}</flux:menu.item>
                        @endif

                        @if(auth()->user()->hasPermission('view_team'))
                        <flux:menu.item :href="route('team.index')" icon="user-group" wire:navigate>{{ __('Team') }}</flux:menu.item>
                        @endif

                        @if(auth()->user()->hasPermission('view_profile') || auth()->user()->hasPermission('change_password') || auth()->user()->hasPermission('manage_appearance') || auth()->user()->hasPermission('manage_roles_permissions'))
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        @endif
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        @fluxScripts

    </body>
</html>
