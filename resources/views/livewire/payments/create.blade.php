<?php

use App\Models\Payment;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $description = '';
    public float $amount = 0;
    public string $date = '';
    public ?int $client_id = null;
    public ?int $invoice_id = null;
    public string $payment_method = '';
    public ?string $reference_number = '';
    public string $status = 'completed';
    public ?string $notes = '';

    public function mount(): void
    {
        // Set default date to today
        $this->date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'payment_method' => ['required', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:pending,completed,failed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'description' => $this->description,
            'amount' => $this->amount,
            'payment_date' => $this->date,
            'client_id' => $this->client_id,
            'invoice_id' => $this->invoice_id,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        $this->redirect(route('payments.index'));
    }

    public function with(): array
    {
        return [
            'clients' => Client::where('user_id', Auth::id())->orderBy('name')->get(),
            'invoices' => Invoice::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Record New Payment</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Track payments from clients</p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                    <input wire:model="description" type="text" id="description" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Amount</label>
                    <input wire:model="amount" type="number" step="0.01" id="amount" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="client_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Client</label>
                    <select wire:model="client_id" id="client_id" required class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="">Select a client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="invoice_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Invoice (Optional)</label>
                    <select wire:model="invoice_id" id="invoice_id" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="">Select an invoice</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} - {{ number_format($invoice->total_amount, 2) }}</option>
                        @endforeach
                    </select>
                    @error('invoice_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Date</label>
                    <input wire:model="date" type="date" id="date" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Payment Method</label>
                    <select wire:model="payment_method" id="payment_method" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                        <option value="">Select payment method</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="other">Other</option>
                    </select>
                    @error('payment_method') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="reference_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Reference Number</label>
                    <input wire:model="reference_number" type="text" id="reference_number" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('reference_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                    <select wire:model="status" id="status" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                    <textarea wire:model="notes" id="notes" rows="4" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('payments.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Save Payment
                </button>
            </div>
        </form>
    </div>
</div>
