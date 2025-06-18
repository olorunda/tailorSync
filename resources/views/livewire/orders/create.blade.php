<?php

use App\Models\Client;
use App\Models\Design;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?int $client_id = null;
    public ?int $design_id = null;
    public string $description = '';
    public ?string $due_date = null;
    public string $status = 'pending';
    public ?float $total_amount = null;
    public ?float $deposit_amount = null;
    public ?string $notes = '';
    public $photos = [];

    public function mount(): void
    {
        // Check if client_id is provided in the query parameters
        $clientId = request()->query('client_id');
        if ($clientId) {
            $this->client_id = $clientId;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'design_id' => ['nullable', 'exists:designs,id'],
            'description' => ['required', 'string', 'max:500'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'string', 'in:pending,in_progress,ready,completed,cancelled'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos.*' => ['nullable', 'image', 'max:1024'],
        ]);

        try {
            // For debugging
            \Log::info('Creating new order', [
                'client_id' => $this->client_id,
                'user_id' => Auth::id(),
                'total_amount' => $this->total_amount,
                'deposit_amount' => $this->deposit_amount,
            ]);

            $photosPaths = [];
            foreach ($this->photos as $photo) {
                $photosPaths[] = $photo->store('order-photos', 'public');
            }

            // Generate a unique order number if not provided
            $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), -6));

            $order = Order::create([
                'user_id' => Auth::id(),
                'client_id' => $this->client_id,
                'order_number' => $orderNumber,
                'design_id' => $this->design_id,
                'description' => $this->description,
                'due_date' => $this->due_date,
                'status' => $this->status,
                'total_amount' => $this->total_amount,
                'cost' => $this->total_amount ?? 0, // Set cost to the same value as total_amount, default to 0
                'deposit' => $this->deposit_amount ?? 0, // Use deposit instead of deposit_amount, default to 0
                'balance' => ($this->total_amount ?? 0) - ($this->deposit_amount ?? 0), // Calculate balance
                'notes' => $this->notes,
                'photos' => $photosPaths,
            ]);

            \Log::info('Order created successfully', ['id' => $order->id, 'order_number' => $orderNumber]);
            session()->flash('success', 'Order created successfully!');

            $this->redirect(route('orders.show', $order));
        } catch (\Exception $e) {
            \Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'clients' => Auth::user()->allClients()->orderBy('name')->get(),
            'designs' => Auth::user()->allDesigns()->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Create New Order</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Add a new order to your system</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Selection -->
                <div>
                    <label for="client_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Client</label>
                    <select wire:model="client_id" id="client_id" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                        <option value="">Select a client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if (count($clients) === 0)
                        <div class="mt-2">
                            <a href="{{ route('clients.create') }}" class="text-orange-600 dark:text-orange-500 text-sm">+ Add a new client first</a>
                        </div>
                    @endif
                </div>

                <!-- Design Selection (Optional) -->
                <div>
                    <label for="design_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Design (Optional)</label>
                    <select wire:model="design_id" id="design_id" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="">No design</option>
                        @foreach ($designs as $design)
                            <option value="{{ $design->id }}">{{ $design->name }}</option>
                        @endforeach
                    </select>
                    @error('design_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if (count($designs) === 0)
                        <div class="mt-2">
                            <a href="{{ route('designs.create') }}" class="text-orange-600 dark:text-orange-500 text-sm">+ Add a new design</a>
                        </div>
                    @endif
                </div>

                <!-- Order Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Order Description</label>
                    <textarea wire:model="description" id="description" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="Describe the order details" required></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Due Date</label>
                    <input wire:model="due_date" type="date" id="due_date" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                    <select wire:model="status" id="status" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="ready">Ready</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Total Amount -->
                <div>
                    <label for="total_amount" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Total Amount</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400">$</span>
                        </div>
                        <input wire:model="total_amount" type="number" step="0.01" min="0" id="total_amount" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-7 p-2.5" placeholder="0.00">
                    </div>
                    @error('total_amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Deposit Amount -->
                <div>
                    <label for="deposit_amount" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Deposit Amount</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400">$</span>
                        </div>
                        <input wire:model="deposit_amount" type="number" step="0.01" min="0" id="deposit_amount" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-7 p-2.5" placeholder="0.00">
                    </div>
                    @error('deposit_amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Photos -->
                <div class="md:col-span-2">
                    <label for="photos" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Photos (Optional)</label>
                    <input wire:model="photos" type="file" id="photos" multiple class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('photos.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($photos)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($photos as $photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-md">
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                    <textarea wire:model="notes" id="notes" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="Any additional notes about this order"></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('orders.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Create Order
                </button>
            </div>
        </form>
    </div>
</div>
