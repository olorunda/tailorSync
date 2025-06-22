<x-storefront-layout :businessDetail="$businessDetail" :cart="$cart" title="Home">
    <!-- Hero Section with Animation -->
    <div class="relative overflow-hidden">
        @if($businessDetail->store_banner_image)
            <div class="h-[70vh] md:h-[80vh] w-full bg-cover bg-center relative" style="background-image: url('{{ Storage::url($businessDetail->store_banner_image) }}')">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/40 flex items-center justify-center">
                    <div class="text-center text-white px-4 max-w-3xl mx-auto animate-fade-in-up">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4 leading-tight">{{ $businessDetail->business_name }}</h1>
                        <p class="text-xl md:text-2xl mb-8 opacity-90">{{ $businessDetail->store_description ?? 'Welcome to our fashion store' }}</p>
                        <a href="{{ route('storefront.products', $businessDetail->store_slug) }}"
                           class="btn-primary-custom px-8 py-4 rounded-full text-lg font-medium inline-block transition-transform duration-300 hover:scale-105 hover:shadow-lg animate-pulse-subtle">
                            Shop Now
                        </a>
                    </div>
                </div>
                <!-- Decorative elements -->
                <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
        @else
            <div class="h-[70vh] md:h-[80vh] w-full bg-gradient-to-r from-secondary-custom to-primary-custom">
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-white px-4 max-w-3xl mx-auto animate-fade-in-up">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4 leading-tight">{{ $businessDetail->business_name }}</h1>
                        <p class="text-xl md:text-2xl mb-8 opacity-90">{{ $businessDetail->store_description ?? 'Welcome to our fashion store' }}</p>
                        <a href="{{ route('storefront.products', $businessDetail->store_slug) }}"
                           class="btn-primary-custom px-8 py-4 rounded-full text-lg font-medium inline-block transition-transform duration-300 hover:scale-105 hover:shadow-lg animate-pulse-subtle">
                            Shop Now
                        </a>
                    </div>
                </div>
                <!-- Decorative elements -->
                <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
        @endif
    </div>

    <!-- Featured Products Section -->
    @if($businessDetail->store_show_featured_products && count($featuredProducts) > 0)
        <div class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-secondary-custom mb-3 relative inline-block">
                        Featured Products
                        <span class="absolute bottom-0 left-0 w-full h-1 bg-accent-custom transform scale-x-0 transition-transform duration-300 group-hover:scale-x-100"></span>
                    </h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Discover our handpicked selection of premium products designed for style and comfort</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($featuredProducts as $product)
                        <div class="group relative bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-[1.02] hover:shadow-xl animate-fade-in-up opacity-0">
                            <div class="relative overflow-hidden">
                                @if($product->primary_image)
                                    <img src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}"
                                         class="w-full h-72 object-cover transform transition duration-500 group-hover:scale-110">
                                    @if($product->isOnSale())
                                        <div class="absolute top-0 right-0 bg-accent-custom text-white px-3 py-1 m-2 rounded-full text-sm font-bold animate-pulse-slow">
                                            SALE
                                        </div>
                                    @endif
                                @else
                                    <div class="w-full h-72 flex items-center justify-center bg-gray-100">
                                        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                <!-- Quick shop overlay -->
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}"
                                       class="bg-white/90 text-secondary-custom px-4 py-2 rounded-full text-sm font-medium hover:bg-white transition-colors duration-200 mx-2">
                                        Quick View
                                    </a>
                                </div>
                            </div>

                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-secondary-custom group-hover:text-primary-custom transition-colors duration-300">
                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}" class="hover:underline">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="mt-2 text-sm text-gray-600">{{ Str::limit($product->description, 60) }}</p>

                                <div class="mt-4 flex justify-between items-center">
                                    <div class="flex flex-col">
                                        @if($product->isOnSale())
                                            <span class="text-accent-custom font-bold text-lg">{{ $currencySymbol }}{{ number_format($product->sale_price, 2) }}</span>
                                            <span class="text-gray-500 line-through text-sm">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="text-accent-custom font-bold text-lg">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                        @endif
                                    </div>

                                    <form action="{{ route('storefront.cart.add', $businessDetail->store_slug) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit"
                                                class="bg-accent-custom text-white px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 hover:bg-primary-custom hover:shadow-md transform hover:-translate-y-1 animate-pulse-subtle">
                                            Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 text-center">
                    <div class="relative inline-block animate-fade-in-up opacity-0" style="animation-delay: 0.5s;">
                        <a href="{{ route('storefront.products', $businessDetail->store_slug) }}"
                           class="inline-flex items-center px-6 py-3 border border-transparent rounded-full shadow-md text-base font-medium text-white bg-primary-custom hover:bg-secondary-custom transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1 group">
                            <span>View All Products</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- New Arrivals Section -->
    @if($businessDetail->store_show_new_arrivals && count($newArrivals) > 0)
        <div class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-block px-3 py-1 bg-accent-custom/20 text-accent-custom rounded-full text-sm font-semibold mb-3">Just Arrived</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-secondary-custom mb-3">New Arrivals</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Be the first to shop our latest collection of fresh designs and styles</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($newArrivals as $product)
                        <div class="group relative bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-[1.02] hover:shadow-xl animate-fade-in-up opacity-0">
                            <!-- New tag -->
                            <div class="absolute top-0 left-0 z-10 m-2 animate-float">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-custom text-white shadow-sm">
                                    NEW
                                </span>
                            </div>

                            <div class="relative overflow-hidden">
                                @if($product->primary_image)
                                    <img src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}"
                                         class="w-full h-72 object-cover transform transition duration-500 group-hover:scale-110">
                                    @if($product->isOnSale())
                                        <div class="absolute top-0 right-0 bg-accent-custom text-white px-3 py-1 m-2 rounded-full text-sm font-bold animate-pulse-slow">
                                            SALE
                                        </div>
                                    @endif
                                @else
                                    <div class="w-full h-72 flex items-center justify-center bg-gray-100">
                                        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                <!-- Quick view button on hover -->
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}"
                                       class="bg-white/90 text-secondary-custom px-4 py-2 rounded-full text-sm font-medium hover:bg-white transition-colors duration-200 shadow-md transform transition duration-300 hover:scale-105">
                                        Quick View
                                    </a>
                                </div>
                            </div>

                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-secondary-custom group-hover:text-primary-custom transition-colors duration-300">
                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}" class="hover:underline">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="mt-2 text-sm text-gray-600">{{ Str::limit($product->description, 60) }}</p>

                                <div class="mt-4 flex justify-between items-center">
                                    <div class="flex flex-col">
                                        @if($product->isOnSale())
                                            <span class="text-accent-custom font-bold text-lg">{{ $currencySymbol }}{{ number_format($product->sale_price, 2) }}</span>
                                            <span class="text-gray-500 line-through text-sm">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="text-accent-custom font-bold text-lg">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                        @endif
                                    </div>

                                    <form action="{{ route('storefront.cart.add', $businessDetail->store_slug) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit"
                                                class="bg-accent-custom text-white px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 hover:bg-primary-custom hover:shadow-md transform hover:-translate-y-1 animate-pulse-subtle">
                                            Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 text-center">
                    <div class="relative inline-block animate-fade-in-up opacity-0" style="animation-delay: 0.5s;">
                        <div class="absolute -inset-1 bg-gradient-to-r from-accent-custom via-primary-custom to-accent-custom rounded-full opacity-50 blur-sm"></div>
                        <a href="{{ route('storefront.products', $businessDetail->store_slug) }}"
                           class="relative inline-flex items-center px-6 py-3 border border-transparent rounded-full shadow-md text-base font-medium text-white bg-primary-custom hover:bg-secondary-custom transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1 group">
                            <span>Explore All New Arrivals</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Custom Designs Section -->
    @if($businessDetail->store_show_custom_designs && count($customDesigns) > 0)
        <div class="py-16 bg-white relative">
            <!-- Decorative background element -->
            <div class="absolute inset-0 bg-gradient-to-b from-white via-primary-custom/5 to-white opacity-50 pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-12">
                    <div class="inline-block relative">
                        <span class="inline-block px-4 py-1 bg-secondary-custom/10 text-secondary-custom rounded-full text-sm font-semibold mb-3">Tailored For You</span>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-secondary-custom mb-3">Custom Designs</h2>
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-accent-custom rounded-full"></div>
                    </div>
                    <p class="text-gray-600 max-w-2xl mx-auto mt-6">Unique pieces crafted to your specifications and style preferences</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($customDesigns as $product)
                        <div class="group relative bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-[1.02] hover:shadow-xl border border-gray-100 animate-fade-in-up opacity-0">
                            <!-- Custom tag -->
                            <div class="absolute top-0 left-0 z-10 m-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-custom text-white shadow-md">
                                    CUSTOM
                                </span>
                            </div>

                            <div class="relative overflow-hidden">
                                @if($product->primary_image)
                                    <img src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}"
                                         class="w-full h-72 object-cover transform transition duration-500 group-hover:scale-110">
                                @else
                                    <div class="w-full h-72 flex items-center justify-center bg-gray-100">
                                        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                <!-- Overlay with gradient -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                <!-- Customize button on hover -->
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}"
                                       class="bg-white/90 text-secondary-custom px-5 py-2.5 rounded-full text-sm font-medium hover:bg-white transition-colors duration-200 shadow-md transform transition duration-300 hover:scale-105">
                                        Customize Now
                                    </a>
                                </div>

                                <!-- Decorative corner element -->
                                <div class="absolute bottom-0 right-0 w-16 h-16 bg-gradient-to-tl from-secondary-custom/30 to-transparent rounded-tl-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            </div>

                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-secondary-custom group-hover:text-primary-custom transition-colors duration-300">
                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}" class="hover:underline">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="mt-2 text-sm text-gray-600">{{ Str::limit($product->description, 60) }}</p>

                                <div class="mt-4 flex justify-between items-center">
                                    <div class="flex flex-col">
                                        @if($product->isOnSale())
                                            <span class="text-accent-custom font-bold text-lg">{{ $currencySymbol }}{{ number_format($product->sale_price, 2) }}</span>
                                            <span class="text-gray-500 line-through text-sm">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="text-accent-custom font-bold text-lg">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                        @endif
                                    </div>

                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}"
                                       class="bg-secondary-custom text-white px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 hover:bg-primary-custom hover:shadow-md transform hover:-translate-y-1 relative overflow-hidden group-hover:animate-pulse-subtle">
                                        <span class="relative z-10">View Details</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 text-center">
                    <div class="relative inline-block animate-fade-in-up opacity-0" style="animation-delay: 0.5s;">
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary-custom via-accent-custom to-secondary-custom rounded-full opacity-70 blur-sm group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-gradient-x"></div>
                        <a href="{{ route('storefront.products', [$businessDetail->store_slug, 'custom_order' => true]) }}"
                           class="relative inline-flex items-center px-6 py-3 border-2 border-secondary-custom rounded-full shadow-md text-base font-medium text-secondary-custom bg-white hover:bg-secondary-custom hover:text-white transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1">
                            <span>Explore All Custom Designs</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-storefront-layout>
