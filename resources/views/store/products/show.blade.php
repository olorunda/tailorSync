<x-layouts.app :title="__('Product Details')">
    <div class="w-full">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h1>
                <p class="text-zinc-600 dark:text-zinc-400">Product details and information</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('store.products.index') }}" class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-orange-600">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Back to Products
                </a>
                @if(auth()->user()->hasPermission('edit_store_products'))
                    <a href="{{ route('store.products.edit', $product) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-orange-600">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Product
                    </a>
                @endif
            </div>
        </div>

        <div class="w-full">
            @if(session('success'))
                <div class="mb-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            @if($product->primary_image)
                                <div class="mb-4">
                                    <img src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}" class="w-full h-auto rounded-lg shadow-md">
                                </div>
                            @endif

                            @if(!empty($product->images) && count($product->images) > 0)
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach($product->images as $image)
                                        <div>
                                            <img src="{{ Storage::url($image) }}" alt="{{ $product->name }}" class="w-full h-auto rounded-lg shadow-sm">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h1>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">SKU: {{ $product->sku }}</p>
                                </div>
                                <div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($product->is_active) bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 @else bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 @endif">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($product->is_featured)
                                        <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                            Featured
                                        </span>
                                    @endif
                                    @if($product->is_custom_order)
                                        <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                            Custom Order
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="flex items-baseline">
                                    @if($product->sale_price)
                                        <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">${{ number_format($product->sale_price, 2) }}</p>
                                        <p class="ml-2 text-xl text-zinc-500 dark:text-zinc-400 line-through">${{ number_format($product->price, 2) }}</p>
                                    @else
                                        <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">${{ number_format($product->price, 2) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Description</h3>
                                <div class="mt-2 prose prose-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $product->description ?? 'No description available.' }}
                                </div>
                            </div>

                            <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Category</h3>
                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $product->category ?? 'Uncategorized' }}</p>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Stock</h3>
                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $product->stock_quantity }} units</p>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($product->sizes) || !empty($product->colors) || !empty($product->materials))
                                <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Product Options</h3>
                                    <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @if(!empty($product->sizes))
                                            <div>
                                                <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Available Sizes</h4>
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach($product->sizes as $size)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
                                                            {{ $size }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($product->colors))
                                            <div>
                                                <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Available Colors</h4>
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach($product->colors as $color)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
                                                            {{ $color }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($product->materials))
                                            <div>
                                                <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Materials</h4>
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach($product->materials as $material)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
                                                            {{ $material }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!empty($product->tags))
                                <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Tags</h3>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($product->tags as $tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <div>
                    @if(auth()->user()->hasPermission('edit_store_products'))
                        <a href="{{ route('store.products.edit', $product) }}" class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-orange-600">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit Product
                        </a>
                    @endif
                </div>
                <div>
                    @if(auth()->user()->hasPermission('delete_store_products'))
                        <form action="{{ route('store.products.destroy', $product) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-red-600" onclick="return confirm('Are you sure you want to delete this product?')">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Product
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
