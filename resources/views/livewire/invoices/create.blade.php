<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\BusinessDetail;
use App\Services\TaxService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $invoice_number = '';
    public string $client_name = '';
    public string $client_email = '';
    public string $client_address = '';
    public ?string $client_id = null;
    public ?string $order_id = null;
    public string $invoice_date = '';
    public string $due_date = '';
    public string $status = 'draft';
    public string $description = '';
    public array $items = [];
    public float $subtotal = 0;
    public float $tax_rate = 0;
    public float $tax_amount = 0;
    public float $discount_amount = 0;
    public float $total_amount = 0;
    public string $notes = '';
    public string $terms = '';
    public $businessDetail = null;
    public $taxEnabled = false;
    public $taxCountry = 'none';

    public function mount()
    {
        // Set default dates
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');

        // Generate invoice number
        $latestInvoice = Auth::user()->allInvoices()->latest()->first();
        $nextInvoiceNumber = $latestInvoice ? (intval(substr($latestInvoice->invoice_number, 3)) + 1) : 1;
        $this->invoice_number = 'INV' . str_pad($nextInvoiceNumber, 5, '0', STR_PAD_LEFT);

        // Get business details and tax settings
        $this->businessDetail = Auth::user()->businessDetail;
        if ($this->businessDetail) {
            $this->taxEnabled = $this->businessDetail->tax_enabled;
            $this->taxCountry = $this->businessDetail->tax_country;

            // Set default tax rate based on business settings if tax is enabled
            if ($this->taxEnabled && $this->taxCountry !== 'none') {
                // We'll calculate the actual tax in calculateTotals()
                // Just initialize with a default for now
                $this->tax_rate = 0;
            } else {
                // Use default tax rate if tax is not enabled
                $this->tax_rate = 7.5;
            }
        } else {
            // Use default tax rate if no business details
            $this->tax_rate = 7.5;
        }

        // Initialize properties with default values
        $this->discount_amount = 0;
        $this->subtotal = 0;
        $this->tax_amount = 0;
        $this->total_amount = 0;
        $this->items = [];
        $this->client_id = null;
        $this->order_id = null;
        $this->client_name = '';
        $this->client_email = '';
        $this->client_address = '';
        $this->description = '';
        $this->status = 'draft';
        $this->notes = '';

        // Add first empty item
        $this->addItem();

        // Set default terms
        $this->terms = 'Payment is due within 30 days. Thank you for your business.';

        // Check if order_id is provided in the query parameters
        $orderId = request()->query('order_id');
        if ($orderId) {
            $this->selectOrder($orderId);
        }
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
            $this->items[$index]['amount'] = $item['quantity'] * $item['unit_price'];
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

        // Calculate tax amount based on business tax settings if enabled
        if ($this->businessDetail && $this->taxEnabled && $this->taxCountry !== 'none') {
            // Create a temporary invoice object to calculate tax
            $tempInvoice = new Invoice();
            $tempInvoice->subtotal = $this->subtotal;

            // Use TaxService to calculate tax
            $taxService = new TaxService($this->businessDetail);
            $taxResult = $taxService->calculateInvoiceTax($tempInvoice);

            // Update tax rate and amount from the calculation
            $this->tax_rate = $taxResult['tax_rate'];
            $this->tax_amount = $taxResult['tax_amount'];
        } else {
            // Use manual tax rate if tax settings are not enabled
            $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        }

        // Calculate total amount
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

    public function save()
    {
        // Ensure properties are properly initialized
        if (!isset($this->tax_rate)) {
            $this->tax_rate = 0;
        }

        if (!isset($this->discount_amount)) {
            $this->discount_amount = 0;
        }

        // Check if an order is selected and if an invoice already exists for this order
        if ($this->order_id) {
            $existingInvoice = Invoice::where('order_id', $this->order_id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingInvoice) {
                // Redirect to the existing invoice
                session()->flash('error', 'An invoice already exists for this order. You are being redirected to view it.');
                return $this->redirect(route('invoices.show', $existingInvoice));
            }
        }

        $validated = $this->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number',
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

        try {
            $invoice = new Invoice();
            $invoice->user_id = Auth::id();
            $invoice->client_id = $this->client_id;
            $invoice->order_id = $this->order_id;
            $invoice->invoice_number = $validated['invoice_number'];

            // Map component fields to database fields
            $invoice->issue_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            $invoice->status = $validated['status'];
            $invoice->notes = $validated['notes'];

            // Financial fields
            $invoice->subtotal = $validated['subtotal'];
            $invoice->tax_rate = $validated['tax_rate'];
            $invoice->tax_amount = $validated['tax_amount'];
            $invoice->discount = $validated['discount_amount'];
            $invoice->total = $validated['total_amount'];

            // Store additional data as JSON if needed
            $invoice->client_data = [
                'name' => $validated['client_name'],
                'email' => $validated['client_email'],
                'address' => $validated['client_address'],
            ];

            $invoice->items = $validated['items'];
            $invoice->terms = $validated['terms'];
            $invoice->description = $validated['description'];

            $invoice->save();

            \Log::info('Invoice created successfully', ['id' => $invoice->id]);
            session()->flash('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            \Log::error('Error creating invoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error creating invoice: ' . $e->getMessage());
            return;
        }

        session()->flash('status', 'Invoice created successfully!');
        $this->redirect(route('invoices.show', $invoice));
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
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Create Invoice</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Generate a new invoice for your client</p>
    </div>

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <!-- Invoice and Client Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Invoice Information -->
                <div>
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Invoice Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Invoice Number <span class="text-red-500">*</span></label>
                            <input wire:model="invoice_number" type="text" id="invoice_number" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                            @error('invoice_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status <span class="text-red-500">*</span></label>
                            <select wire:model="status" id="status" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="invoice_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Invoice Date <span class="text-red-500">*</span></label>
                                <input wire:model="invoice_date" type="date" id="invoice_date" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                                @error('invoice_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Due Date <span class="text-red-500">*</span></label>
                                <input wire:model="due_date" type="date" id="due_date" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                                @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                            <textarea wire:model="description" id="description" rows="2" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                            @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div>
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Client Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Select Client</label>
                            <select wire:change="selectClient($event.target.value)" id="client_id" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                <option value="">Select a client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="order_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Select Order (Optional)</label>
                            <select wire:change="selectOrder($event.target.value)" id="order_id" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                <option value="">Select an order</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}">{{ $order->order_number }} - {{ $order->client->name ?? 'Unknown' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="client_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Client Name <span class="text-red-500">*</span></label>
                            <input wire:model="client_name" type="text" id="client_name" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                            @error('client_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="client_email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Client Email</label>
                            <input wire:model="client_email" type="email" id="client_email" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('client_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="client_address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Client Address</label>
                            <textarea wire:model="client_address" id="client_address" rows="2" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                            @error('client_address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="mt-6">
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
                                            class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                            placeholder="Item description"
                                            required
                                        >
                                        @error("items.{$index}.description") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-2" data-label="Quantity">
                                        <input
                                            wire:model="items.{{ $index }}.quantity"
                                            wire:keyup="calculateTotals"
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                            required
                                        >
                                        @error("items.{$index}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-2" data-label="Unit Price">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">{{ Auth::user()->getCurrencySymbol() }}</span>
                                            </div>
                                            <input
                                                wire:model="items.{{ $index }}.unit_price"
                                                type="number"
                                                wire:keyup="calculateTotals"
                                                min="0"
                                                step="0.01"
                                                class="pl-7 bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                                required
                                            >
                                        </div>
                                        @error("items.{$index}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-2" data-label="Amount">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">{{ Auth::user()->getCurrencySymbol() }}</span>
                                            </div>
                                            <input
                                                type="number"
                                                value="{{ $item['quantity'] * $item['unit_price'] }}"
                                                class="pl-7 bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                                readonly
                                            >
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-right" data-label="Action">
                                        <button
                                            type="button"
                                            wire:click="removeItem({{ $index }})"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            {{ count($items) <= 1 ? 'disabled' : '' }}
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
                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="tax_rate" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Tax Rate (%):
                                @if($taxEnabled && $taxCountry !== 'none')
                                    <span class="text-xs text-orange-600 dark:text-orange-400 ml-1">(Auto-calculated)</span>
                                @endif
                            </label>
                            <div class="w-24">
                                <input
                                    wire:model.live.debounce.500ms="tax_rate"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    id="tax_rate"
                                    class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                    @if($taxEnabled && $taxCountry !== 'none') readonly @endif
                                >
                            </div>
                        </div>
                        @if($taxEnabled && $taxCountry !== 'none')
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 italic">
                                Tax is automatically calculated based on your {{ ucfirst($taxCountry) }} tax settings.
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Tax Amount:</span>
                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($tax_amount, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="discount_amount" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Discount:</label>
                            <div class="w-24 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">{{ Auth::user()->getCurrencySymbol() }}</span>
                                </div>
                                <input
                                    wire:model.live.debounce.500ms="discount_amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    id="discount_amount"
                                    class="pl-7 bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                >
                            </div>
                        </div>

                        <div class="flex justify-between pt-3 border-t border-zinc-200 dark:border-zinc-700">
                            <span class="text-base font-medium text-zinc-900 dark:text-zinc-100">Total:</span>
                            <span class="text-base font-bold text-orange-600 dark:text-orange-500">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-6">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Additional Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                        <textarea wire:model="notes" id="notes" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="Notes visible to client"></textarea>
                        @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="terms" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Terms & Conditions</label>
                        <textarea wire:model="terms" id="terms" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="Payment terms and conditions"></textarea>
                        @error('terms') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-zinc-200 dark:border-zinc-700 mt-6">
                <a href="{{ route('invoices.index') }}" class="inline-flex justify-center py-2 px-4 border border-zinc-300 dark:border-zinc-600 shadow-sm text-sm font-medium rounded-md text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 mr-3">
                    Cancel
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Create Invoice
                </button>
            </div>
        </form>
    </div>
</div>
