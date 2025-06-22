<?php

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Order $order;
    public ?Invoice $invoice = null;

    public function mount(Order $order): void
    {
        $this->order = $order;
        $this->order->load('client', 'design');

        // Check if an invoice exists for this order
        // Check if an invoice exists for this order
        $this->invoice = Auth::user()->allInvoices()->where('order_id', $this->order->id)->first();
    }

    public function updateStatus($status): void
    {
        try {
            $oldStatus = $this->order->status;
            $this->order->status = $status;

            // If the status is being set to cancelled, also set the payment status to cancelled
            if ($status === 'cancelled') {
                $this->order->payment_status = 'cancelled';
            }

            $this->order->save();

            \Log::info('Order status updated', [
                'order_id' => $this->order->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'payment_status' => $this->order->payment_status
            ]);

            session()->flash('success', 'Order status updated to ' . ucfirst(str_replace('_', ' ', $status)));
        } catch (\Exception $e) {
            \Log::error('Error updating order status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating order status: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $orderNumber = $this->order->order_number ?? $this->order->id;
            $this->order->delete();

            \Log::info('Order deleted', [
                'order_id' => $orderNumber
            ]);

            session()->flash('success', 'Order #' . $orderNumber . ' was deleted successfully');
            return redirect()->route('orders.index');
        } catch (\Exception $e) {
            \Log::error('Error deleting order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error deleting order: ' . $e->getMessage());
        }
    }
}; ?>

<div class="w-full">
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Order #{{ $order->id }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Created {{ $order->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-2">
            @if ($order->status !== 'completed' && $order->status !== 'cancelled')
            <a href="{{ route('orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Order
            </a>
            @else
            <button disabled class="inline-flex items-center px-4 py-2 bg-zinc-400 text-white rounded-md text-sm font-medium cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Order
            </button>
            @endif
            @if ($invoice)
            <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                View Invoice
            </a>
            @else
            <a href="{{ route('invoices.create', ['order_id' => $order->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                Create Invoice
            </a>
            @endif
        </div>
    </div>

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
                                @if ($order->client)
                                    <a href="{{ route('clients.show', $order->client) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">
                                        {{ $order->client->name }}
                                    </a>
                                @else
                                    No Client
                                @endif
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
                                <a href="{{ route('designs.show', $order->design) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">
                                    {{ $order->design->name }}
                                </a>
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
                                {{ $order->total_amount ? Auth::user()->getCurrencySymbol() . number_format($order->total_amount, 2) : 'Not set' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Deposit</p>
                            <p class="text-zinc-900 dark:text-zinc-100">
                                {{ $order->deposit_amount ? Auth::user()->getCurrencySymbol() . number_format($order->deposit_amount, 2) : 'Not set' }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if ($order->notes)
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Notes</p>
                            <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-line">{{ $order->notes }}</p>
                        </div>
                    @endif

                    <!-- Photos -->
                    @if ($order->photos && count($order->photos) > 0)
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">Photos</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                @foreach ($order->photos as $photo)
                                    <a href="{{ Storage::url($photo) }}" target="_blank" class="block">
                                        <img src="{{ Storage::url($photo) }}" alt="Order photo" class="h-24 w-full object-cover rounded-md hover:opacity-90 transition-opacity">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <button wire:click="delete" wire:confirm="Are you sure you want to delete this order? This action cannot be undone." class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Delete Order
                    </button>
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

                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">Update Status</p>
                        <div class="space-y-2">
                            <button wire:click="updateStatus('pending')" class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors
                                @if($order->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                @else bg-zinc-100 hover:bg-yellow-50 dark:bg-zinc-700 dark:hover:bg-yellow-900/10 text-zinc-800 dark:text-zinc-200 @endif">
                                Pending
                            </button>

                            <button wire:click="updateStatus('in_progress')" class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors
                                @if($order->status === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                @else bg-zinc-100 hover:bg-blue-50 dark:bg-zinc-700 dark:hover:bg-blue-900/10 text-zinc-800 dark:text-zinc-200 @endif">
                                In Progress
                            </button>

                            <button wire:click="updateStatus('ready')" class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors
                                @if($order->status === 'ready') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                @else bg-zinc-100 hover:bg-purple-50 dark:bg-zinc-700 dark:hover:bg-purple-900/10 text-zinc-800 dark:text-zinc-200 @endif">
                                Ready
                            </button>

                            <button wire:click="updateStatus('completed')" class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors
                                @if($order->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                @else bg-zinc-100 hover:bg-green-50 dark:bg-zinc-700 dark:hover:bg-green-900/10 text-zinc-800 dark:text-zinc-200 @endif">
                                Completed
                            </button>

                            <button wire:click="updateStatus('cancelled')" class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors
                                @if($order->status === 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                @else bg-zinc-100 hover:bg-red-50 dark:bg-zinc-700 dark:hover:bg-red-900/10 text-zinc-800 dark:text-zinc-200 @endif">
                                Cancelled
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        @if ($order->status !== 'completed' && $order->status !== 'cancelled')
                        <a href="{{ route('orders.edit', $order) }}" class="block w-full text-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                            Edit Order
                        </a>
                        @else
                        <button disabled class="block w-full text-center px-4 py-2 bg-zinc-400 text-white rounded-md text-sm font-medium cursor-not-allowed">
                            Edit Order
                        </button>
                        @endif
                        @if ($invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                            View Invoice
                        </a>
                        @else
                        <a href="{{ route('invoices.create', ['order_id' => $order->id]) }}" class="block w-full text-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">
                            Create Invoice
                        </a>
                        @endif
                        <a href="{{ route('orders.index') }}" class="block w-full text-center px-4 py-2 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-200 rounded-md text-sm font-medium transition-colors">
                            Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
