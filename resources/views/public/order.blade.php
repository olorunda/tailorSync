<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Order #{{ $order->id ?? $invoice->id }} - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    <div class="min-h-screen">
        <header class="bg-white dark:bg-zinc-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="/">
                            <x-app-logo-icon class="block h-9 w-auto" />
                        </a>
                    </div>
                    @if($order)
                    <h1 class="ml-4 text-xl font-semibold">Order #{{ $order->id }}</h1>
                    @else

{{--                        <h1 class="ml-4 text-xl font-semibold">Invoice #{{ $order->id }}</h1>--}}
                    @endif
                </div>
                <div>
                    <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                        </svg>
                        Print
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @if($order)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Order Details Card -->
                <div class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Order Details</h2>

                        <div class="space-y-6">
                            <!-- Client Info -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 dark:text-orange-500 font-medium text-sm">{{ strtoupper(substr($order->client->name ?? 'NA', 0, 2)) }}</span>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Client</p>
                                    <p class="text-zinc-900 dark:text-zinc-100">
                                        {{ $order->client->name ?? 'No Client' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Description</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $order->description }}</p>
                            </div>

                            <!-- Design -->
                            @if ($order->design)
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Design</p>
                                    <p class="text-zinc-900 dark:text-zinc-100">
                                        {{ $order->design->name }}
                                    </p>
                                </div>
                            @endif

                            <!-- Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Created Date</p>
                                    <p class="text-zinc-900 dark:text-zinc-100">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Due Date</p>
                                    <p class="text-zinc-900 dark:text-zinc-100">
                                        @if ($order->due_date)
                                            {{ $order->due_date->format('M d, Y') }}
                                            @if ($order->due_date->isPast())
                                                <span class="text-red-600 dark:text-red-400 ml-2">Overdue</span>
                                            @endif
                                        @else
                                            Not set
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Financial Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Total Amount</p>
                                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $order->total_amount ? $currencySymbol . number_format($order->total_amount, 2) : 'Not set' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Status</p>
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        @if($order->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                        @elseif($order->status === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                        @elseif($order->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                        @elseif($order->status === 'ready') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                        @elseif($order->status === 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                        @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Order Status</h2>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">Current Status</p>
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($order->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                    @elseif($order->status === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                    @elseif($order->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                    @elseif($order->status === 'ready') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                    @elseif($order->status === 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                    @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items Section -->
            @if($order->orderItems->count() > 0 || $order->products->count() > 0)
            <div class="mt-6">
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Items Purchased</h2>

                        <div class="overflow-x-auto">
                            <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Item</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($order->orderItems as $item)
                                        <tr>
                                            <td class="px-4 py-4" data-label="Item">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $item->product ? $item->product->name : $item->name ?? 'Unknown Item' }}
                                                </div>
                                                @if($item->options)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                        Options: {{ is_array($item->options) ? implode(', ', $item->options) : $item->options }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" data-label="Quantity">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" data-label="Price">
                                                {{ $currencySymbol }}{{ number_format($item->price, 2) }}
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100" data-label="Total">
                                                {{ $currencySymbol }}{{ number_format($item->total ?? ($item->price * $item->quantity), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach($order->products as $product)
                                        @if(!$order->orderItems->contains('product_id', $product->id))
                                        <tr>
                                            <td class="px-4 py-4" data-label="Item">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $product->name }}
                                                </div>
                                                @if($product->pivot->options)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                        Options: {{ is_array($product->pivot->options) ? implode(', ', $product->pivot->options) : $product->pivot->options }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" data-label="Quantity">
                                                {{ $product->pivot->quantity }}
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" data-label="Price">
                                                {{ $currencySymbol }}{{ number_format($product->pivot->price, 2) }}
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100" data-label="Total">
                                                {{ $currencySymbol }}{{ number_format($product->pivot->total ?? ($product->pivot->price * $product->pivot->quantity), 2) }}
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endif
            @if ($invoice)
            <div class="mt-6">
                <!-- Invoice Details -->
                <div id="printable-invoice" class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between mb-8">
                            <div class="mb-4 md:mb-0">
                                <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mb-1">Invoice #{{ $invoice->invoice_number }}</h2>
                                @if ($invoice->description)
                                    <p class="text-zinc-600 dark:text-zinc-400">{{ $invoice->description }}</p>
                                @endif
                                <div class="mt-4 space-y-1">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        <span class="font-medium">Invoice Date:</span> {{ $invoice->invoice_date?->format('M d, Y') }}
                                    </p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        <span class="font-medium">Due Date:</span>
                                        <span class="{{ $invoice->due_date < now() && $invoice->status !== 'paid' ? 'text-red-600 dark:text-red-400' : '' }}">
                                            {{ $invoice->due_date?->format('M d, Y') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{$invoice->user->businessDetail->business_name}}</p>
                                    <p>{{ $invoice->user->businessDetail->business_address }}</p>
                                    {{--                                <p>{{ $invoice->user->businessDetail->city }}--}}
                                    {{--                                    , {{ $invoice->user->businessDetail->state }} {{ $invoice->user->businessDetail->postal_code }}</p>--}}
                                    <p>Phone: {{ $invoice->user->businessDetail->business_phone }}</p>
                                    <p>Email: {{ $invoice->user->businessDetail->business_email }}</p>

                                </div>
                                <div class="inline-block px-3 py-1 rounded-full
                                    {{ $invoice->status === 'paid' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : '' }}
                                    {{ $invoice->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : '' }}
                                    {{ $invoice->status === 'draft' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400' : '' }}
                                    {{ $invoice->status === 'cancelled' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400' : '' }}
                                ">
                                    {{ ucfirst($invoice->status) }}
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-b border-zinc-200 dark:border-zinc-700 py-6 mb-6">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-3">Bill To:</h3>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->client_name }}</p>
                            @if ($invoice->client_email)
                                <p class="text-zinc-600 dark:text-zinc-400">{{ $invoice->client_email }}</p>
                            @endif
                            @if ($invoice->client_address)
                                <p class="text-zinc-600 dark:text-zinc-400 whitespace-pre-line">{{ $invoice->client_address }}</p>
                            @endif
                        </div>

                        <!-- Invoice Items -->
                        <div class="overflow-x-auto">
                            <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Unit Price</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($invoice->items as $item)
                                        <tr>
                                            <td class="px-4 py-4" data-label="Description">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item['description'] }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" data-label="Quantity">
                                                {{ $item['quantity'] }}
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" data-label="Unit Price">
                                                {{ $currencySymbol }}{{ number_format($item['unit_price'], 2) }}
                                            </td>
                                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100" data-label="Amount">
                                                {{ $currencySymbol }}{{ number_format($item['quantity'] * $item['unit_price'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Invoice Totals -->
                        <div class="mt-6 flex justify-end">
                            <div class="w-full md:w-1/3 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Subtotal:</span>
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</span>
                                </div>

                                @if ($invoice->tax_rate > 0)
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Tax ({{ $invoice->tax_rate }}%):</span>
                                        <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</span>
                                    </div>
                                @endif

                                @if ($invoice->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Discount:</span>
                                        <span class="text-sm text-zinc-900 dark:text-zinc-100">-{{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                    <span class="text-base font-medium text-zinc-900 dark:text-zinc-100">Total:</span>
                                    <span class="text-base font-bold text-orange-600 dark:text-orange-500">{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($invoice->notes || $invoice->terms)
                            <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                                @if ($invoice->notes)
                                    <div class="mb-4">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Notes</h3>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-line">{{ $invoice->notes }}</p>
                                    </div>
                                @endif

                                @if ($invoice->terms)
                                    <div>
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Terms & Conditions</h3>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-line">{{ $invoice->terms }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </main>

        <footer class="bg-white dark:bg-zinc-800 shadow mt-6">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
