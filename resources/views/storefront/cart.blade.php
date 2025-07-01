<x-storefront-layout :businessDetail="$businessDetail" :cart="$cart" title="Shopping Cart">
    <div class="py-8 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-secondary-custom mb-8 text-center">Shopping Cart</h1>

            @if($cart->items->isEmpty())
                <div class="bg-white shadow-md rounded-lg p-8 text-center">
                    <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                    <p class="text-gray-500 mb-6">Looks like you haven't added any items to your cart yet.</p>
                    <a href="{{ route('storefront.products', $businessDetail->store_slug) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-custom hover:bg-secondary-custom">
                        Browse Products
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h2 class="text-lg font-medium text-secondary-custom">Cart Items ({{ $cart->getItemCount() }})</h2>
                            </div>

                            <ul role="list" class="divide-y divide-gray-200">
                                @foreach($cart->items as $item)
                                    <li class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                                @if($item->product->primary_image)
                                                    <img src="{{ Storage::url($item->product->primary_image) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover object-center">
                                                @else
                                                    <div class="h-full w-full flex items-center justify-center bg-gray-200">
                                                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="ml-4 flex-1 flex flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900">
                                                        <h3>
                                                            <a href="{{ route('storefront.product', [$businessDetail->store_slug, $item->product->id]) }}">
                                                                {{ $item->product->name }}
                                                            </a>
                                                        </h3>
                                                        <p class="ml-4 text-accent-custom">{{ $currencySymbol }}{{ number_format($item->price, 2) }}</p>
                                                    </div>

                                                    @if($item->product->category)
                                                        <p class="mt-1 text-sm text-gray-500">{{ $item->product->category }}</p>
                                                    @endif

                                                    @if(!empty($item->options))
                                                        <div class="mt-1 text-sm text-gray-500">
                                                            @foreach($item->options as $key => $value)
                                                                <span class="mr-2">{{ ucfirst($key) }}: {{ $value }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if(!empty($item->custom_design_data))
                                                        <div class="mt-1 text-sm text-gray-500">
                                                            <span class="font-medium text-primary-custom">Custom Design</span>
                                                            @foreach($item->custom_design_data as $key => $value)
                                                                @if($key !== 'notes')
                                                                    <span class="mr-2">{{ ucfirst($key) }}: {{ $value }}</span>
                                                                @endif
                                                            @endforeach

                                                            @if(!empty($item->custom_design_data['notes']))
                                                                <div class="mt-1 text-xs text-gray-500">
                                                                    <span class="font-medium">Notes:</span> {{ Str::limit($item->custom_design_data['notes'], 100) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="flex-1 flex items-end justify-between text-sm">
                                                    <form action="{{ route('storefront.cart.update', $businessDetail->store_slug) }}" method="POST" class="flex items-center">
                                                        @csrf
                                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                                        <label for="quantity-{{ $item->id }}" class="mr-2 text-gray-500">Qty</label>
                                                        <input type="number" id="quantity-{{ $item->id }}" name="quantity" value="{{ $item->quantity }}" min="1" class="w-16 border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm">
                                                        <button type="submit" class="ml-2 text-primary-custom hover:text-secondary-custom">
                                                            Update
                                                        </button>
                                                    </form>

                                                    <div class="flex">
                                                        <a href="{{ route('storefront.cart.remove', [$businessDetail->store_slug, $item->id]) }}" class="font-medium text-red-600 hover:text-red-500">
                                                            Remove
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h2 class="text-lg font-medium text-secondary-custom">Order Summary</h2>
                            </div>

                            <div class="px-6 py-4">
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium">{{ $currencySymbol }}{{ number_format($cart->subtotal ?? $cart->total, 2) }}</span>
                                </div>

                                <div class="flex justify-between py-2 border-t border-gray-200">
                                    <span class="text-gray-600">Shipping</span>
                                    <span class="font-medium">Calculated at checkout</span>
                                </div>

                                <div class="flex justify-between py-2 border-t border-gray-200">
                                    <span class="text-gray-600">Tax</span>
                                    @if(isset($cart->tax_amount) && $cart->tax_amount > 0)
                                        <span class="font-medium">{{ $currencySymbol }}{{ number_format($cart->tax_amount, 2) }}</span>
                                    @else
                                        <span class="font-medium">Calculated at checkout</span>
                                    @endif
                                </div>

                                <div class="flex justify-between py-2 border-t border-gray-200 text-lg font-bold">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-accent-custom">{{ $currencySymbol }}{{ number_format($cart->total, 2) }}</span>
                                </div>

                                <div class="mt-6">
                                    <a href="{{ route('storefront.checkout', $businessDetail->store_slug) }}" class="w-full flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-accent-custom hover:bg-opacity-90">
                                        Proceed to Checkout
                                    </a>
                                </div>

                                <div class="mt-4 text-center">
                                    <a href="{{ route('storefront.products', $businessDetail->store_slug) }}" class="text-sm font-medium text-primary-custom hover:text-secondary-custom">
                                        Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-storefront-layout>
