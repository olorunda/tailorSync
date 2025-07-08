<x-storefront-layout :businessDetail="$businessDetail" :cart="$cart" :title="$product->name">
    <div class="py-8 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex flex-wrap mb-4 sm:mb-6 bg-gray-50 p-2 sm:p-3 rounded-lg shadow-sm" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center">
                    <li class="flex items-center">
                        <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" class="text-primary-custom hover:text-secondary-custom transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            <span class="sr-only">Home</span>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ route('storefront.products', array_merge([$businessDetail->store_slug], $product->is_custom_order ? ['custom_order' => true] : [])) }}"
                           class="text-primary-custom hover:text-secondary-custom transition-colors duration-300 whitespace-nowrap text-xs sm:text-sm">
                            {{ $product->is_custom_order ? 'Custom Designs' : 'Products' }}
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-700 font-medium truncate max-w-[150px] sm:max-w-[200px] md:max-w-xs text-xs sm:text-sm">{{ $product->name }}</span>
                    </li>
                </ol>
            </nav>

            <!-- Product Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 md:gap-8">
                <!-- Product Images -->
                <div class="animate-fade-in">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden relative group">
                        @if($product->isOnSale())
                            <div class="absolute top-2 sm:top-4 left-2 sm:left-4 z-10 bg-accent-custom text-white text-xs sm:text-sm font-bold px-2 sm:px-3 py-1 sm:py-1.5 rounded-full shadow-md animate-pulse-subtle">
                                {{ $product->getDiscountPercentage() }}% OFF
                            </div>
                        @endif

                        <div class="relative">
                            @if($product->primary_image)
                                <img id="main-image" src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}"
                                     class="w-full h-56 sm:h-64 md:h-80 lg:h-96 object-contain object-center p-2 sm:p-4 transition-all duration-300 transform group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            @else
                                <div class="w-full h-56 sm:h-64 md:h-80 lg:h-96 flex items-center justify-center bg-gray-100">
                                    <svg class="h-16 w-16 sm:h-20 sm:w-20 md:h-24 md:w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Image Navigation Arrows (if multiple images) -->
                        @if(!empty($product->images) && count($product->images) > 1)
                            <button id="prev-image" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-1.5 sm:p-2 rounded-full shadow-md opacity-70 sm:opacity-0 group-hover:opacity-100 transition-opacity duration-300 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button id="next-image" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-1.5 sm:p-2 rounded-full shadow-md opacity-70 sm:opacity-0 group-hover:opacity-100 transition-opacity duration-300 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if(!empty($product->images) && count($product->images) > 1)
                        <div class="mt-2 sm:mt-4 grid grid-cols-5 sm:grid-cols-5 md:grid-cols-6 gap-1 sm:gap-2 overflow-x-auto pb-2">
                            <!-- Include primary image in thumbnails -->
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer transition-transform duration-300 hover:scale-105 thumbnail-item active"
                                 data-src="{{ Storage::url($product->primary_image) }}">
                                <img src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}" class="w-full h-12 sm:h-16 md:h-20 object-cover">
                            </div>

                            @foreach($product->images as $index => $image)
                                <div class="bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer transition-transform duration-300 hover:scale-105 thumbnail-item"
                                     data-src="{{ Storage::url($image) }}">
                                    <img src="{{ Storage::url($image) }}" alt="{{ $product->name }} - Image {{ $index + 1 }}" class="w-full h-12 sm:h-16 md:h-20 object-cover">
                                </div>
                            @endforeach
                        </div>

                        <!-- Image Gallery Script -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const mainImage = document.getElementById('main-image');
                                const thumbnails = document.querySelectorAll('.thumbnail-item');
                                const prevButton = document.getElementById('prev-image');
                                const nextButton = document.getElementById('next-image');
                                let currentIndex = 0;

                                // Function to update the main image
                                function updateMainImage(index) {
                                    // Remove active class from all thumbnails
                                    thumbnails.forEach(thumb => thumb.classList.remove('active', 'ring-2', 'ring-primary-custom'));

                                    // Add active class to current thumbnail
                                    thumbnails[index].classList.add('active', 'ring-2', 'ring-primary-custom');

                                    // Update main image
                                    mainImage.src = thumbnails[index].getAttribute('data-src');

                                    // Update current index
                                    currentIndex = index;
                                }

                                // Add click event to thumbnails
                                thumbnails.forEach((thumbnail, index) => {
                                    thumbnail.addEventListener('click', () => {
                                        updateMainImage(index);
                                    });
                                });

                                // Add click event to prev button
                                if (prevButton) {
                                    prevButton.addEventListener('click', () => {
                                        let newIndex = currentIndex - 1;
                                        if (newIndex < 0) newIndex = thumbnails.length - 1;
                                        updateMainImage(newIndex);
                                    });
                                }

                                // Add click event to next button
                                if (nextButton) {
                                    nextButton.addEventListener('click', () => {
                                        let newIndex = currentIndex + 1;
                                        if (newIndex >= thumbnails.length) newIndex = 0;
                                        updateMainImage(newIndex);
                                    });
                                }

                                // Initialize first thumbnail as active
                                thumbnails[0].classList.add('active', 'ring-2', 'ring-primary-custom');
                            });
                        </script>
                    @endif
                </div>

                <!-- Product Info -->
                <div class="animate-fade-in">
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <!-- Product Title and Category -->
                        <div class="border-b border-gray-100 pb-3 sm:pb-4">
                            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-secondary-custom mb-2 leading-tight">{{ $product->name }}</h1>

                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-2 sm:mt-3">
                                @if($product->category)
                                    <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-primary-custom bg-opacity-10 text-primary-custom">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5 mr-0.5 sm:mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $product->category }}
                                    </span>
                                @endif

                                @if($product->is_custom_order)
                                    <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-secondary-custom bg-opacity-10 text-secondary-custom">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5 mr-0.5 sm:mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                        </svg>
                                        Custom Design
                                    </span>
                                @endif

                                @if($product->stock_quantity > 0 && !$product->is_custom_order)
                                    <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5 mr-0.5 sm:mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        In Stock
                                    </span>
                                @elseif(!$product->is_custom_order)
                                    <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5 mr-0.5 sm:mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        Out of Stock
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Price Section -->
                        <div class="py-3 sm:py-4 border-b border-gray-100">
                            <div class="flex items-center">
                                @if($product->isOnSale())
                                    <div class="flex flex-col">
                                        <div class="flex items-center">
                                            <span class="text-2xl sm:text-3xl font-bold text-accent-custom">{{ $currencySymbol }}{{ number_format($product->sale_price, 2) }}</span>
                                            <span class="ml-2 sm:ml-3 text-base sm:text-lg text-gray-500 line-through">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                        </div>
                                        <div class="mt-1 text-xs sm:text-sm text-green-600 font-medium">
                                            You save: {{ $currencySymbol }}{{ number_format($product->price - $product->sale_price, 2) }} ({{ $product->getDiscountPercentage() }}%)
                                        </div>
                                    </div>
                                @else
                                    <span class="text-2xl sm:text-3xl font-bold text-accent-custom">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="py-3 sm:py-4 border-b border-gray-100">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1 sm:mb-2">Description</h3>
                            <div class="prose prose-sm max-w-none text-gray-600 text-sm sm:text-base">
                                <p>{{ $product->description }}</p>
                            </div>
                        </div>

                    @if(!$product->is_custom_order)
                        <!-- Regular Product Form -->
                        <form action="{{ route('storefront.cart.add', $businessDetail->store_slug) }}" method="POST" class="mb-4 sm:mb-6">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            @if(!empty($product->sizes))
                                <div class="mb-3 sm:mb-4">
                                    <label for="size" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Size</label>
                                    <x-simple-select
                                        id="size"
                                        name="options[size]"
                                        :options="collect($product->sizes)->map(fn($size) => ['id' => $size, 'name' => $size])->toArray()"
                                        class="mt-1"
                                    />
                                </div>
                            @endif

                            @if(!empty($product->colors))
                                <div class="mb-3 sm:mb-4">
                                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Color</label>
                                    <x-simple-select
                                        id="color"
                                        name="options[color]"
                                        :options="collect($product->colors)->map(fn($color) => ['id' => $color, 'name' => $color])->toArray()"
                                        class="mt-1"
                                    />
                                </div>
                            @endif

                            <div class="mb-3 sm:mb-4">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Quantity</label>
                                <div class="flex flex-wrap items-center">
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="block w-20 sm:w-24 border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom text-sm">
                                    <span class="ml-2 text-xs sm:text-sm text-gray-500 self-center">
                                        {{ $product->stock_quantity }} available
                                    </span>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-accent-custom text-white py-2.5 sm:py-3 px-4 rounded-md hover:bg-opacity-90 transition duration-150 ease-in-out flex items-center justify-center text-sm sm:text-base">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Add to Cart
                            </button>
                        </form>
                    @else
                        <!-- Custom Order Form -->
                        <form action="{{ route('storefront.cart.add', $businessDetail->store_slug) }}" method="POST" class="mb-4 sm:mb-6">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="mb-3 sm:mb-4">
                                <label for="custom_notes" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Custom Design Notes</label>
                                <textarea id="custom_notes" name="custom_design_data[notes]" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom text-sm" placeholder="Please provide any specific details about your custom order..."></textarea>
                            </div>

                            @if(!empty($product->sizes))
                                <div class="mb-3 sm:mb-4">
                                    <label for="size" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Size</label>
                                    <x-simple-select
                                        id="size"
                                        name="custom_design_data[size]"
                                        :options="collect($product->sizes)->map(fn($size) => ['id' => $size, 'name' => $size])->toArray()"
                                        class="mt-1"
                                    />
                                </div>
                            @endif

                            @if(!empty($product->colors))
                                <div class="mb-3 sm:mb-4">
                                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Color</label>
                                    <x-simple-select
                                        id="color"
                                        name="custom_design_data[color]"
                                        :options="collect($product->colors)->map(fn($color) => ['id' => $color, 'name' => $color])->toArray()"
                                        class="mt-1"
                                    />
                                </div>
                            @endif

                            @if(!empty($product->materials))
                                <div class="mb-3 sm:mb-4">
                                    <label for="material" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Material</label>
                                    <x-simple-select
                                        id="material"
                                        name="custom_design_data[material]"
                                        :options="collect($product->materials)->map(fn($material) => ['id' => $material, 'name' => $material])->toArray()"
                                        class="mt-1"
                                    />
                                </div>
                            @endif

                            <div class="mb-3 sm:mb-4">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Quantity</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" class="block w-20 sm:w-24 border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom text-sm">
                            </div>

                            <button type="submit" class="w-full bg-primary-custom text-white py-2.5 sm:py-3 px-4 rounded-md hover:bg-secondary-custom transition duration-150 ease-in-out flex items-center justify-center text-sm sm:text-base">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                                Order Custom Design
                            </button>
                        </form>
                    @endif

                    <!-- Product Details -->
                    <div class="mt-6 sm:mt-8">
                        <h2 class="text-lg sm:text-xl font-semibold text-secondary-custom mb-3 sm:mb-4">Product Details</h2>
                        <div class="border-t border-gray-200 pt-3 sm:pt-4">
                            @if($product->sku)
                                <div class="py-1.5 sm:py-2 flex flex-wrap justify-between">
                                    <span class="text-gray-500 text-sm sm:text-base">SKU</span>
                                    <span class="text-gray-900 text-sm sm:text-base">{{ $product->sku }}</span>
                                </div>
                            @endif

                            @if($product->category)
                                <div class="py-1.5 sm:py-2 flex flex-wrap justify-between border-t border-gray-200">
                                    <span class="text-gray-500 text-sm sm:text-base">Category</span>
                                    <span class="text-gray-900 text-sm sm:text-base">{{ $product->category }}</span>
                                </div>
                            @endif

                            @if(!empty($product->materials))
                                <div class="py-1.5 sm:py-2 flex flex-wrap justify-between border-t border-gray-200">
                                    <span class="text-gray-500 text-sm sm:text-base">Materials</span>
                                    <span class="text-gray-900 text-sm sm:text-base">{{ implode(', ', $product->materials) }}</span>
                                </div>
                            @endif

                            @if(!empty($product->tags))
                                <div class="py-1.5 sm:py-2 flex flex-wrap justify-between border-t border-gray-200">
                                    <span class="text-gray-500 text-sm sm:text-base">Tags</span>
                                    <span class="text-gray-900 text-sm sm:text-base">{{ implode(', ', $product->tags) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            @if(count($relatedProducts) > 0)
                <div class="mt-10 sm:mt-16 animate-fade-in">
                    <h2 class="text-xl sm:text-2xl font-bold text-secondary-custom mb-4 sm:mb-6 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 mr-1.5 sm:mr-2 text-primary-custom" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                        </svg>
                        You May Also Like
                    </h2>

                    <!-- Mobile Swiper for Small Screens -->
                    <div class="block md:hidden overflow-x-auto pb-4 -mx-4 px-4">
                        <div class="flex space-x-3 w-max">
                            @foreach($relatedProducts as $relatedProduct)
                                <div class="w-52 sm:w-64 flex-shrink-0">
                                    <div class="group bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
                                        <a href="{{ route('storefront.product', [$businessDetail->store_slug, $relatedProduct->id]) }}" class="block relative">
                                            <div class="aspect-w-1 aspect-h-1 bg-gray-100 group-hover:opacity-90 transition-opacity duration-300">
                                                @if($relatedProduct->primary_image)
                                                    <img src="{{ Storage::url($relatedProduct->primary_image) }}" alt="{{ $relatedProduct->name }}" class="w-full h-40 sm:h-48 object-cover object-center">
                                                @else
                                                    <div class="w-full h-40 sm:h-48 flex items-center justify-center bg-gray-100">
                                                        <svg class="h-12 w-12 sm:h-16 sm:w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif

                                                @if($relatedProduct->isOnSale())
                                                    <div class="absolute top-2 right-2 bg-accent-custom text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">
                                                        {{ $relatedProduct->getDiscountPercentage() }}% OFF
                                                    </div>
                                                @endif
                                            </div>
                                        </a>

                                        <div class="p-3">
                                            <h3 class="text-sm sm:text-base font-semibold text-secondary-custom mb-1 truncate">
                                                <a href="{{ route('storefront.product', [$businessDetail->store_slug, $relatedProduct->id]) }}" class="hover:text-primary-custom transition-colors duration-300">
                                                    {{ $relatedProduct->name }}
                                                </a>
                                            </h3>
                                            <p class="text-xs text-gray-600 mb-2 line-clamp-2 h-8">{{ Str::limit($relatedProduct->description, 60) }}</p>
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    @if($relatedProduct->isOnSale())
                                                        <span class="text-accent-custom font-bold text-sm">{{ $currencySymbol }}{{ number_format($relatedProduct->sale_price, 2) }}</span>
                                                        <span class="text-gray-500 line-through text-xs ml-1">{{ $currencySymbol }}{{ number_format($relatedProduct->price, 2) }}</span>
                                                    @else
                                                        <span class="text-accent-custom font-bold text-sm">{{ $currencySymbol }}{{ number_format($relatedProduct->price, 2) }}</span>
                                                    @endif
                                                </div>
                                                <a href="{{ route('storefront.product', [$businessDetail->store_slug, $relatedProduct->id]) }}"
                                                   class="inline-flex items-center text-primary-custom hover:text-secondary-custom text-xs sm:text-sm font-medium transition-colors duration-300">
                                                    <span>View</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Desktop Grid for Medium and Large Screens -->
                    <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($relatedProducts as $relatedProduct)
                            <div class="group bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
                                <a href="{{ route('storefront.product', [$businessDetail->store_slug, $relatedProduct->id]) }}" class="block relative">
                                    <div class="aspect-w-1 aspect-h-1 bg-gray-100 group-hover:opacity-90 transition-opacity duration-300">
                                        @if($relatedProduct->primary_image)
                                            <img src="{{ Storage::url($relatedProduct->primary_image) }}" alt="{{ $relatedProduct->name }}" class="w-full h-48 sm:h-52 lg:h-56 object-cover object-center">
                                        @else
                                            <div class="w-full h-48 sm:h-52 lg:h-56 flex items-center justify-center bg-gray-100">
                                                <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif

                                        @if($relatedProduct->isOnSale())
                                            <div class="absolute top-2 right-2 bg-accent-custom text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                                                {{ $relatedProduct->getDiscountPercentage() }}% OFF
                                            </div>
                                        @endif
                                    </div>
                                </a>

                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-secondary-custom mb-1 truncate">
                                        <a href="{{ route('storefront.product', [$businessDetail->store_slug, $relatedProduct->id]) }}" class="hover:text-primary-custom transition-colors duration-300">
                                            {{ $relatedProduct->name }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2 h-10">{{ Str::limit($relatedProduct->description, 60) }}</p>
                                    <div class="flex justify-between items-center">
                                        <div>
                                            @if($relatedProduct->isOnSale())
                                                <span class="text-accent-custom font-bold">{{ $currencySymbol }}{{ number_format($relatedProduct->sale_price, 2) }}</span>
                                                <span class="text-gray-500 line-through text-sm ml-1">{{ $currencySymbol }}{{ number_format($relatedProduct->price, 2) }}</span>
                                            @else
                                                <span class="text-accent-custom font-bold">{{ $currencySymbol }}{{ number_format($relatedProduct->price, 2) }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('storefront.product', [$businessDetail->store_slug, $relatedProduct->id]) }}"
                                           class="inline-flex items-center px-3 py-1 bg-primary-custom bg-opacity-10 text-primary-custom rounded-full text-sm font-medium hover:bg-opacity-20 transition-colors duration-300">
                                            <span>View Details</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</x-storefront-layout>
