<?php

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $sku = '';
    public string $type = '';
    public string $description = '';
    public int $quantity = 0;
    public string $unit = 'pcs';
    public ?float $unit_price = null;
    public ?float $total_cost = null;
    public ?string $supplier = null;
    public ?string $location = null;
    public $image = null;

    public function updatedQuantity()
    {
        $this->calculateTotalCost();
    }

    public function updatedUnitPrice()
    {
        $this->calculateTotalCost();
    }

    public function calculateTotalCost()
    {
        if ($this->quantity && $this->unit_price) {
            $this->total_cost = $this->quantity * $this->unit_price;
        }
    }

    public function mount(): void
    {
        // Calculate total cost if quantity and unit price are set with default values
        if ($this->quantity && $this->unit_price) {
            $this->calculateTotalCost();
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $inventoryItem = new InventoryItem();
        $inventoryItem->user_id = Auth::id();
        $inventoryItem->name = $validated['name'];
        $inventoryItem->sku = $validated['sku'];
        $inventoryItem->type = $validated['type'];
        $inventoryItem->description = $validated['description'];
        $inventoryItem->quantity = $validated['quantity'];
        $inventoryItem->unit = $validated['unit'];
        $inventoryItem->unit_price = $validated['unit_price'];
        $inventoryItem->total_cost = $validated['total_cost'];
        $inventoryItem->supplier = $validated['supplier'];
        $inventoryItem->location = $validated['location'];

        if ($this->image) {
            $inventoryItem->image = $this->image->store('inventory', 'public');
        }

        $inventoryItem->save();

        session()->flash('status', 'Inventory item created successfully!');
        $this->redirect(route('inventory.index'));
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
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Add Inventory Item</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Add a new item to your inventory</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Inventory
        </a>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" id="name" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="sku" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">SKU / Item Code</label>
                    <input wire:model="sku" type="text" id="sku" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('sku') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Type <span class="text-red-500">*</span></label>
                    <select wire:model="type" id="type" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                        <option value="">Select Type</option>
                        <option value="fabric">Fabric</option>
                        <option value="accessory">Accessory</option>
                        <option value="tool">Tool</option>
                        <option value="packaging">Packaging</option>
                        <option value="other">Other</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input wire:model.live="quantity" type="number" min="0" id="quantity" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="unit" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Unit <span class="text-red-500">*</span></label>
                    <select wire:model="unit" id="unit" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="m">Meters (m)</option>
                        <option value="cm">Centimeters (cm)</option>
                        <option value="yards">Yards</option>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="g">Grams (g)</option>
                        <option value="rolls">Rolls</option>
                        <option value="boxes">Boxes</option>
                        <option value="sets">Sets</option>
                    </select>
                    @error('unit') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="unit_price" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Unit Price</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">$</span>
                        </div>
                        <input wire:model.live="unit_price" type="number" min="0" step="0.01" id="unit_price" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-7 p-2.5">
                    </div>
                    @error('unit_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="total_cost" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Total Cost</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">$</span>
                        </div>
                        <input wire:model="total_cost" type="number" min="0" step="0.01" id="total_cost" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-7 p-2.5" readonly>
                    </div>
                    @error('total_cost') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="supplier" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Supplier</label>
                    <input wire:model="supplier" type="text" id="supplier" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('supplier') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Storage Location</label>
                    <input wire:model="location" type="text" id="location" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('location') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                    <textarea wire:model="description" id="description" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="image" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Image</label>
                    <div class="flex items-center">
                        <div
                            x-data="{ uploading: false, progress: 0 }"
                            x-on:livewire-upload-start="uploading = true"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                            class="flex flex-col items-start space-y-2"
                        >
                            <label for="image-upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                                Upload Image
                            </label>
                            <input wire:model="image" id="image-upload" type="file" class="hidden" accept="image/*">

                            <!-- Upload Progress -->
                            <div x-show="uploading" class="w-full">
                                <div class="bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5 mb-1">
                                    <div class="bg-orange-600 h-2.5 rounded-full" x-bind:style="`width: ${progress}%`"></div>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400" x-text="`Uploading: ${progress}%`"></p>
                            </div>

                            <!-- Image Preview -->
                            @if ($image)
                                <div class="mt-2">
                                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="h-32 w-32 object-cover rounded-md">
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('image') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('inventory.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Save Item
                </button>
            </div>
        </form>
    </div>
</div>
