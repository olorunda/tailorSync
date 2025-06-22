<x-layouts.app :title="__('Edit Store Order')">
    <div class="w-full">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Order</h1>
                <p class="text-zinc-600 dark:text-zinc-400">Update order information and status</p>
            </div>
            <div>
                <a href="{{ route('store.orders.show', $order) }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 border border-transparent rounded-md font-semibold text-xs text-zinc-900 dark:text-zinc-100 uppercase tracking-widest hover:bg-zinc-300 dark:hover:bg-zinc-600 active:bg-zinc-400 dark:active:bg-zinc-800 focus:outline-none focus:border-zinc-400 dark:focus:border-zinc-500 focus:ring ring-zinc-200 dark:focus:ring-zinc-600 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Order
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <form action="{{ route('store.orders.update', $order) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Order Information</h3>

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Order Status</label>
                                <select id="status" name="status" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="payment_status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Payment Status</label>
                                <select id="payment_status" name="payment_status" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                    <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                    <option value="partially_paid" {{ $order->payment_status == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                </select>
                                @error('payment_status')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Shipping Information</h3>

                            <div class="mb-4">
                                <label for="shipping_method" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Shipping Method</label>
                                <input type="text" id="shipping_method" name="shipping_method" value="{{ $order->shipping_method }}" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                @error('shipping_method')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="tracking_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tracking Number</label>
                                <input type="text" id="tracking_number" name="tracking_number" value="{{ $order->tracking_number }}" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                @error('tracking_number')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Additional Information</h3>

                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Order Notes</label>
                            <textarea id="notes" name="notes" rows="4" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">{{ $order->notes }}</textarea>
                            @error('notes')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <a href="{{ route('store.orders.show', $order) }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Update Order
                        </button>
                    </div>
                </form>

                @if($order->payment_status !== 'paid')
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Quick Actions</h3>
                    <form action="{{ route('store.orders.mark-as-paid', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Mark as Paid
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
