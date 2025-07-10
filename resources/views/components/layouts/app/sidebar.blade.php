<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <!--Start of Tawk.to Script-->
{{--        <script type="text/javascript">--}}

{{--            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();--}}
{{--            Tawk_API.visitor = {--}}
{{--                name : "{{ auth()->user()->name }}",--}}
{{--                email : "{{ auth()->user()->email }}",--}}
{{--                hash : "{{ hash_hmac('sha256',auth()->user()->email,'d22a3437b3b93352089be49722deaaa6f8df1817266whhwh') }}"--}}
{{--            };--}}
{{--            (function(){--}}
{{--                var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];--}}
{{--                s1.async=true;--}}
{{--                s1.src='https://embed.tawk.to/686e122c6bd052190d8f071a/1ivmvdvnj';--}}
{{--                s1.charset='UTF-8';--}}
{{--                s1.setAttribute('crossorigin','*');--}}
{{--                s0.parentNode.insertBefore(s1,s0);--}}
{{--            })();--}}
{{--        </script>--}}
        <!--End of Tawk.to Script-->
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <!-- Offline indicator -->
        <div class="offline-indicator fixed top-0 left-0 w-full bg-yellow-500 text-white text-center py-2 z-50 hidden">
            <div class="container mx-auto flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                You are currently offline. Some features may be limited.
            </div>
        </div>

        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 shadow-md">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="bell" :href="route('notifications.index')" :current="request()->routeIs('notifications.*')" wire:navigate>{{ __('Notifications') }}</flux:navlist.item>
                    <flux:navlist.item icon="credit-card" :href="route('subscriptions.index')" :current="request()->routeIs('subscriptions.*')" wire:navigate>{{ __('Subscription') }}</flux:navlist.item>
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

                @if(auth()->user()->hasPermission('view_invoices') || auth()->user()->hasPermission('view_payments') || auth()->user()->hasPermission('view_expenses') || auth()->user()->hasPermission('view_tax_reports'))
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
                        @if(auth()->user()->hasPermission('view_tax_reports'))
                            <flux:navlist.item icon="chart-bar" :href="route('reports.tax')" :current="request()->routeIs('reports.tax')" wire:navigate>{{ __('Tax Report') }}</flux:navlist.item>
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

                @if(auth()->user()->hasPermission('view_store_products') || auth()->user()->hasPermission('view_store_orders') || auth()->user()->hasPermission('view_store_purchases'))
                    <flux:navlist.group :heading="__('Store Management')" class="grid">
                        @if(auth()->user()->hasPermission('view_store_products'))
                            <flux:navlist.item icon="shopping-bag" :href="route('store.products.index')" :current="request()->routeIs('store.products.*')" wire:navigate>{{ __('Products') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_store_orders'))
                            <flux:navlist.item icon="shopping-cart" :href="route('store.orders.index')" :current="request()->routeIs('store.orders.*')" wire:navigate>{{ __('Store Orders') }}</flux:navlist.item>
                        @endif
                        @if(auth()->user()->hasPermission('view_store_purchases'))
                            <flux:navlist.item icon="credit-card" :href="route('store.purchases.index')" :current="request()->routeIs('store.purchases.*')" wire:navigate>{{ __('Purchases') }}</flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

{{--            <flux:navlist variant="outline">--}}
{{--                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">--}}
{{--                {{ __('Repository') }}--}}
{{--                </flux:navlist.item>--}}

{{--                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">--}}
{{--                {{ __('Documentation') }}--}}
{{--                </flux:navlist.item>--}}
{{--            </flux:navlist>--}}

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
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo-icon />
                <span class="text-lg font-semibold">{{ config('app.name', 'TailorFit') }}</span>
            </a>

            <flux:spacer />

            <div class="flex items-center gap-2 mr-2">
                @livewire('notifications.dropdown')
            </div>

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

        <!-- Breadcrumbs -->
        <div
            class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-4 py-2 sm:px-6 lg:px-8 hidden lg:block shadow-sm">
            <flux:breadcrumbs class="text-sm">
                <flux:breadcrumbs.item href="{{ route('dashboard') }}" class="hover:text-primary-500 transition-colors duration-200">{{ __('Dashboard') }}</flux:breadcrumbs.item>
                @if(request()->segment(1) && request()->segment(1) !== 'dashboard')
                    <flux:breadcrumbs.item
                        href="{{ url(request()->segment(1)) }}" class="hover:text-primary-500 transition-colors duration-200">{{ __(ucfirst(request()->segment(1))) }}</flux:breadcrumbs.item>
                @endif
                @if(request()->segment(2))
                    <flux:breadcrumbs.item>{{ __(ucfirst(request()->segment(2))) }}</flux:breadcrumbs.item>
                @endif
            </flux:breadcrumbs>
        </div>

        <div>
            <x-subscription-cta />
        </div>
            {{ $slot }}


        <!-- Mobile Bottom Navigation -->
        <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-zinc-200 dark:bg-zinc-900 dark:border-zinc-700 shadow-lg backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90 lg:hidden">
            <div class="grid h-full grid-cols-5 mx-auto">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('dashboard') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }} hover:text-primary-500 active:scale-95 transition-all duration-200" wire:navigate>
                    <flux:icon name="home" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ __('Home') }}</span>
                </a>

                <a href="{{ route('notifications.index') }}" class="flex flex-col items-center justify-center relative {{ request()->routeIs('notifications.*') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }} hover:text-primary-500 active:scale-95 transition-all duration-200" wire:navigate>
                    <div class="relative">
                        <flux:icon name="bell" class="w-6 h-6" />
                        @livewire('notifications.dropdown', ['showDropdown' => false])
                    </div>
                    <span class="text-xs mt-1">{{ __('Notifications') }}</span>
                </a>

                @if(auth()->user()->hasPermission('view_clients'))
                <a href="{{ route('clients.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('clients.*') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }} hover:text-primary-500 active:scale-95 transition-all duration-200" wire:navigate>
                    <flux:icon name="users" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ __('Clients') }}</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_orders'))
                <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('orders.*') ? 'text-primary-600 dark:text-primary-500 font-medium' : 'text-zinc-500 dark:text-zinc-400' }} hover:text-primary-500 active:scale-95 transition-all duration-200" wire:navigate>
                    <flux:icon name="clipboard-document-list" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ __('Orders') }}</span>
                </a>
                @endif

                <flux:dropdown position="top" align="end" class="flex flex-col items-center justify-center">
                    <button type="button" class="flex flex-col items-center justify-center w-full h-full text-zinc-500 dark:text-zinc-400 hover:text-primary-500 active:scale-95 transition-all duration-200">
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

                        @if(auth()->user()->hasPermission('view_store_products'))
                        <flux:menu.item :href="route('store.products.index')" icon="shopping-bag" wire:navigate>{{ __('Store Products') }}</flux:menu.item>
                        @endif

                        @if(auth()->user()->hasPermission('view_store_orders'))
                        <flux:menu.item :href="route('store.orders.index')" icon="shopping-cart" wire:navigate>{{ __('Store Orders') }}</flux:menu.item>
                        @endif

                        @if(auth()->user()->hasPermission('view_store_purchases'))
                        <flux:menu.item :href="route('store.purchases.index')" icon="credit-card" wire:navigate>{{ __('Store Purchases') }}</flux:menu.item>
                        @endif

                        @if(auth()->user()->hasPermission('view_tax_reports'))
                        <flux:menu.item :href="route('reports.tax')" icon="chart-bar" wire:navigate>{{ __('Tax Report') }}</flux:menu.item>
                        @endif

                        <flux:menu.item :href="route('subscriptions.index')" icon="credit-card" wire:navigate>{{ __('Subscription') }}</flux:menu.item>

                        @if(auth()->user()->hasPermission('view_profile') || auth()->user()->hasPermission('change_password') || auth()->user()->hasPermission('manage_appearance') || auth()->user()->hasPermission('manage_roles_permissions'))
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        @endif
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        @fluxScripts

        <!-- System-wide Modal for Session Messages -->
        <div x-data="{
            show: {{ session('error') || session('status') || session('info') ? 'true' : 'false' }},
            type: '{{ session('error') ? 'error' : (session('status') ? 'status' : (session('info') ? 'info' : '')) }}',
            message: '{{ session('error') ?? session('status') ?? session('info') ?? '' }}'
        }">
            <x-modal name="system-message-modal" :show="session('error') || session('status') || session('info') ? true : false">
                <div class="p-0 max-h-[80vh] overflow-y-auto w-full max-w-md mx-auto">
                    <div class="relative p-6 rounded-lg"
                        :class="{
                            'bg-red-50/80 dark:bg-red-900/80': type === 'error',
                            'bg-green-50/80 dark:bg-green-900/80': type === 'status',
                            'bg-blue-50/80 dark:bg-blue-900/80': type === 'info'
                        }">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 p-2 rounded-full"
                                    :class="{
                                        'bg-red-100 dark:bg-red-800': type === 'error',
                                        'bg-green-100 dark:bg-green-800': type === 'status',
                                        'bg-blue-100 dark:bg-blue-800': type === 'info'
                                    }">
                                    <svg x-show="type === 'error'" class="w-6 h-6 text-red-600 dark:text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <svg x-show="type === 'status'" class="w-6 h-6 text-green-600 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg x-show="type === 'info'" class="w-6 h-6 text-blue-600 dark:text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium"
                                        :class="{
                                            'text-red-800 dark:text-red-200': type === 'error',
                                            'text-green-800 dark:text-green-200': type === 'status',
                                            'text-blue-800 dark:text-blue-200': type === 'info'
                                        }">
                                        <span x-show="type === 'error'">Error</span>
                                        <span x-show="type === 'status'">Success</span>
                                        <span x-show="type === 'info'">Information</span>
                                    </h3>
                                    <div class="mt-2 text-sm"
                                        :class="{
                                            'text-red-700 dark:text-red-300': type === 'error',
                                            'text-green-700 dark:text-green-300': type === 'status',
                                            'text-blue-700 dark:text-blue-300': type === 'info'
                                        }">
                                        <p x-text="message"></p>
                                    </div>
                                </div>
                            </div>
                            <button x-on:click="$dispatch('close')" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-4 text-right">
                            <button x-on:click="$dispatch('close')" class="px-4 py-2 text-sm font-medium rounded-md"
                                :class="{
                                    'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-800 dark:text-red-100 dark:hover:bg-red-700': type === 'error',
                                    'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-800 dark:text-green-100 dark:hover:bg-green-700': type === 'status',
                                    'bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700': type === 'info'
                                }">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </x-modal>
        </div>

    </body>
</html>
