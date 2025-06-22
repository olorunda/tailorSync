<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>

        <x-slot:heading>
            {{ __('Store Preview') }}
        </x-slot>
        <x-slot:subheading>
            {{ __('Preview how your online store will appear to customers') }}
        </x-slot>

        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Store Status</h2>

                        <div>
                            @if($businessDetail->store_enabled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        This is a preview of how your store will appear to customers. Any changes you make to your store settings will be reflected here.
                    </p>

                    @if(!$businessDetail->store_enabled)
                        <div class="mt-4 bg-yellow-50 dark:bg-yellow-900/10 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                        Your store is currently inactive. Enable it in the store settings to make it accessible to customers.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 flex space-x-4">
                        <a href="{{ route('settings.store') }}" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg font-medium hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Edit Store Settings
                            </span>
                        </a>

                        @if($businessDetail->store_enabled && $businessDetail->store_slug)
                            <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" target="_blank" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                                <span class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Open Live Store
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-zinc-200 dark:border-zinc-700">
                    <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-2 flex items-center justify-between">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Preview Mode</span>
                        <div class="flex space-x-2">
                            <button id="desktop-view" class="text-xs text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-700 px-2 py-1 rounded border border-zinc-300 dark:border-zinc-600 active">Desktop</button>
                            <button id="mobile-view" class="text-xs text-zinc-500 dark:text-zinc-400 hover:bg-white dark:hover:bg-zinc-700 px-2 py-1 rounded border border-transparent hover:border-zinc-300 dark:hover:border-zinc-600">Mobile</button>
                        </div>
                    </div>
                </div>

                <div class="p-1 bg-zinc-50 dark:bg-zinc-800">
                    <iframe id="store-preview" src="{{ route('storefront.index', $businessDetail->store_slug ?? 'preview') }}" class="w-full h-[800px] border-0 rounded"></iframe>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Store Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Store Name</h3>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $businessDetail->business_name }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Store URL</h3>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">
                                @if($businessDetail->store_slug)
                                    {{ url('/shop/' . $businessDetail->store_slug) }}
                                @else
                                    <span class="text-yellow-600 dark:text-yellow-500">Not set</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Theme Colors</h3>
                            <div class="flex space-x-2 mt-1">
                                <div class="h-6 w-6 rounded-full border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $businessDetail->store_theme_color ?? '#3b82f6' }}"></div>
                                <div class="h-6 w-6 rounded-full border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $businessDetail->store_secondary_color ?? '#1e40af' }}"></div>
                                <div class="h-6 w-6 rounded-full border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $businessDetail->store_accent_color ?? '#f97316' }}"></div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Display Sections</h3>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @if($businessDetail->store_show_featured_products)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        Featured Products
                                    </span>
                                @endif

                                @if($businessDetail->store_show_new_arrivals)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        New Arrivals
                                    </span>
                                @endif

                                @if($businessDetail->store_show_custom_designs)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        Custom Designs
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Store Description</h3>
                        <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $businessDetail->store_description ?? 'No description set' }}</p>
                    </div>

                    @if($businessDetail->store_announcement)
                        <div class="mt-4">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Store Announcement</h3>
                            <div class="p-2 rounded" style="background-color: {{ $businessDetail->store_accent_color ?? '#f97316' }}; color: white;">
                                {{ $businessDetail->store_announcement }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Store Statistics</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Total Products</h3>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ count($featuredProducts) + count($newArrivals) + count($customDesigns) }}</p>
                        </div>

                        <div class="bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Featured Products</h3>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ count($featuredProducts) }}</p>
                        </div>

                        <div class="bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Custom Designs</h3>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ count($customDesigns) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-settings.layout>

    <script>
        // Toggle between desktop and mobile views
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('store-preview');
            const desktopButton = document.getElementById('desktop-view');
            const mobileButton = document.getElementById('mobile-view');

            desktopButton.addEventListener('click', function() {
                desktopButton.classList.add('active', 'bg-white', 'dark:bg-zinc-700', 'border-zinc-300', 'dark:border-zinc-600');
                desktopButton.classList.remove('hover:bg-white', 'dark:hover:bg-zinc-700', 'border-transparent', 'hover:border-zinc-300', 'dark:hover:border-zinc-600');

                mobileButton.classList.remove('active', 'bg-white', 'dark:bg-zinc-700', 'border-zinc-300', 'dark:border-zinc-600');
                mobileButton.classList.add('hover:bg-white', 'dark:hover:bg-zinc-700', 'border-transparent', 'hover:border-zinc-300', 'dark:hover:border-zinc-600');

                iframe.style.width = '100%';
                iframe.style.margin = '0';
            });

            mobileButton.addEventListener('click', function() {
                mobileButton.classList.add('active', 'bg-white', 'dark:bg-zinc-700', 'border-zinc-300', 'dark:border-zinc-600');
                mobileButton.classList.remove('hover:bg-white', 'dark:hover:bg-zinc-700', 'border-transparent', 'hover:border-zinc-300', 'dark:hover:border-zinc-600');

                desktopButton.classList.remove('active', 'bg-white', 'dark:bg-zinc-700', 'border-zinc-300', 'dark:border-zinc-600');
                desktopButton.classList.add('hover:bg-white', 'dark:hover:bg-zinc-700', 'border-transparent', 'hover:border-zinc-300', 'dark:hover:border-zinc-600');

                iframe.style.width = '375px';
                iframe.style.margin = '0 auto';
            });
        });
    </script>
</section>
