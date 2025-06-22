<x-storefront-layout :businessDetail="$businessDetail" :cart="$cart" title="Order Confirmation">
    <div class="py-8 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <svg class="h-24 w-24 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h1 class="text-3xl font-extrabold text-secondary-custom mb-2">Thank You for Your Order!</h1>
                <p class="text-lg text-gray-600">Your order has been received and is being processed.</p>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-secondary-custom">Order Details</h2>
                </div>

                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Order Number</h3>
                            <p class="text-base font-medium text-gray-900">{{ $order->order_number }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Date</h3>
                            <p class="text-base font-medium text-gray-900">{{ $order->created_at->format('F j, Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Total</h3>
                            <p class="text-base font-medium text-accent-custom">{{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Payment Status</h3>
                            <p class="text-base font-medium text-gray-900">{{ ucfirst($order->payment_status) }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-3">Order Items</h3>

                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($order->orderItems as $item)
                                <li class="py-4 flex">
                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                        @if($item->product->primary_image)
                                            <img src="{{ Storage::url($item->product->primary_image) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover object-center">
                                        @else
                                            <div class="h-full w-full flex items-center justify-center bg-gray-200">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ml-4 flex-1 flex flex-col">
                                        <div>
                                            <div class="flex justify-between text-sm font-medium text-gray-900">
                                                <h4>{{ $item->product->name }}</h4>
                                                <p class="ml-4">{{ $currencySymbol }}{{ number_format($item->total, 2) }}</p>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">Qty: {{ $item->quantity }} x {{ $currencySymbol }}{{ number_format($item->price, 2) }}</p>

                                            @if(!empty($item->options))
                                                <div class="mt-1 text-xs text-gray-500">
                                                    @foreach($item->options as $key => $value)
                                                        <span class="mr-2">{{ ucfirst($key) }}: {{ $value }}</span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if(!empty($item->custom_design_data))
                                                <div class="mt-1 text-xs text-primary-custom">
                                                    <span class="font-medium">Custom Design</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-3">Shipping Address</h3>
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $order->shipping_address }}</p>
                            </div>

                            @if($order->billing_address && $order->billing_address !== $order->shipping_address)
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-3">Billing Address</h3>
                                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $order->billing_address }}</p>
                                </div>
                            @endif

                            @if($order->notes)
                                <div class="md:col-span-2">
                                    <h3 class="text-sm font-medium text-gray-500 mb-3">Order Notes</h3>
                                    <p class="text-sm text-gray-900">{{ $order->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p class="text-gray-600 mb-6">A confirmation email has been sent to your email address.</p>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('storefront.index', $businessDetail->store_slug) }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-primary-custom hover:bg-secondary-custom">
                        Return to Home
                    </a>

                    <a href="{{ route('storefront.products', $businessDetail->store_slug) }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-storefront-layout>
