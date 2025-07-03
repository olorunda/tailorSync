<x-layouts.storefront :businessDetail="$businessDetail" :cart="$cart" :currencySymbol="$currencySymbol" title="Order Details">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Order #{{ $order->order_number }}</h2>
                        <a href="{{ route('storefront.orders', $businessDetail->store_slug) }}" class="inline-flex items-center text-sm text-primary-custom hover:text-secondary-custom">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Orders
                        </a>
                    </div>

                    <!-- Order Status -->
                    <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                            <div class="mb-4 md:mb-0">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Order Status</p>
                                <p class="text-lg font-semibold">
                                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                        @if($order->status == 'completed') bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400
                                        @elseif($order->status == 'processing') bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400
                                        @elseif($order->status == 'cancelled') bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-4 md:mb-0">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Order Date</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Payment Status</p>
                                <p class="text-zinc-900 dark:text-zinc-100">
                                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                        @if($order->payment_status == 'paid') bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400
                                        @elseif($order->payment_status == 'partial') bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400
                                        @else bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400
                                        @endif">
                                        {{ ucfirst($order->payment_status ?? 'unpaid') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Information -->
                    @if($order->tracking_number)
                        <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Tracking Information</h3>
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Tracking Number</p>
                                    <p class="text-zinc-900 dark:text-zinc-100">{{ $order->tracking_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Shipping Method</p>
                                    <p class="text-zinc-900 dark:text-zinc-100">{{ $order->shipping_method ?? 'Standard Shipping' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Order Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Order Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Product</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($order->orderItems as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $item->product->name ?? 'Unknown Product' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $currencySymbol }}{{ number_format($item->price, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $currencySymbol }}{{ number_format($item->total, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Order Summary</h3>
                        <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4">
                            <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                                <span class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($order->total_amount - ($order->shipping_cost ?? 0) - ($order->tax ?? 0), 2) }}</span>
                            </div>
                            @if($order->shipping_cost)
                                <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-zinc-600 dark:text-zinc-400">Shipping</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($order->shipping_cost, 2) }}</span>
                                </div>
                            @endif
                            @if($order->tax)
                                <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-zinc-600 dark:text-zinc-400">Tax</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($order->tax, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between py-2 font-semibold">
                                <span class="text-zinc-900 dark:text-zinc-100">Total</span>
                                <span class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    @if($order->shipping_address)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Shipping Address</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4">
                                <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-line">{{ $order->shipping_address }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($order->notes)
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Order Notes</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4">
                                <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-line">{{ $order->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.storefront>
