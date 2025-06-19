<?php

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public InventoryItem $inventoryItem;

    public function mount(InventoryItem $inventoryItem)
    {
        $this->inventoryItem = $inventoryItem;
    }

    public function delete()
    {
        if (!auth()->user()->hasPermission('delete_inventory')) {
            session()->flash('error', 'You Do Not Have Permission to Delte Inventory!');
            return $this->redirect(route('inventory.index'));
        }
        $this->inventoryItem->delete();
        session()->flash('status', 'Inventory item deleted successfully!');
        return $this->redirect(route('inventory.index'));
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
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->name }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                @if($inventoryItem->sku)
                    SKU: {{ $inventoryItem->sku }} |
                @endif
                Type: {{ ucfirst($inventoryItem->type ?? 'N/A') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
            <a href="{{ route('inventory.edit', $inventoryItem) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Item Details -->
        <div class="md:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Item Details</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Name</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">SKU / Item Code</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->sku ?? 'N/A' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Type</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                    {{ ucfirst($inventoryItem->type ?? 'N/A') }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                @if ($inventoryItem->quantity <= 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                        Out of Stock
                                    </span>
                                @elseif ($inventoryItem->quantity <= 5)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        In Stock
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Quantity</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->quantity }} {{ $inventoryItem->unit ?? 'pcs' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Unit Price</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->unit_price ? '$' . number_format($inventoryItem->unit_price, 2) : 'N/A' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Cost</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->total_cost ? '$' . number_format($inventoryItem->total_cost, 2) : 'N/A' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Value</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                @if($inventoryItem->unit_price && $inventoryItem->quantity > 0)
                                    ${{ number_format($inventoryItem->unit_price * $inventoryItem->quantity, 2) }}
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Supplier</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->supplier ?? 'N/A' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Storage Location</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->location ?? 'N/A' }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Description</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $inventoryItem->description ?? 'No description provided.' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Usage History Card (Placeholder) -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Usage History</h2>
                </div>
                <div class="p-6">
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-zinc-500 dark:text-zinc-400">No usage history available for this item.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Image Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Item Image</h2>
                </div>
                <div class="p-6">
                    @if ($inventoryItem->image)
                        <img src="{{ Storage::url($inventoryItem->image) }}" alt="{{ $inventoryItem->name }}" class="w-full h-auto rounded-lg">
                    @else
                        <div class="bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center h-48">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-center mt-2 text-sm text-zinc-500 dark:text-zinc-400">No image available</p>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Actions</h2>
                </div>
                <div class="p-6 space-y-4">
                    <a href="{{ route('inventory.edit', $inventoryItem) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit Item
                    </a>

                    <button
                        x-data="{}"
                        x-on:click="if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) { $wire.delete() }"
                        type="button"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 bg-white dark:bg-zinc-800 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md text-sm font-medium transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Delete Item
                    </button>
                </div>
            </div>

            <!-- Stock Management Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Quick Stock Update</h2>
                </div>
                <div class="p-6">
                    <div class="text-center py-4">
                        <p class="text-zinc-500 dark:text-zinc-400 mb-4">Current stock: {{ $inventoryItem->quantity }} {{ $inventoryItem->unit ?? 'pcs' }}</p>
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('inventory.edit', $inventoryItem) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                Update Stock
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
