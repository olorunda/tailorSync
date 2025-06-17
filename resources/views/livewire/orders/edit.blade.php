<?php

use App\Models\Client;
use App\Models\Design;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Order $order;
    public ?int $client_id = null;
    public ?int $design_id = null;
    public string $description = '';
    public ?string $due_date = null;
    public string $status = 'pending';
    public ?float $total_amount = null;
    public ?float $deposit_amount = null;
    public ?string $notes = '';
    public array $existingPhotos = [];
    public $newPhotos = [];
    public array $photosToDelete = [];

    public function mount(Order $order): void
    {
        // Prevent editing of completed or cancelled orders
        if ($order->status === 'completed' || $order->status === 'cancelled') {
            session()->flash('error', 'Orders that have been ' . $order->status . ' cannot be edited.');
            $this->redirect(route('orders.show', $order));
            return;
        }

        $this->order = $order;
        $this->client_id = $order->client_id;
        $this->design_id = $order->design_id;
        $this->description = $order->description;
        $this->due_date = $order->due_date ? $order->due_date->format('Y-m-d') : null;
        $this->status = $order->status;
        $this->total_amount = $order->total_amount;
        $this->deposit_amount = $order->deposit_amount ?? $order->deposit;
        $this->notes = $order->notes ?? '';
        $this->existingPhotos = $order->photos ?? [];
    }

    public function save(): void
    {
        // Double-check to prevent editing of completed or cancelled orders
        // This is a security measure in case someone tries to bypass the UI
        if ($this->order->status === 'completed' || $this->order->status === 'cancelled') {
            session()->flash('error', 'Orders that have been ' . $this->order->status . ' cannot be edited.');
            $this->redirect(route('orders.show', $this->order));
            return;
        }

        $validated = $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'design_id' => ['nullable', 'exists:designs,id'],
            'description' => ['required', 'string', 'max:500'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:pending,in_progress,ready,completed,cancelled'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'newPhotos.*' => ['nullable', 'image', 'max:1024'],
        ]);

        try {
            // For debugging
            \Log::info('Updating order', [
                'order_id' => $this->order->id,
                'total_amount' => $this->total_amount,
                'deposit_amount' => $this->deposit_amount,
            ]);

            // Delete photos marked for deletion
            $updatedPhotos = $this->existingPhotos;
            foreach ($this->photosToDelete as $index) {
                if (isset($updatedPhotos[$index])) {
                    $photoPath = $updatedPhotos[$index];
                    if (Storage::disk('public')->exists($photoPath)) {
                        Storage::disk('public')->delete($photoPath);
                    }
                    unset($updatedPhotos[$index]);
                }
            }
            $updatedPhotos = array_values($updatedPhotos); // Reindex array

            // Add new photos
            foreach ($this->newPhotos as $photo) {
                $photoPath = $photo->store('order-photos', 'public');
                $updatedPhotos[] = $photoPath;
            }

            $this->order->client_id = $this->client_id;
            $this->order->design_id = $this->design_id;
            $this->order->description = $this->description;
            $this->order->due_date = $this->due_date;
            $this->order->status = $this->status;
            $this->order->total_amount = $this->total_amount;
            $this->order->deposit_amount = $this->deposit_amount;
            $this->order->notes = $this->notes;
            $this->order->photos = $updatedPhotos;
            $this->order->save();

            \Log::info('Order updated successfully', ['id' => $this->order->id]);
            session()->flash('success', 'Order updated successfully!');

            $this->redirect(route('orders.show', $this->order));
        } catch (\Exception $e) {
            \Log::error('Error updating order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    public function togglePhotoDelete($index): void
    {
        if (in_array($index, $this->photosToDelete)) {
            $this->photosToDelete = array_diff($this->photosToDelete, [$index]);
        } else {
            $this->photosToDelete[] = $index;
        }
    }

    public function with(): array
    {
        return [
            'clients' => Client::where('user_id', Auth::id())->orderBy('name')->get(),
            'designs' => Design::where('user_id', Auth::id())->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Order #{{ $order->id }}</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update order details</p>
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

                <!-- Existing Photos -->
                @if (count($existingPhotos) > 0)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Existing Photos</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($existingPhotos as $index => $photo)
                                <div class="relative group">
                                    <img src="{{ Storage::url($photo) }}" class="h-24 w-24 object-cover rounded-md {{ in_array($index, $photosToDelete) ? 'opacity-50' : '' }}">
                                    <button type="button" wire:click="togglePhotoDelete({{ $index }})" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-md opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if (in_array($index, $photosToDelete))
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        @endif
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        @if (count($photosToDelete) > 0)
                            <p class="text-sm text-red-500 mt-2">{{ count($photosToDelete) }} photo(s) marked for deletion</p>
                        @endif
                    </div>
                @endif

                <!-- New Photos -->
                <div class="md:col-span-2">
                    <label for="newPhotos" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Add New Photos</label>
                    <input wire:model="newPhotos" type="file" id="newPhotos" multiple class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('newPhotos.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($newPhotos)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($newPhotos as $photo)
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
                <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Update Order
                </button>
            </div>
        </form>
    </div>
</div>
