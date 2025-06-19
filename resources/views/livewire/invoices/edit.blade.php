<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;

new class extends Component {
    public Invoice $invoice;
    public string $invoice_number = '';
    public string $client_name = '';
    public string $client_email = '';
    public string $client_address = '';
    public ?string $client_id = null;
    public ?string $order_id = null;
    public string $invoice_date = '';
    public string $due_date = '';
    public string $status = '';
    public string $description = '';
    public array $items = [];
    public float $subtotal = 0;
    public float $tax_rate = 0;
    public float $tax_amount = 0;
    public float $discount_amount = 0;
    public float $total_amount = 0;
    public string $notes = '';
    public string $terms = '';

    public function mount(Invoice $invoice)
    {

        if (!in_array($invoice->user_id,[Auth::id(),Auth::user()->parent_id])) {
            return $this->redirect(route('invoices.index'));
        }

        $this->invoice = $invoice;
        $this->invoice_number = $invoice->invoice_number;
        $this->client_name = $invoice->client_name ?? '';
        $this->client_email = $invoice->client_email ?? '';
        $this->client_address = $invoice->client_address ?? '';
        $this->client_id = $invoice->client_id;
        $this->order_id = $invoice->order_id;
        $this->invoice_date = $invoice->invoice_date?->format('Y-m-d') ?? '';
        $this->due_date = $invoice->due_date?->format('Y-m-d') ?? '';
        $this->status = $invoice->status;
        $this->description = $invoice->description ?? '';
        $this->items = $invoice->items ?? [];
        $this->subtotal = $invoice->subtotal ?? 0;
        $this->tax_rate = $invoice->tax_rate ?? 0;
        $this->tax_amount = $invoice->tax_amount ?? 0;
        $this->discount_amount = $invoice->discount_amount ?? 0;
        $this->total_amount = $invoice->total_amount ?? 0;
        $this->notes = $invoice->notes ?? '';
        $this->terms = $invoice->terms ?? '';
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'amount' => 0,
        ];

        // Ensure tax_rate and discount_amount are initialized
        if (!isset($this->tax_rate)) {
            $this->tax_rate = 0;
        }

        if (!isset($this->discount_amount)) {
            $this->discount_amount = 0;
        }

        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);

            // Ensure tax_rate and discount_amount are initialized
            if (!isset($this->tax_rate)) {
                $this->tax_rate = 0;
            }

            if (!isset($this->discount_amount)) {
                $this->discount_amount = 0;
            }

            $this->calculateTotals();
        }
    }

    public function updatedItems()
    {
        foreach ($this->items as $index => $item) {
            $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
            $unit_price = isset($item['unit_price']) ? $item['unit_price'] : 0;
            $this->items[$index]['amount'] = $quantity * $unit_price;
        }

        // Ensure properties are properly initialized
        if (!isset($this->tax_rate)) {
            $this->tax_rate = 0;
        }

        if (!isset($this->discount_amount)) {
            $this->discount_amount = 0;
        }

        $this->calculateTotals();
    }

    public function updatedTaxRate()
    {
        // Ensure tax_rate is properly initialized
        if (!isset($this->tax_rate)) {
            $this->tax_rate = 0;
        }

        $this->calculateTotals();
    }

    public function updatedDiscountAmount()
    {
        // Ensure discount_amount is properly initialized
        if (!isset($this->discount_amount)) {
            $this->discount_amount = 0;
        }

        // Ensure tax_rate is properly initialized as well
        if (!isset($this->tax_rate)) {
            $this->tax_rate = 0;
        }

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // Initialize subtotal
        $this->subtotal = 0;

        // Calculate subtotal from items if they exist
        if (isset($this->items) && is_array($this->items)) {
            $this->subtotal = array_sum(array_column($this->items, 'amount'));
        }

        // Ensure tax_rate is properly initialized
        if (!isset($this->tax_rate)) {
            $this->tax_rate = 0;
        }

        // Ensure discount_amount is properly initialized
        if (!isset($this->discount_amount)) {
            $this->discount_amount = 0;
        }

        // Calculate tax amount and total
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
    }

    public function selectClient($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $this->client_id = $client->id;
            $this->client_name = $client->name;
            $this->client_email = $client->email ?? '';
            $this->client_address = $client->address ?? '';

            // Ensure tax_rate and discount_amount are initialized
            if (!isset($this->tax_rate)) {
                $this->tax_rate = 0;
            }

            if (!isset($this->discount_amount)) {
                $this->discount_amount = 0;
            }
        }
    }

    public function selectOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order && $order->client) {
            // Check if an invoice already exists for this order
            $existingInvoice = Invoice::where('order_id', $order->id)
                ->where('user_id', Auth::id())
                ->where('id', '!=', $this->invoice->id) // Exclude current invoice
                ->first();

            if ($existingInvoice) {
                // Redirect to the existing invoice
                session()->flash('error', 'An invoice already exists for this order. You are being redirected to view it.');
                return $this->redirect(route('invoices.show', $existingInvoice));
            }

            $this->order_id = $order->id;
            $this->selectClient($order->client_id);

            // Clear existing items
            $this->items = [];

            // Add order items
            if ($order->items && count($order->items) > 0) {
                foreach ($order->items as $orderItem) {
                    $this->items[] = [
                        'description' => $orderItem['description'] ?? 'Order item',
                        'quantity' => $orderItem['quantity'] ?? 1,
                        'unit_price' => $orderItem['price'] ?? 0,
                        'amount' => ($orderItem['quantity'] ?? 1) * ($orderItem['price'] ?? 0),
                    ];
                }
            } else {
                // Add a default item for the order
                $this->items[] = [
                    'description' => 'Order #' . $order->order_number,
                    'quantity' => 1,
                    'unit_price' => $order->total_amount ?? 0,
                    'amount' => $order->total_amount ?? 0,
                ];
            }

            $this->calculateTotals();
        }
    }

    public function save()
    {
        // Check if the invoice was previously paid
        $wasPaid = $this->invoice->status === 'paid';

        // If the invoice was paid and is still paid, prevent editing
        if ($wasPaid && $this->status === 'paid') {
            session()->flash('error', 'Paid invoices cannot be edited. Change the status to pending first if you need to make changes.');
            return $this->redirect(route('invoices.show', $this->invoice));
        }

        // If the invoice was paid but status is being changed to pending, only allow status change
        if ($wasPaid && $this->status === 'pending') {
            $this->invoice->status = 'pending';
            $this->invoice->save();

            session()->flash('status', 'Invoice status updated to pending.');
            return $this->redirect(route('invoices.show', $this->invoice));
        }

        // Check if the order_id has changed and if an invoice already exists for the new order
        if ($this->order_id && $this->order_id != $this->invoice->order_id) {
            $existingInvoice = Invoice::where('order_id', $this->order_id)
                ->where('user_id', Auth::id())
                ->where('id', '!=', $this->invoice->id)
                ->first();

            if ($existingInvoice) {
                // Redirect to the existing invoice
                session()->flash('error', 'An invoice already exists for this order. You are being redirected to view it.');
                return $this->redirect(route('invoices.show', $existingInvoice));
            }
        }

        $validated = $this->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,' . $this->invoice->id,
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_address' => 'nullable|string',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'status' => 'required|string|in:draft,pending,paid,cancelled',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $this->invoice->invoice_number = $validated['invoice_number'];
        $this->invoice->client_id = $this->client_id;
        $this->invoice->order_id = $this->order_id;
        $this->invoice->issue_date = $validated['invoice_date'];
        $this->invoice->due_date = $validated['due_date'];
        $this->invoice->status = $validated['status'];
        $this->invoice->description = $validated['description'];
        $this->invoice->items = $validated['items'];
        $this->invoice->subtotal = $validated['subtotal'];
        $this->invoice->tax_rate = $validated['tax_rate'];
        $this->invoice->tax_amount = $validated['tax_amount'];
        $this->invoice->discount = $validated['discount_amount'];
        $this->invoice->total = $validated['total_amount'];
        $this->invoice->notes = $validated['notes'];
        $this->invoice->terms = $validated['terms'];

        // Store client data in the client_data JSON column
        $this->invoice->client_data = [
            'name' => $validated['client_name'],
            'email' => $validated['client_email'],
            'address' => $validated['client_address'],
        ];
        $this->invoice->save();

        session()->flash('status', 'Invoice updated successfully!');
        $this->redirect(route('invoices.show', $this->invoice));
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center h-96">
            <div class="flex flex-col items-center gap-2">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-orange-600"></div>
                <span class="text-orange-600 text-lg">Loading...</span>
            </div>
        </div>
        HTML;
    }

    public function with(): array
    {
        return [
            'clients' => Auth::user()->allClients()
                ->orderBy('name')
                ->get(),
            'orders' => Auth::user()->allOrders()
                ->whereNotNull('client_id')
                ->orderBy('created_at', 'desc')
                ->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Invoice</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Update invoice #{{ $invoice->invoice_number }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Invoice
            </a>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        @if ($invoice->status === 'paid')
        <div class="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700 dark:text-yellow-200">
                        This invoice is marked as paid. You can only change its status to pending. To make other changes, first change the status to pending.
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Invoice Information -->
                <div>
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Invoice Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Invoice Number <span class="text-red-500">*</span></label>
                            <input wire:model="invoice_number" type="text" id="invoice_number" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required {{ $invoice->status === 'paid' ? 'disabled' : '' }}>
                            @error('invoice_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
                            <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="invoice_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Invoice Date <span class="text-red-500">*</span></label>
                                <input wire:model="invoice_date" type="date" id="invoice_date" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required {{ $invoice->status === 'paid' ? 'disabled' : '' }}>
                                @error('invoice_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Due Date <span class="text-red-500">*</span></label>
                                <input wire:model="due_date" type="date" id="due_date" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required {{ $invoice->status === 'paid' ? 'disabled' : '' }}>
                                @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                            <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" {{ $invoice->status === 'paid' ? 'disabled' : '' }}></textarea>
                            @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div>
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Client Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Select Client</label>
                            <select wire:change="selectClient($event.target.value)" id="client_id" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" {{ $invoice->status === 'paid' ? 'disabled' : '' }}>
                                <option value="">Select a client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ $client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="order_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Select Order (Optional)</label>
                            <select wire:change="selectOrder($event.target.value)" id="order_id" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" {{ $invoice->status === 'paid' ? 'disabled' : '' }}>
                                <option value="">Select an order</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}" {{ $order_id == $order->id ? 'selected' : '' }}>{{ $order->order_number }} - {{ $order->client->name ?? 'Unknown' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="client_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Client Name <span class="text-red-500">*</span></label>
                            <input wire:model="client_name" type="text" id="client_name" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required {{ $invoice->status === 'paid' ? 'disabled' : '' }}>
                            @error('client_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="client_email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Client Email</label>
                            <input wire:model="client_email" type="email" id="client_email" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" {{ $invoice->status === 'paid' ? 'disabled' : '' }}>
                            @error('client_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="client_address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Client Address</label>
                            <textarea wire:model="client_address" id="client_address" rows="2" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" {{ $invoice->status === 'paid' ? 'disabled' : '' }}></textarea>
                            @error('client_address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Invoice Items</h2>

            <div class="overflow-x-auto">
                <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider w-24">Quantity</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider w-32">Unit Price</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider w-32">Amount</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider w-16">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($items as $index => $item)
                            <tr>
                                <td class="px-4 py-2" data-label="Description">
                                    <input
                                        wire:model="items.{{ $index }}.description"
                                        type="text"
                                        class="block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        placeholder="Item description"
                                        required
                                        {{ $invoice->status === 'paid' ? 'disabled' : '' }}
                                    >
                                    @error("items.{$index}.description") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2" data-label="Quantity">
                                    <input
                                        wire:model="items.{{ $index }}.quantity"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        class="block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        required
                                        {{ $invoice->status === 'paid' ? 'disabled' : '' }}
                                    >
                                    @error("items.{$index}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2" data-label="Unit Price">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">$</span>
                                        </div>
                                        <input
                                            wire:model="items.{{ $index }}.unit_price"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="pl-7 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            required
                                            {{ $invoice->status === 'paid' ? 'disabled' : '' }}
                                        >
                                    </div>
                                    @error("items.{$index}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2" data-label="Amount">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">$</span>
                                        </div>
                                        <input
                                            type="number"
                                            value="{{ $item['quantity'] * $item['unit_price'] }}"
                                            class="pl-7 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm bg-zinc-50 dark:bg-zinc-800"
                                            readonly
                                        >
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right" data-label="Action">
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $index }})"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        {{ count($items) <= 1 || $invoice->status === 'paid' ? 'disabled' : '' }}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button
                    type="button"
                    wire:click="addItem"
                    class="inline-flex items-center px-3 py-2 border border-zinc-300 dark:border-zinc-600 shadow-sm text-sm leading-4 font-medium rounded-md text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    {{ $invoice->status === 'paid' ? 'disabled' : '' }}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Item
                </button>
            </div>

            <div class="mt-6 flex justify-end">
                <div class="w-full md:w-1/3 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Subtotal:</span>
                        <span class="text-sm text-zinc-900 dark:text-zinc-100">${{ number_format($subtotal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="tax_rate" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Tax Rate (%):</label>
                        <div class="w-24">
                            <input
                                wire:model.live="tax_rate"
                                type="number"
                                min="0"
                                step="0.01"
                                id="tax_rate"
                                class="block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                {{ $invoice->status === 'paid' ? 'disabled' : '' }}
                            >
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Tax Amount:</span>
                        <span class="text-sm text-zinc-900 dark:text-zinc-100">${{ number_format($tax_amount, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="discount_amount" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Discount:</label>
                        <div class="w-24 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">$</span>
                            </div>
                            <input
                                wire:model.live="discount_amount"
                                type="number"
                                min="0"
                                step="0.01"
                                id="discount_amount"
                                class="pl-7 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                {{ $invoice->status === 'paid' ? 'disabled' : '' }}
                            >
                        </div>
                    </div>

                    <div class="flex justify-between pt-3 border-t border-zinc-200 dark:border-zinc-700">
                        <span class="text-base font-medium text-zinc-900 dark:text-zinc-100">Total:</span>
                        <span class="text-base font-bold text-orange-600 dark:text-orange-500">${{ number_format($total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Additional Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                    <textarea wire:model="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" placeholder="Notes visible to client" {{ $invoice->status === 'paid' ? 'disabled' : '' }}></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="terms" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Terms & Conditions</label>
                    <textarea wire:model="terms" id="terms" rows="3" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" placeholder="Payment terms and conditions" {{ $invoice->status === 'paid' ? 'disabled' : '' }}></textarea>
                    @error('terms') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex justify-center py-2 px-4 border border-zinc-300 dark:border-zinc-600 shadow-sm text-sm font-medium rounded-md text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 mr-3">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                Save Changes
            </button>
        </div>
    </form>
</div>
