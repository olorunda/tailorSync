<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Store Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('store.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <div class="flex items-center">
                                <input id="store_enabled" name="store_enabled" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ $businessDetail->store_enabled ? 'checked' : '' }}>
                                <label for="store_enabled" class="ml-2 block text-sm text-gray-900">
                                    Enable Online Store
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                When enabled, your online store will be accessible to customers.
                            </p>
                        </div>

                        <div class="mb-6">
                            <label for="store_slug" class="block text-sm font-medium text-gray-700">Store URL</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    {{ url('/shop/') }}/
                                </span>
                                <input type="text" name="store_slug" id="store_slug" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" placeholder="your-store-name" value="{{ $businessDetail->store_slug }}">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                This will be the URL of your online store. Use only letters, numbers, and hyphens.
                            </p>
                            @error('store_slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="store_description" class="block text-sm font-medium text-gray-700">Store Description</label>
                            <textarea id="store_description" name="store_description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $businessDetail->store_description }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">
                                A brief description of your store that will appear on the homepage.
                            </p>
                            @error('store_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="store_announcement" class="block text-sm font-medium text-gray-700">Store Announcement</label>
                            <input type="text" name="store_announcement" id="store_announcement" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $businessDetail->store_announcement }}">
                            <p class="mt-1 text-sm text-gray-500">
                                A short announcement that will appear at the top of your store (e.g., "Free shipping on orders over $50").
                            </p>
                            @error('store_announcement')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="store_banner_image" class="block text-sm font-medium text-gray-700">Store Banner Image</label>
                            <div class="mt-1 flex items-center">
                                @if($businessDetail->store_banner_image)
                                    <div class="mr-4">
                                        <img src="{{ Storage::url($businessDetail->store_banner_image) }}" alt="Store Banner" class="h-32 w-auto object-cover rounded-md">
                                    </div>
                                @endif
                                <input type="file" name="store_banner_image" id="store_banner_image" class="focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                This image will be displayed as the banner on your store homepage. Recommended size: 1920x600 pixels.
                            </p>
                            @error('store_banner_image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Store Theme Colors</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="store_theme_color" class="block text-sm font-medium text-gray-700">Primary Color</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" name="store_theme_color" id="store_theme_color" class="h-8 w-8 rounded-md border border-gray-300 mr-2" value="{{ $businessDetail->store_theme_color ?? '#3b82f6' }}">
                                        <input type="text" name="store_theme_color_text" id="store_theme_color_text" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $businessDetail->store_theme_color ?? '#3b82f6' }}">
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        The main color of your store.
                                    </p>
                                </div>

                                <div>
                                    <label for="store_secondary_color" class="block text-sm font-medium text-gray-700">Secondary Color</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" name="store_secondary_color" id="store_secondary_color" class="h-8 w-8 rounded-md border border-gray-300 mr-2" value="{{ $businessDetail->store_secondary_color ?? '#1e40af' }}">
                                        <input type="text" name="store_secondary_color_text" id="store_secondary_color_text" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $businessDetail->store_secondary_color ?? '#1e40af' }}">
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Used for headers and navigation.
                                    </p>
                                </div>

                                <div>
                                    <label for="store_accent_color" class="block text-sm font-medium text-gray-700">Accent Color</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" name="store_accent_color" id="store_accent_color" class="h-8 w-8 rounded-md border border-gray-300 mr-2" value="{{ $businessDetail->store_accent_color ?? '#f97316' }}">
                                        <input type="text" name="store_accent_color_text" id="store_accent_color_text" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $businessDetail->store_accent_color ?? '#f97316' }}">
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Used for buttons and highlights.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Store Display Options</h3>

                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="store_show_featured_products" name="store_show_featured_products" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ $businessDetail->store_show_featured_products ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="store_show_featured_products" class="font-medium text-gray-700">Show Featured Products</label>
                                        <p class="text-gray-500">Display featured products section on the homepage.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="store_show_new_arrivals" name="store_show_new_arrivals" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ $businessDetail->store_show_new_arrivals ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="store_show_new_arrivals" class="font-medium text-gray-700">Show New Arrivals</label>
                                        <p class="text-gray-500">Display new arrivals section on the homepage.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="store_show_custom_designs" name="store_show_custom_designs" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ $businessDetail->store_show_custom_designs ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="store_show_custom_designs" class="font-medium text-gray-700">Show Custom Designs</label>
                                        <p class="text-gray-500">Display custom designs section on the homepage.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Settings
                            </button>

                            <a href="{{ route('store.preview') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Preview Store
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Store URL</h3>

                    @if($businessDetail->store_enabled && $businessDetail->store_slug)
                        <div class="bg-gray-50 p-4 rounded-md">
                            <p class="text-sm text-gray-600 mb-2">Your store is currently <span class="font-medium text-green-600">active</span> and available at:</p>
                            <div class="flex items-center">
                                <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ url('/shop/' . $businessDetail->store_slug) }}
                                </a>
                                <button type="button" onclick="copyToClipboard('{{ url('/shop/' . $businessDetail->store_slug) }}')" class="ml-2 inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-md">
                            <p class="text-sm text-yellow-800">
                                Your store is currently <span class="font-medium">inactive</span>. Enable it and set a store URL to make it accessible to customers.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sync color inputs
        document.addEventListener('DOMContentLoaded', function() {
            const colorInputs = [
                { color: 'store_theme_color', text: 'store_theme_color_text' },
                { color: 'store_secondary_color', text: 'store_secondary_color_text' },
                { color: 'store_accent_color', text: 'store_accent_color_text' }
            ];

            colorInputs.forEach(pair => {
                const colorInput = document.getElementById(pair.color);
                const textInput = document.getElementById(pair.text);

                colorInput.addEventListener('input', function() {
                    textInput.value = this.value;
                });

                textInput.addEventListener('input', function() {
                    colorInput.value = this.value;
                });
            });
        });

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Store URL copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</x-app-layout>
