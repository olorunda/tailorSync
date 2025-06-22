<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>

        <x-slot:heading>
            {{ __('Store Settings') }}
        </x-slot>
        <x-slot:subheading>
            {{ __('Configure your online store appearance and settings') }}
        </x-slot>

        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Store Configuration</h2>

                    <form wire:submit.prevent="updateStoreSettings" class="space-y-4">
                        @if($successMessage)
                            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-lg">
                                {{ $successMessage }}
                            </div>
                        @endif

                        <div class="flex items-center">
                            <label for="storeEnabled" class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" id="storeEnabled" wire:model.live="storeEnabled" class="sr-only">
                                    <div class="block bg-zinc-300 dark:bg-zinc-600 w-14 h-8 rounded-full"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-all duration-300 ease-in-out transform {{ $storeEnabled ? 'translate-x-6' : '' }}"></div>
                                </div>
                                <div class="ml-3 text-zinc-700 dark:text-zinc-300 font-medium">
                                    Enable Online Store
                                </div>
                            </label>
                        </div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            When enabled, your online store will be accessible to customers.
                        </p>

                        <div>
                            <label for="storeSlug" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Store URL</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 text-sm">
                                    {{ url('/shop/') }}/
                                </span>
                                <input type="text" id="storeSlug" wire:model="storeSlug" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-orange-500 focus:border-orange-500 border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            </div>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                This will be the URL of your online store. Use only letters, numbers, and hyphens.
                            </p>
                            @error('storeSlug') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="storeDescription" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Store Description</label>
                            <textarea id="storeDescription" wire:model="storeDescription" rows="3" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white"></textarea>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                A brief description of your store that will appear on the homepage.
                            </p>
                            @error('storeDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="storeAnnouncement" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Store Announcement</label>
                            <input type="text" id="storeAnnouncement" wire:model="storeAnnouncement" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                A short announcement that will appear at the top of your store (e.g., "Free shipping on orders over $50").
                            </p>
                            @error('storeAnnouncement') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Store Banner Image</label>

                            <div class="mt-2 flex items-center">
                                @if($businessDetail->store_banner_image)
                                    <div class="mr-4">
                                        <img src="{{ Storage::url($businessDetail->store_banner_image) }}" alt="Store Banner" class="h-32 w-auto object-cover rounded-md">
                                    </div>
                                @endif

                                <label for="newBannerImage" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg font-medium hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors cursor-pointer">
                                    {{ $businessDetail->store_banner_image ? 'Change Banner' : 'Upload Banner' }}
                                </label>
                                <input type="file" id="newBannerImage" wire:model="newBannerImage" class="hidden">
                            </div>

                            @error('newBannerImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            @if($newBannerImage)
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Preview:</p>
                                    <img src="{{ $newBannerImage->temporaryUrl() }}" alt="New Banner Preview" class="mt-1 h-32 w-auto object-cover rounded-md">
                                </div>
                            @endif
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Store Theme Colors</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="storeThemeColor" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Primary Color</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" id="storeThemeColor" wire:model="storeThemeColor" class="h-8 w-8 rounded-md border border-zinc-300 dark:border-zinc-600 mr-2">
                                        <input type="text" wire:model="storeThemeColor" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        The main color of your store.
                                    </p>
                                </div>

                                <div>
                                    <label for="storeSecondaryColor" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Secondary Color</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" id="storeSecondaryColor" wire:model="storeSecondaryColor" class="h-8 w-8 rounded-md border border-zinc-300 dark:border-zinc-600 mr-2">
                                        <input type="text" wire:model="storeSecondaryColor" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        Used for headers and navigation.
                                    </p>
                                </div>

                                <div>
                                    <label for="storeAccentColor" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Accent Color</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" id="storeAccentColor" wire:model="storeAccentColor" class="h-8 w-8 rounded-md border border-zinc-300 dark:border-zinc-600 mr-2">
                                        <input type="text" wire:model="storeAccentColor" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        Used for buttons and highlights.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Store Display Options</h3>

                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="storeShowFeaturedProducts" wire:model="storeShowFeaturedProducts" type="checkbox" class="focus:ring-orange-500 h-4 w-4 text-orange-600 border-zinc-300 dark:border-zinc-600 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="storeShowFeaturedProducts" class="font-medium text-zinc-700 dark:text-zinc-300">Show Featured Products</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Display featured products section on the homepage.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="storeShowNewArrivals" wire:model="storeShowNewArrivals" type="checkbox" class="focus:ring-orange-500 h-4 w-4 text-orange-600 border-zinc-300 dark:border-zinc-600 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="storeShowNewArrivals" class="font-medium text-zinc-700 dark:text-zinc-300">Show New Arrivals</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Display new arrivals section on the homepage.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="storeShowCustomDesigns" wire:model="storeShowCustomDesigns" type="checkbox" class="focus:ring-orange-500 h-4 w-4 text-orange-600 border-zinc-300 dark:border-zinc-600 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="storeShowCustomDesigns" class="font-medium text-zinc-700 dark:text-zinc-300">Show Custom Designs</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Display custom designs section on the homepage.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between pt-4">
                            <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                                Save Settings
                            </button>

                            <a href="{{ route('settings.store.preview') }}" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg font-medium hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                Preview Store
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($businessDetail->store_enabled && $businessDetail->store_slug)
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Your Store URL</h2>
                        <div class="bg-zinc-50 dark:bg-zinc-700/50 p-4 rounded-md">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">Your store is currently <span class="font-medium text-green-600 dark:text-green-400">active</span> and available at:</p>
                            <div class="flex items-center">
                                <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" target="_blank" class="text-orange-600 hover:text-orange-700 dark:text-orange-500 dark:hover:text-orange-400 font-medium">
                                    {{ url('/shop/' . $businessDetail->store_slug) }}
                                </a>
                                <button type="button" onclick="copyToClipboard('{{ url('/shop/' . $businessDetail->store_slug) }}')" class="ml-2 inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Store Management</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @if(auth()->user()->hasPermission('view_store_products'))
                            <a href="{{ route('store.products.index') }}" class="flex flex-col items-center p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">Manage Products</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Add, edit, and remove store products</span>
                            </a>
                            @endif

                            @if(auth()->user()->hasPermission('view_store_orders'))
                            <a href="{{ route('store.orders.index') }}" class="flex flex-col items-center p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">Manage Orders</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">View and process customer orders</span>
                            </a>
                            @endif

                            @if(auth()->user()->hasPermission('view_store_purchases'))
                            <a href="{{ route('store.purchases.index') }}" class="flex flex-col items-center p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">Manage Purchases</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Track customer purchases and payments</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-settings.layout>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Store URL copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</section>
