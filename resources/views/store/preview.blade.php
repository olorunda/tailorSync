<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Store Preview') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('store.settings') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Back to Settings
                </a>

                @if($businessDetail->store_enabled && $businessDetail->store_slug)
                    <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Open Live Store
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Store Status</h3>

                        <div>
                            @if($businessDetail->store_enabled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        This is a preview of how your store will appear to customers. Any changes you make to your store settings will be reflected here.
                    </p>

                    @if(!$businessDetail->store_enabled)
                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Your store is currently inactive. Enable it in the store settings to make it accessible to customers.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <div class="bg-gray-100 px-4 py-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Preview Mode</span>
                        <div class="flex space-x-2">
                            <button class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-300 active">Desktop</button>
                            <button class="text-xs text-gray-500 hover:bg-white px-2 py-1 rounded border border-transparent hover:border-gray-300">Mobile</button>
                        </div>
                    </div>
                </div>

                <div class="p-1 bg-gray-100">
                    <iframe id="store-preview" src="{{ route('storefront.index', $businessDetail->store_slug ?? 'preview') }}" class="w-full h-[800px] border-0 rounded"></iframe>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Store Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Store Name</h4>
                            <p class="text-base text-gray-900">{{ $businessDetail->business_name }}</p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Store URL</h4>
                            <p class="text-base text-gray-900">
                                @if($businessDetail->store_slug)
                                    {{ url('/shop/' . $businessDetail->store_slug) }}
                                @else
                                    <span class="text-yellow-600">Not set</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Theme Colors</h4>
                            <div class="flex space-x-2 mt-1">
                                <div class="h-6 w-6 rounded-full border border-gray-300" style="background-color: {{ $businessDetail->store_theme_color ?? '#3b82f6' }}"></div>
                                <div class="h-6 w-6 rounded-full border border-gray-300" style="background-color: {{ $businessDetail->store_secondary_color ?? '#1e40af' }}"></div>
                                <div class="h-6 w-6 rounded-full border border-gray-300" style="background-color: {{ $businessDetail->store_accent_color ?? '#f97316' }}"></div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Display Sections</h4>
                            <div class="flex space-x-4 mt-1">
                                @if($businessDetail->store_show_featured_products)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Featured Products
                                    </span>
                                @endif

                                @if($businessDetail->store_show_new_arrivals)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        New Arrivals
                                    </span>
                                @endif

                                @if($businessDetail->store_show_custom_designs)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Custom Designs
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Store Description</h4>
                        <p class="text-base text-gray-900">{{ $businessDetail->store_description ?? 'No description set' }}</p>
                    </div>

                    @if($businessDetail->store_announcement)
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Store Announcement</h4>
                            <div class="bg-accent-custom text-white p-2 rounded">
                                {{ $businessDetail->store_announcement }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Store Statistics</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Total Products</h4>
                            <p class="text-2xl font-bold text-gray-900">{{ $featuredProducts->count() + $newArrivals->count() + $customDesigns->count() }}</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Featured Products</h4>
                            <p class="text-2xl font-bold text-gray-900">{{ $featuredProducts->count() }}</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Custom Designs</h4>
                            <p class="text-2xl font-bold text-gray-900">{{ $customDesigns->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom theme colors for preview */
        .bg-accent-custom {
            background-color: {{ $businessDetail->store_accent_color ?? '#f97316' }};
        }
    </style>

    <script>
        // Toggle between desktop and mobile views
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('store-preview');
            const viewButtons = document.querySelectorAll('.bg-gray-100 button');

            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    viewButtons.forEach(btn => {
                        btn.classList.remove('active', 'bg-white', 'border-gray-300');
                        btn.classList.add('hover:bg-white', 'border-transparent', 'hover:border-gray-300');
                    });

                    // Add active class to clicked button
                    this.classList.add('active', 'bg-white', 'border-gray-300');
                    this.classList.remove('hover:bg-white', 'border-transparent', 'hover:border-gray-300');

                    // Set iframe width based on view
                    if (this.textContent.trim() === 'Mobile') {
                        iframe.style.width = '375px';
                        iframe.style.margin = '0 auto';
                    } else {
                        iframe.style.width = '100%';
                        iframe.style.margin = '0';
                    }
                });
            });
        });
    </script>
</x-app-layout>
