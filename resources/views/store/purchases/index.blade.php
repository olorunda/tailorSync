<x-layouts.app :title="__('Store Purchases')">
    <div class="w-full">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Store Purchases</h1>
                <p class="text-zinc-600 dark:text-zinc-400">Manage customer purchases and track orders</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Filter Purchases</h3>
            <form action="{{ route('store.purchases.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Purchase #, Customer Name or Email" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                    <x-simple-select
                        name="status"
                        id="status"
                        :options="[
                            ['id' => '', 'name' => 'All Statuses'],
                            ['id' => 'pending', 'name' => 'Pending', 'selected' => request('status') == 'pending'],
                            ['id' => 'processing', 'name' => 'Processing', 'selected' => request('status') == 'processing'],
                            ['id' => 'shipped', 'name' => 'Shipped', 'selected' => request('status') == 'shipped'],
                            ['id' => 'delivered', 'name' => 'Delivered', 'selected' => request('status') == 'delivered'],
                            ['id' => 'cancelled', 'name' => 'Cancelled', 'selected' => request('status') == 'cancelled']
                        ]"
                    />
                </div>
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Payment Status</label>
                    <x-simple-select
                        name="payment_status"
                        id="payment_status"
                        :options="[
                            ['id' => '', 'name' => 'All Payment Statuses'],
                            ['id' => 'paid', 'name' => 'Paid', 'selected' => request('payment_status') == 'paid'],
                            ['id' => 'pending', 'name' => 'Pending', 'selected' => request('payment_status') == 'pending'],
                            ['id' => 'failed', 'name' => 'Failed', 'selected' => request('payment_status') == 'failed'],
                            ['id' => 'refunded', 'name' => 'Refunded', 'selected' => request('payment_status') == 'refunded']
                        ]"
                    />
                </div>
                <div>
                    <label for="date_range" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Date Range</label>
                    <x-simple-select
                        name="date_range"
                        id="date_range"
                        :options="[
                            ['id' => '', 'name' => 'All Dates'],
                            ['id' => 'today', 'name' => 'Today', 'selected' => request('date_range') == 'today'],
                            ['id' => 'this_week', 'name' => 'This Week', 'selected' => request('date_range') == 'this_week'],
                            ['id' => 'this_month', 'name' => 'This Month', 'selected' => request('date_range') == 'this_month'],
                            ['id' => 'last_month', 'name' => 'Last Month', 'selected' => request('date_range') == 'last_month'],
                            ['id' => 'this_year', 'name' => 'This Year', 'selected' => request('date_range') == 'this_year']
                        ]"
                    />
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Payment Method</label>
                    <x-simple-select
                        name="payment_method"
                        id="payment_method"
                        :options="[
                            ['id' => '', 'name' => 'All Payment Methods'],
                            ['id' => 'credit_card', 'name' => 'Credit Card', 'selected' => request('payment_method') == 'credit_card'],
                            ['id' => 'paypal', 'name' => 'PayPal', 'selected' => request('payment_method') == 'paypal'],
                            ['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'selected' => request('payment_method') == 'bank_transfer'],
                            ['id' => 'cash', 'name' => 'Cash', 'selected' => request('payment_method') == 'cash']
                        ]"
                    />
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sort By</label>
                    <x-simple-select
                        name="sort"
                        id="sort"
                        :options="[
                            ['id' => 'newest', 'name' => 'Newest First', 'selected' => request('sort') == 'newest' || !request('sort')],
                            ['id' => 'oldest', 'name' => 'Oldest First', 'selected' => request('sort') == 'oldest'],
                            ['id' => 'amount_high', 'name' => 'Amount (High to Low)', 'selected' => request('sort') == 'amount_high'],
                            ['id' => 'amount_low', 'name' => 'Amount (Low to High)', 'selected' => request('sort') == 'amount_low']
                        ]"
                    />
                </div>
                <div class="md:col-span-3 flex justify-end space-x-3">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('store.purchases.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        @if($purchases->isEmpty())
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-12 text-center">
                <div class="flex-shrink-0 flex justify-center mb-4">
                    <svg class="h-16 w-16 text-zinc-400 dark:text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No purchases found</h3>
                <p class="text-zinc-500 dark:text-zinc-400 mb-6">When customers make purchases in your store, they will appear here.</p>
            </div>
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Customer Purchases</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Purchase Number
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Payment
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Total
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($purchases as $purchase)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <td class="px-6 py-4 whitespace-nowrap" data-label="Purchase Number">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $purchase->purchase_number }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-label="Customer">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $purchase->customer_name }}
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $purchase->customer_email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-label="Date">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $purchase->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $purchase->created_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-label="Status">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($purchase->status == 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                            @elseif($purchase->status == 'processing') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                            @elseif($purchase->status == 'shipped') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                            @elseif($purchase->status == 'delivered') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                            @elseif($purchase->status == 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                            @endif">
                                            {{ ucfirst($purchase->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-label="Payment">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($purchase->payment_status == 'paid') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                            @elseif($purchase->payment_status == 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                            @elseif($purchase->payment_status == 'failed') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                            @elseif($purchase->payment_status == 'refunded') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                            @endif">
                                            {{ ucfirst($purchase->payment_status) }}
                                        </span>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                            {{ ucfirst(str_replace('_', ' ', $purchase->payment_method)) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100" data-label="Total">
                                        ${{ number_format($purchase->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Actions">
                                        <a href="{{ route('store.purchases.show', $purchase) }}" class="text-orange-600 dark:text-orange-400 hover:text-orange-900 dark:hover:text-orange-300 mr-3">View</a>
                                        <a href="{{ route('store.purchases.edit', $purchase) }}" class="text-orange-600 dark:text-orange-400 hover:text-orange-900 dark:hover:text-orange-300">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $purchases->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
