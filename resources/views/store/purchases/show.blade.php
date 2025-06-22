<x-layouts.app :title="__('Purchase Details')">
    <div class="w-full">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Purchase Details</h1>
                <p class="text-zinc-600 dark:text-zinc-400">View and manage purchase information</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('store.purchases.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 border border-transparent rounded-md font-semibold text-xs text-zinc-900 dark:text-zinc-100 uppercase tracking-widest hover:bg-zinc-300 dark:hover:bg-zinc-600 active:bg-zinc-400 dark:active:bg-zinc-800 focus:outline-none focus:border-zinc-400 dark:focus:border-zinc-500 focus:ring ring-zinc-200 dark:focus:ring-zinc-600 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Back to Purchases
                </a>
                <a href="{{ route('store.purchases.edit', $purchase) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:border-orange-900 focus:ring ring-orange-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Edit Purchase
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Purchase Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Purchase Number</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $purchase->purchase_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $purchase->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Status</p>
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($purchase->status == 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                        @elseif($purchase->status == 'processing') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                        @elseif($purchase->status == 'shipped') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                        @elseif($purchase->status == 'delivered') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                        @elseif($purchase->status == 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                        @endif">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Payment Status</p>
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($purchase->payment_status == 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                        @elseif($purchase->payment_status == 'paid') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                        @elseif($purchase->payment_status == 'refunded') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                        @elseif($purchase->payment_status == 'partially_paid') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $purchase->payment_status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Customer Information</h3>
                        <div>
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Customer Name</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $purchase->customer_name }}</p>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $purchase->customer_email ?: 'Not provided' }}</p>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $purchase->customer_phone ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Purchase Items</h3>
                <div class="overflow-x-auto">
                    <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Product
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Price
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($purchase->products as $product)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <td class="px-6 py-4 whitespace-nowrap" data-label="Product">
                                        <div class="flex items-center">
                                            @if($product->primary_image)
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-md object-cover" src="{{ Storage::url($product->primary_image) }}" alt="{{ $product->name }}">
                                                </div>
                                            @endif
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $product->name }}
                                                </div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    SKU: {{ $product->sku }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" data-label="Price">
                                        ${{ number_format($product->pivot->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" data-label="Quantity">
                                        {{ $product->pivot->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100" data-label="Total">
                                        ${{ number_format($product->pivot->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Purchase Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Shipping Information</h4>
                            @if($purchase->shipping_address)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    @if(is_array($purchase->shipping_address))
                                        {{ $purchase->shipping_address['address'] ?? '' }}<br>
                                        {{ $purchase->shipping_address['city'] ?? '' }}, {{ $purchase->shipping_address['state'] ?? '' }} {{ $purchase->shipping_address['zip'] ?? '' }}<br>
                                        {{ $purchase->shipping_address['country'] ?? '' }}
                                    @else
                                        {{ $purchase->shipping_address }}
                                    @endif
                                </p>
                            @else
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">No shipping address provided</p>
                            @endif

                            <div class="mt-4">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Shipping Method</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $purchase->shipping_method ?? 'Standard Shipping' }}</p>
                            </div>

                            @if($purchase->tracking_number)
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Tracking Number</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $purchase->tracking_number }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="bg-zinc-50 dark:bg-zinc-700/50 rounded-lg p-4">
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Subtotal</span>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">${{ number_format($purchase->total_amount - ($purchase->shipping_cost + $purchase->tax), 2) }}</span>
                            </div>
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Shipping</span>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">${{ number_format($purchase->shipping_cost, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Tax</span>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">${{ number_format($purchase->tax, 2) }}</span>
                            </div>
                            <div class="border-t border-zinc-200 dark:border-zinc-600 mt-2 pt-2">
                                <div class="flex justify-between py-2 text-base font-medium">
                                    <span class="text-zinc-900 dark:text-zinc-100">Total</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">${{ number_format($purchase->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($purchase->notes)
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Notes</h3>
                    <div class="bg-zinc-50 dark:bg-zinc-700/50 rounded-lg p-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $purchase->notes }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
