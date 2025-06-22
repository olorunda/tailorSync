<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $businessDetail->business_name }} - {{ $title ?? 'Fashion Store' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Theme Colors -->
    <style>
        :root {
            --primary-color: {{ $businessDetail->store_theme_color ?? '#3b82f6' }};
            --secondary-color: {{ $businessDetail->store_secondary_color ?? '#1e40af' }};
            --accent-color: {{ $businessDetail->store_accent_color ?? '#f97316' }};
        }

        .bg-primary-custom {
            background-color: var(--primary-color);
        }

        .bg-secondary-custom {
            background-color: var(--secondary-color);
        }

        .bg-accent-custom {
            background-color: var(--accent-color);
        }

        .text-primary-custom {
            color: var(--primary-color);
        }

        .text-secondary-custom {
            color: var(--secondary-color);
        }

        .text-accent-custom {
            color: var(--accent-color);
        }

        .border-primary-custom {
            border-color: var(--primary-color);
        }

        .border-secondary-custom {
            border-color: var(--secondary-color);
        }

        .border-accent-custom {
            border-color: var(--accent-color);
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary-custom:hover {
            background-color: var(--secondary-color);
        }

        .btn-accent-custom {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-accent-custom:hover {
            filter: brightness(90%);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-secondary-custom text-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            @if($businessDetail->logo_path)
                                <a href="{{ route('storefront.index', $businessDetail->store_slug) }}">
                                    <img class="h-10 w-auto" src="{{ Storage::url($businessDetail->logo_path) }}" alt="{{ $businessDetail->business_name }}">
                                </a>
                            @else
                                <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" class="text-xl font-bold">
                                    {{ $businessDetail->business_name }}
                                </a>
                            @endif
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('storefront.index') ? 'border-accent-custom text-white' : 'border-transparent text-gray-200 hover:border-gray-300 hover:text-white' }} text-sm font-medium">
                                Home
                            </a>
                            <a href="{{ route('storefront.products', $businessDetail->store_slug) }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('storefront.products') ? 'border-accent-custom text-white' : 'border-transparent text-gray-200 hover:border-gray-300 hover:text-white' }} text-sm font-medium">
                                Products
                            </a>
                            <a href="{{ route('storefront.products', [$businessDetail->store_slug, 'custom_order' => true]) }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->has('custom_order') ? 'border-accent-custom text-white' : 'border-transparent text-gray-200 hover:border-gray-300 hover:text-white' }} text-sm font-medium">
                                Custom Designs
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <a href="{{ route('storefront.cart', $businessDetail->store_slug) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-accent-custom hover:bg-opacity-75 focus:outline-none transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Cart ({{ $cart->getItemCount() }})
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="sm:hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" class="block pl-3 pr-4 py-2 {{ request()->routeIs('storefront.index') ? 'border-l-4 border-accent-custom bg-secondary-custom bg-opacity-75 text-white' : 'border-l-4 border-transparent text-gray-200 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} text-base font-medium">
                        Home
                    </a>
                    <a href="{{ route('storefront.products', $businessDetail->store_slug) }}" class="block pl-3 pr-4 py-2 {{ request()->routeIs('storefront.products') && !request()->has('custom_order') ? 'border-l-4 border-accent-custom bg-secondary-custom bg-opacity-75 text-white' : 'border-l-4 border-transparent text-gray-200 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} text-base font-medium">
                        Products
                    </a>
                    <a href="{{ route('storefront.products', [$businessDetail->store_slug, 'custom_order' => true]) }}" class="block pl-3 pr-4 py-2 {{ request()->has('custom_order') ? 'border-l-4 border-accent-custom bg-secondary-custom bg-opacity-75 text-white' : 'border-l-4 border-transparent text-gray-200 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} text-base font-medium">
                        Custom Designs
                    </a>
                </div>
            </div>
        </nav>

        <!-- Announcement Bar (if set) -->
        @if($businessDetail->store_announcement)
            <div class="bg-accent-custom text-white text-center py-2 px-4">
                {{ $businessDetail->store_announcement }}
            </div>
        @endif

        <!-- Page Content -->
        <main>
            @if(session('success'))
                <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-secondary-custom text-white">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">{{ $businessDetail->business_name }}</h3>
                        <p class="text-sm text-gray-300">{{ $businessDetail->store_description ?? $businessDetail->business_address }}</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                        <p class="text-sm text-gray-300">{{ $businessDetail->business_address }}</p>
                        <p class="text-sm text-gray-300">{{ $businessDetail->business_phone }}</p>
                        <p class="text-sm text-gray-300">{{ $businessDetail->business_email }}</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            @if($businessDetail->facebook_handle)
                                <a href="{{ $businessDetail->facebook_handle }}" target="_blank" class="text-gray-300 hover:text-white">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                            @if($businessDetail->instagram_handle)
                                <a href="{{ $businessDetail->instagram_handle }}" target="_blank" class="text-gray-300 hover:text-white">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                            @if($businessDetail->tiktok_handle)
                                <a href="{{ $businessDetail->tiktok_handle }}" target="_blank" class="text-gray-300 hover:text-white">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($businessDetail->whatsapp_handle)
                                <a href="https://wa.me/{{ $businessDetail->whatsapp_handle }}" target="_blank" class="text-gray-300 hover:text-white">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-700 pt-4 text-center text-sm text-gray-400">
                    &copy; {{ date('Y') }} {{ $businessDetail->business_name }}. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
