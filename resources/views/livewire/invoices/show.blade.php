<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Notifications\InvoiceEmailNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        if (!array_intersect([$invoice->user->parent_id,$invoice->user_id],[Auth::id(),Auth::user()->parent_id]) && !in_array($invoice->user_id,[Auth::id(),Auth::user()->parent_id])) {
            return $this->redirect(route('invoices.index'));
        }

        $this->invoice = $invoice;
    }

    public function markAsPaid()
    {
        $this->invoice->status = 'paid';
        $this->invoice->save();

        // Create a payment record for this invoice
        \App\Models\Payment::updateOrCreate(['invoice_id' => $this->invoice->id],[
            'user_id' => $this->invoice->user_id,
            'client_id' => $this->invoice->client_id,
            'invoice_id' => $this->invoice->id,
            'order_id' => $this->invoice->order_id,
            'amount' => $this->invoice->total,
            'payment_date' => now(),
            'payment_method' => 'bank_transfer',
            'description' => 'Payment for Invoice #' . $this->invoice->invoice_number,
            'status' => 'completed',
        ]);

        session()->flash('status', 'Invoice marked as paid successfully!');
        $this->redirect(route('invoices.show', $this->invoice));
    }

    public function markAsPending()
    {
        $this->invoice->status = 'pending';
        $this->invoice->save();

        session()->flash('status', 'Invoice marked as pending successfully!');
        $this->redirect(route('invoices.show', $this->invoice));
    }

    public function cancelInvoice()
    {
        $this->invoice->status = 'cancelled';
        $this->invoice->save();

        session()->flash('status', 'Invoice cancelled successfully!');
        $this->redirect(route('invoices.show', $this->invoice));
    }

    public function delete()
    {
        // Prevent deletion of paid invoices
        if ($this->invoice->status === 'paid') {
            session()->flash('error', 'Paid invoices cannot be deleted. Change the status to pending first if you need to delete this invoice.');
            return;
        }

        $this->invoice->delete();

        session()->flash('status', 'Invoice deleted successfully!');
        $this->redirect(route('invoices.index'));
    }

    public function emailToClient()
    {
        // Check if client email exists
        if (!$this->invoice->client_email) {
            session()->flash('error', 'Client email address is not available. Please update the client information first.');
            return;
        }

        try {
            // Get the client
            $client = Client::find($this->invoice->client_id);

            if ($client) {
                // Send notification to the client
                $client->notify(new InvoiceEmailNotification($this->invoice));

                session()->flash('status', 'Invoice has been emailed to the client successfully!');
            } else {
                session()->flash('error', 'Client not found. Please check the invoice details.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send email: ' . $e->getMessage());
        }
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
}; ?>

<div class="w-full">
    @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                              clip-rule="evenodd"/>
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
    @if (session()->has('status'))
        <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-200">
                        {{ session('status') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Invoice #{{ $invoice->invoice_number }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                Created on {{ $invoice->created_at->format('M d, Y') }} |
                Status:
                @if ($invoice->status === 'paid')
                    <span class="text-green-600 dark:text-green-400 font-medium">Paid</span>
                @elseif ($invoice->status === 'pending')
                    <span class="text-yellow-600 dark:text-yellow-400 font-medium">Pending</span>
                @elseif ($invoice->status === 'draft')
                    <span class="text-blue-600 dark:text-blue-400 font-medium">Draft</span>
                @elseif ($invoice->status === 'cancelled')
                    <span class="text-zinc-500 dark:text-zinc-400 font-medium">Cancelled</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
            <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                {{ $invoice->status === 'paid' ? 'Change Status' : 'Edit' }}
            </a>
        </div>
    </div>

    <!-- Invoice Actions -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6 mb-6">
        <div class="flex flex-wrap gap-3">
            @if ($invoice->status !== 'paid')
                <button
                    wire:click="markAsPaid"
                    wire:confirm="Are you sure you want to mark this invoice as paid?"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Mark as Paid
                </button>
            @endif

            @if ($invoice->status !== 'pending' && $invoice->status !== 'cancelled')
                <button
                    wire:click="markAsPending"
                    wire:confirm="Are you sure you want to mark this invoice as pending?"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md text-sm font-medium transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    Mark as Pending
                </button>
            @endif

            @if ($invoice->status === 'pending' && $invoice->user->businessDetail && $invoice->user->businessDetail->payment_enabled)
                <a
                    href="{{ route('payment.invoice.pay', $invoice->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                    Pay Now
                </a>
            @endif

            @if ($invoice->status !== 'cancelled')
                <button
                    wire:click="cancelInvoice"
                    wire:confirm="Are you sure you want to cancel this invoice?"
                    class="inline-flex items-center px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white rounded-md text-sm font-medium transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Cancel Invoice
                </button>
            @endif

            @if ($invoice->status !== 'paid')
            <button
                wire:click="delete"
                wire:confirm="Are you sure you want to delete this invoice? This action cannot be undone."
                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete
            </button>
            @endif

            <a
                href="#"
                onclick="window.print(); return false;"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                </svg>
                Print
            </a>

            <button
                wire:click="emailToClient"
                wire:confirm="Are you sure you want to email this invoice to the client?"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
                Email to Client
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Invoice Details -->
        <div class="md:col-span-2 space-y-6">
            <!-- Invoice Information -->
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
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->user->businessDetail->business_name }}</p>
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
                                            {{ Auth::user()->getCurrencySymbol() }}{{ number_format($item['unit_price'], 2) }}
                                        </td>
                                        <td class="px-4 py-4 text-right whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100" data-label="Amount">
                                            {{ Auth::user()->getCurrencySymbol() }}{{ number_format($item['quantity'] * $item['unit_price'], 2) }}
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
                                <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($invoice->subtotal, 2) }}</span>
                            </div>

                            @if ($invoice->tax_rate > 0)
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Tax ({{ $invoice->tax_rate }}%):</span>
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($invoice->tax_amount, 2) }}</span>
                                </div>
                            @endif

                            @if ($invoice->discount_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Discount:</span>
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">-{{ Auth::user()->getCurrencySymbol() }}{{ number_format($invoice->discount_amount, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                <span class="text-base font-medium text-zinc-900 dark:text-zinc-100">Total:</span>
                                <span class="text-base font-bold text-orange-600 dark:text-orange-500">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($invoice->total_amount, 2) }}</span>
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

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Payment Status -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Payment Status</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full
                            {{ $invoice->status === 'paid' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : '' }}
                            {{ $invoice->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' : '' }}
                            {{ $invoice->status === 'draft' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : '' }}
                            {{ $invoice->status === 'cancelled' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' : '' }}
                            flex items-center justify-center
                        ">
                            @if ($invoice->status === 'paid')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @elseif ($invoice->status === 'pending')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif ($invoice->status === 'draft')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            @elseif ($invoice->status === 'cancelled')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                {{ ucfirst($invoice->status) }}
                            </h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                @if ($invoice->status === 'paid')
                                    Payment received
                                @elseif ($invoice->status === 'pending')
                                    Awaiting payment
                                @elseif ($invoice->status === 'draft')
                                    Not yet finalized
                                @elseif ($invoice->status === 'cancelled')
                                    Invoice cancelled
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                        <dl class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <div class="py-3 flex justify-between">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Amount Due</dt>
                                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($invoice->total_amount, 2) }}</dd>
                            </div>
                            <div class="py-3 flex justify-between">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Invoice Date</dt>
                                <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_date?->format('M d, Y') }}</dd>
                            </div>
                            <div class="py-3 flex justify-between">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Due Date</dt>
                                <dd class="text-sm text-zinc-900 dark:text-zinc-100 {{ $invoice->due_date < now() && $invoice->status !== 'paid' ? 'text-red-600 dark:text-red-400' : '' }}">
                                    {{ $invoice->due_date?->format('M d, Y') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Client Information -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Client Information</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Name</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $invoice->client_name }}</p>
                        </div>

                        @if ($invoice->client_email)
                            <div>
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</h3>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $invoice->client_email }}</p>
                            </div>
                        @endif

                        @if ($invoice->client_address)
                            <div>
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</h3>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100 whitespace-pre-line">{{ $invoice->client_address }}</p>
                            </div>
                        @endif

                        @if ($invoice->client_id)
                            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                <a href="{{ route('clients.show', $invoice->client_id) }}" class="inline-flex items-center text-sm font-medium text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    View Client Profile
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Related Order -->
            @if ($invoice->order_id)
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Related Order</h2>
                    </div>
                    <div class="p-6">
                        <a href="{{ route('orders.show', $invoice->order_id) }}" class="inline-flex items-center text-sm font-medium text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                            </svg>
                            View Order Details
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
