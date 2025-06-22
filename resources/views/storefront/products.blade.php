<x-storefront-layout :businessDetail="$businessDetail" :cart="$cart" title="{{ request()->has('custom_order') ? 'Custom Designs' : 'Products' }}">
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-white bg-opacity-80 z-50 flex items-center justify-center transition-opacity duration-300 opacity-0 pointer-events-none">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-custom mb-4"></div>
            <p class="text-primary-custom font-medium">Loading products...</p>
        </div>
    </div>

    <div class="py-8 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-secondary-custom mb-6 text-center animate-fade-in">
                {{ request()->has('custom_order') ? 'Custom Designs' : 'Products' }}
            </h1>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">
                <!-- Filters - Mobile Toggle -->
                <div class="lg:hidden mb-4">
                    <button type="button"
                            class="w-full flex items-center justify-between bg-white shadow-md rounded-lg p-3 sm:p-4 text-secondary-custom font-medium transition-all duration-300 hover:shadow-lg"
                            onclick="document.getElementById('filter-section').classList.toggle('hidden')">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-custom" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filters & Sorting
                        </span>
                        <svg id="filter-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-custom transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Filters -->
                <div id="filter-section" class="lg:col-span-1 hidden lg:block animate-fade-in">
                    <div class="bg-white shadow-lg rounded-lg p-4 sm:p-5 sticky top-20">
                        <h2 class="text-lg sm:text-xl font-semibold text-secondary-custom mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-custom" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filters
                        </h2>

                        <form action="{{ route('storefront.products', $businessDetail->store_slug) }}" method="GET" class="space-y-5">
                            @if(request()->has('custom_order'))
                                <input type="hidden" name="custom_order" value="1">
                            @endif

                            <!-- Category Filter -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-primary-custom" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                    </svg>
                                    Category
                                </label>
                                <select id="category" name="category" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-custom focus:border-primary-custom sm:text-sm rounded-md transition duration-150 ease-in-out bg-white">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Price Range Filter -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-primary-custom" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                    </svg>
                                    Price Range
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="min_price" class="sr-only">Min Price</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">{{ $currencySymbol }}</span>
                                            </div>
                                            <input type="number" id="min_price" name="min_price" placeholder="Min" value="{{ request('min_price') }}" min="0" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="max_price" class="sr-only">Max Price</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">{{ $currencySymbol }}</span>
                                            </div>
                                            <input type="number" id="max_price" name="max_price" placeholder="Max" value="{{ request('max_price') }}" min="0" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm bg-white">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sort By -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-primary-custom" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
                                    </svg>
                                    Sort By
                                </label>
                                <select id="sort" name="sort" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-custom focus:border-primary-custom sm:text-sm rounded-md transition duration-150 ease-in-out bg-white">
                                    <option value="newest" {{ request('sort') == 'newest' || !request('sort') ? 'selected' : '' }}>Newest</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                                </select>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row gap-3">
                                <button type="submit" class="flex-1 bg-primary-custom text-white py-3 px-4 rounded-md hover:bg-secondary-custom transition duration-300 ease-in-out transform hover:scale-105 flex items-center justify-center shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                    </svg>
                                    Apply Filters
                                </button>

                                @if(request('category') || request('min_price') || request('max_price') || request('sort'))
                                    <a href="{{ route('storefront.products', array_merge([$businessDetail->store_slug], request()->has('custom_order') ? ['custom_order' => 1] : [])) }}"
                                       class="flex-1 bg-gray-200 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-300 transition duration-300 ease-in-out flex items-center justify-center shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Clear Filters
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="lg:col-span-3">
                    @if($products->isEmpty())
                        <div class="bg-white shadow-lg rounded-lg p-8 text-center animate-fade-in">
                            <div class="relative mx-auto w-32 h-32 mb-6">
                                <svg class="h-32 w-32 text-gray-200 absolute" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.5 3.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2 4.5 3.5 3 2v20l1.5-1.5L6 22l1.5-1.5L9 22l1.5-1.5L12 22l1.5-1.5L15 22l1.5-1.5L18 22l1.5-1.5L21 22V2l-1.5 1.5z"></path>
                                </svg>
                                <svg class="h-20 w-20 text-primary-custom absolute top-6 left-6 animate-pulse-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold text-secondary-custom mb-3">No Products Found</h3>
                            <div class="max-w-md mx-auto">
                                <p class="text-gray-600 mb-6">
                                    We couldn't find any products matching your current filters. Try adjusting your search criteria or browse our other collections.
                                </p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="{{ route('storefront.products', $businessDetail->store_slug) }}" class="inline-flex items-center px-5 py-2.5 bg-primary-custom text-white rounded-md hover:bg-secondary-custom transition duration-300 shadow-md hover:shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                        View All Products
                                    </a>
                                    @if(request()->has('custom_order'))
                                        <a href="{{ route('storefront.products', $businessDetail->store_slug) }}" class="inline-flex items-center px-5 py-2.5 bg-accent-custom text-white rounded-md hover:bg-accent-custom/90 transition duration-300 shadow-md hover:shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
                                            </svg>
                                            Browse Regular Products
                                        </a>
                                    @else
                                        <a href="{{ route('storefront.products', [$businessDetail->store_slug, 'custom_order' => true]) }}" class="inline-flex items-center px-5 py-2.5 bg-accent-custom text-white rounded-md hover:bg-accent-custom/90 transition duration-300 shadow-md hover:shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                            </svg>
                                            Explore Custom Designs
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
                            @foreach($products as $product)
                                <div class="group relative bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1 animate-fade-in-up">
                                    <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}" class="block relative">
                                        <div class="aspect-w-1 aspect-h-1 bg-gray-200 group-hover:opacity-90 transition-opacity duration-300">
                                            @if($product->primary_image)
                                                <img src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}" class="w-full h-40 sm:h-48 md:h-52 lg:h-56 object-cover object-center">
                                            @else
                                                <div class="w-full h-40 sm:h-48 md:h-52 lg:h-56 flex items-center justify-center bg-gray-200">
                                                    <svg class="h-12 w-12 sm:h-16 sm:w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        @if($product->isOnSale())
                                            <div class="absolute top-2 right-2 bg-accent-custom text-white text-xs font-bold px-2 sm:px-3 py-1 rounded-full shadow-md animate-pulse-subtle">
                                                SALE
                                            </div>
                                        @endif
                                    </a>
                                    <div class="p-3 sm:p-4">
                                        <div class="mb-1">
                                            @if($product->category)
                                                <span class="inline-block text-xs font-medium text-primary-custom bg-primary-custom/10 px-2 py-0.5 rounded-full">
                                                    {{ $product->category }}
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="text-base sm:text-lg font-semibold text-secondary-custom mb-1 hover:text-primary-custom transition-colors duration-300 truncate">
                                            <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        <p class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-3 line-clamp-2 h-8 sm:h-10">{{ Str::limit($product->description, 80) }}</p>
                                        <div class="mt-auto">
                                            <div class="flex items-center mb-2 sm:mb-3">
                                                @if($product->isOnSale())
                                                    <span class="text-accent-custom font-bold text-base sm:text-lg">{{ $currencySymbol }}{{ number_format($product->sale_price, 2) }}</span>
                                                    <span class="text-gray-500 line-through text-xs sm:text-sm ml-2">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                                @else
                                                    <span class="text-accent-custom font-bold text-base sm:text-lg">{{ $currencySymbol }}{{ number_format($product->price, 2) }}</span>
                                                @endif
                                            </div>
                                            @if($product->is_custom_order)
                                                <a href="{{ route('storefront.product', [$businessDetail->store_slug, $product->id]) }}" class="w-full bg-primary-custom text-white px-3 sm:px-4 py-2 rounded-md text-xs sm:text-sm font-medium hover:bg-secondary-custom transition duration-300 ease-in-out flex items-center justify-center shadow-sm hover:shadow">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    View Details
                                                </a>
                                            @else
                                                <form action="{{ route('storefront.cart.add', $businessDetail->store_slug) }}" method="POST" class="w-full">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="w-full bg-accent-custom text-white px-3 sm:px-4 py-2 rounded-md text-xs sm:text-sm font-medium hover:bg-accent-custom/90 transition duration-300 ease-in-out transform hover:scale-105 flex items-center justify-center shadow-sm hover:shadow">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                                        </svg>
                                                        Add to Cart
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-10">
                            <div class="pagination-wrapper">
                                {{ $products->appends(request()->query())->links('pagination::tailwind') }}
                            </div>

                            <style>
                                /* Custom pagination styling */
                                .pagination-wrapper nav {
                                    display: flex;
                                    justify-content: center;
                                }

                                .pagination-wrapper .shadow-sm {
                                    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                                }

                                .pagination-wrapper .relative {
                                    position: relative;
                                }

                                .pagination-wrapper .z-0 {
                                    border-radius: 0.5rem;
                                    overflow: hidden;
                                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                                }

                                .pagination-wrapper .z-0 > span:not(.text-gray-500),
                                .pagination-wrapper .z-0 > a {
                                    padding: 0.75rem 1rem;
                                    display: inline-flex;
                                    align-items: center;
                                    justify-content: center;
                                    min-width: 2.5rem;
                                    font-weight: 500;
                                    transition: all 0.2s ease-in-out;
                                }

                                .pagination-wrapper .z-0 > span.bg-white {
                                    background-color: white;
                                }

                                .pagination-wrapper .z-0 > span.border-gray-300 {
                                    border-color: #e5e7eb;
                                }

                                .pagination-wrapper .z-0 > span.text-gray-500 {
                                    color: #6b7280;
                                }

                                .pagination-wrapper .z-0 > span.bg-primary-custom,
                                .pagination-wrapper .z-0 > span.text-white {
                                    background-color: var(--primary-color);
                                    color: white;
                                }

                                .pagination-wrapper .z-0 > a:hover {
                                    background-color: #f3f4f6;
                                    color: var(--primary-color);
                                }

                                @media (max-width: 640px) {
                                    .pagination-wrapper .z-0 > span:not(.text-gray-500),
                                    .pagination-wrapper .z-0 > a {
                                        padding: 0.5rem 0.75rem;
                                        min-width: 2rem;
                                        font-size: 0.875rem;
                                    }

                                    .pagination-wrapper .z-0 {
                                        max-width: 100%;
                                        overflow-x: auto;
                                        display: flex;
                                        justify-content: center;
                                    }
                                }
                            </style>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Page Scripts: Filter Toggle and Loading State -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter section handling
            const filterSection = document.getElementById('filter-section');
            const filterIcon = document.getElementById('filter-icon');
            const filterToggleBtn = document.querySelector('[onclick="document.getElementById(\'filter-section\').classList.toggle(\'hidden\')"]');
            const loadingOverlay = document.getElementById('loading-overlay');

            // Show loading overlay on initial page load
            showLoadingOverlay();

            // Hide loading overlay when page is fully loaded
            window.addEventListener('load', function() {
                hideLoadingOverlay();
            });

            // Show loading overlay when form is submitted
            const filterForms = document.querySelectorAll('form');
            filterForms.forEach(form => {
                form.addEventListener('submit', function() {
                    showLoadingOverlay();
                });
            });

            // Show loading overlay when pagination or filter links are clicked
            document.querySelectorAll('a').forEach(link => {
                // Only add event listener to pagination and filter-related links
                if (link.href.includes('page=') ||
                    link.href.includes('category=') ||
                    link.href.includes('sort=') ||
                    link.href.includes('min_price=') ||
                    link.href.includes('max_price=') ||
                    link.href.includes('custom_order=')) {
                    link.addEventListener('click', function() {
                        showLoadingOverlay();
                    });
                }
            });

            // Update the toggle button to also rotate the icon
            if (filterToggleBtn) {
                filterToggleBtn.onclick = function(e) {
                    e.preventDefault();
                    filterSection.classList.toggle('hidden');

                    // Rotate the icon when toggled
                    if (filterIcon) {
                        if (filterSection.classList.contains('hidden')) {
                            filterIcon.style.transform = 'rotate(0deg)';
                        } else {
                            filterIcon.style.transform = 'rotate(180deg)';
                        }
                    }
                };
            }

            // Check if we're on a desktop device and show filter by default
            if (window.innerWidth >= 1024) {
                filterSection.classList.remove('hidden');
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    filterSection.classList.remove('hidden');
                    // Reset icon rotation on desktop
                    if (filterIcon) filterIcon.style.transform = 'rotate(0deg)';
                } else if (!filterToggleBtn.getAttribute('data-clicked')) {
                    // Only hide on mobile if user hasn't explicitly clicked the toggle
                    filterSection.classList.add('hidden');
                }
            });

            // Add smooth transition for filter section
            filterSection.style.transition = 'all 0.3s ease-in-out';

            // Helper functions for loading overlay
            function showLoadingOverlay() {
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
                    loadingOverlay.classList.add('opacity-100');
                    document.body.classList.add('overflow-hidden');
                }
            }

            function hideLoadingOverlay() {
                if (loadingOverlay) {
                    setTimeout(() => {
                        loadingOverlay.classList.add('opacity-0', 'pointer-events-none');
                        loadingOverlay.classList.remove('opacity-100');
                        document.body.classList.remove('overflow-hidden');
                    }, 500); // Delay to ensure content is fully loaded
                }
            }
        });
    </script>
</x-storefront-layout>
