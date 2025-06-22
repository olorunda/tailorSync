<x-layouts.app :title="__('Store Order Details')">
    <div class="w-full">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Order Details</h1>
                <p class="text-zinc-600 dark:text-zinc-400">View and manage order information</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('store.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 border border-transparent rounded-md font-semibold text-xs text-zinc-900 dark:text-zinc-100 uppercase tracking-widest hover:bg-zinc-300 dark:hover:bg-zinc-600 active:bg-zinc-400 dark:active:bg-zinc-800 focus:outline-none focus:border-zinc-400 dark:focus:border-zinc-500 focus:ring ring-zinc-200 dark:focus:ring-zinc-600 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Back to Orders
                </a>
                @if($order->payment_status !== 'paid' && $order->status !== 'cancelled' && $order->payment_status !== 'cancelled')
                <form action="{{ route('store.orders.mark-as-paid', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mark as Paid
                    </button>
                </form>
                @endif
                @if(!in_array($order->status, ['delivered', 'cancelled']))
                <button type="button" onclick="openCancelModal()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel Order
                </button>
                @endif
                <a href="{{ route('store.orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:border-orange-900 focus:ring ring-orange-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Edit Order
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
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Order Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Order Number</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Status</p>
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($order->status == 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                        @elseif($order->status == 'processing') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                        @elseif($order->status == 'shipped') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                        @elseif($order->status == 'delivered') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                        @elseif($order->status == 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Payment Status</p>
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($order->payment_status == 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                        @elseif($order->payment_status == 'paid') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                        @elseif($order->payment_status == 'refunded') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                        @elseif($order->payment_status == 'partially_paid') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Customer Information</h3>
                        <div>
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Customer</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $order->client ? $order->client->name : 'Guest Customer' }}</p>
                        </div>
                        @if($order->client)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $order->client->email }}</p>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $order->client->phone }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Order Items</h3>
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
                            @foreach($order->products as $product)
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
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Order Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Shipping Information</h4>
                            @if($order->shipping_address)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $order->shipping_address }}</p>
                            @else
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">No shipping address provided</p>
                            @endif

                            <div class="mt-4">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Shipping Method</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $order->shipping_method ?? 'Standard Shipping' }}</p>
                            </div>

                            @if($order->tracking_number)
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Tracking Number</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $order->tracking_number }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="bg-zinc-50 dark:bg-zinc-700/50 rounded-lg p-4">
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Subtotal</span>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">${{ number_format($order->total_amount - ($order->shipping_cost + $order->tax), 2) }}</span>
                            </div>
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Shipping</span>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">${{ number_format($order->shipping_cost, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Tax</span>
                                <span class="text-zinc-900 dark:text-zinc-100 font-medium">${{ number_format($order->tax, 2) }}</span>
                            </div>
                            <div class="border-t border-zinc-200 dark:border-zinc-600 mt-2 pt-2">
                                <div class="flex justify-between py-2 text-base font-medium">
                                    <span class="text-zinc-900 dark:text-zinc-100">Total</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">${{ number_format($order->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($order->notes)
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Notes</h3>
                    <div class="bg-zinc-50 dark:bg-zinc-700/50 rounded-lg p-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $order->notes }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Cancel Order Confirmation Modal -->
    <div id="cancelOrderModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100" id="modal-title">
                                Cancel Order
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Are you sure you want to cancel this order? This action cannot be undone.
                                    The product quantities will be restored to inventory, and the payment status will be set to cancelled.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-zinc-50 dark:bg-zinc-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form action="{{ route('store.orders.cancel', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Yes, Cancel Order
                        </button>
                    </form>
                    <button type="button" onclick="closeCancelModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openCancelModal() {
            document.getElementById('cancelOrderModal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelOrderModal').classList.add('hidden');
        }
    </script>
</x-layouts.app>
