<x-layouts.app :title="__('Create New Product')">
    <div class="w-full">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Create New Product</h1>
                <p class="text-zinc-600 dark:text-zinc-400">Add a new product to your store</p>
            </div>
            <a href="{{ route('store.products.index') }}" class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-orange-600">
                <svg class="-ml-1 mr-2 h-5 w-5 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Products
            </a>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Product Information</h3>
            </div>
            <div class="p-6">
                    <form action="{{ route('store.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Product Information</h3>

                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Product Name</label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                                    @error('name')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="sku" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">SKU</label>
                                    <input type="text" id="sku" name="sku" value="{{ old('sku') }}" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Leave blank to auto-generate</p>
                                    @error('sku')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="category" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Category</label>
                                    <input type="text" id="category" name="category" value="{{ old('category') }}" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                    @error('category')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                                    <textarea id="description" name="description" rows="4" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Pricing & Inventory</h3>

                                <div class="mb-4">
                                    <label for="price" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Price</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-7 p-2.5" required>
                                    </div>
                                    @error('price')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="sale_price" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sale Price (Optional)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" min="0" step="0.01" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-7 p-2.5">
                                    </div>
                                    @error('sale_price')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="stock_quantity" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Stock Quantity</label>
                                    <input type="number" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                                    @error('stock_quantity')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="is_featured" name="is_featured" type="checkbox" value="1" {{ old('is_featured') ? 'checked' : '' }} class="focus:ring-orange-500 h-4 w-4 text-orange-600 dark:text-orange-500 border-zinc-300 dark:border-zinc-600 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="is_featured" class="font-medium text-zinc-700 dark:text-zinc-300">Featured Product</label>
                                            <p class="text-zinc-500 dark:text-zinc-400">Featured products appear on the homepage of your store.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', 1) ? 'checked' : '' }} class="focus:ring-orange-500 h-4 w-4 text-orange-600 dark:text-orange-500 border-zinc-300 dark:border-zinc-600 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="is_active" class="font-medium text-zinc-700 dark:text-zinc-300">Active Product</label>
                                            <p class="text-zinc-500 dark:text-zinc-400">Inactive products are not visible in your store.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="is_custom_order" name="is_custom_order" type="checkbox" value="1" {{ old('is_custom_order') ? 'checked' : '' }} class="focus:ring-orange-500 h-4 w-4 text-orange-600 dark:text-orange-500 border-zinc-300 dark:border-zinc-600 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="is_custom_order" class="font-medium text-zinc-700 dark:text-zinc-300">Custom Order</label>
                                            <p class="text-zinc-500 dark:text-zinc-400">Custom orders may require additional information from customers.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Product Images</h3>

                            <div class="mb-4">
                                <label for="primary_image" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Primary Image</label>
                                <input type="file" id="primary_image" name="primary_image" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                @error('primary_image')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="images" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Additional Images</label>
                                <input type="file" id="images" name="images[]" multiple class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">You can select multiple files.</p>
                                @error('images')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                                @error('images.*')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <a href="{{ route('store.products.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Create Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
