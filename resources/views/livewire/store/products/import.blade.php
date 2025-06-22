<?php

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $csvFile = null;
    public $importedCount = 0;
    public $updatedCount = 0;
    public $errorCount = 0;
    public $importErrors = [];
    public $processing = false;
    public $updateMode = false;

    public function mount()
    {
        if (!auth()->user()->hasPermission('create_store_products')) {
            session()->flash('error', 'You do not have permission to import products.');
            return $this->redirect(route('store.products.index'));
        }
    }

    public function import()
    {
        if (!auth()->user()->hasPermission('create_store_products')) {
            session()->flash('error', 'You do not have permission to import products.');
            return $this->redirect(route('store.products.index'));
        }

        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
        ]);

        $this->processing = true;
        $this->importedCount = 0;
        $this->updatedCount = 0;
        $this->errorCount = 0;
        $this->importErrors = [];

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');

        // Get headers
        $headers = fgetcsv($file);
        $headers = array_map('strtolower', $headers);
        $requiredHeaders = ['name', 'price', 'stock_quantity'];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (!empty($missingHeaders)) {
            $this->processing = false;
            $this->addError('csvFile', 'CSV file is missing required headers: ' . implode(', ', $missingHeaders));
            return;
        }

        // Process rows
        $row = 2; // Start from row 2 (after header)
        while (($data = fgetcsv($file)) !== false) {
            $rowData = array_combine($headers, count($headers) === count($data) ? $data : array_pad($data, count($headers), null));

            // Validate required fields
            if (empty($rowData['name'])) {
                $this->importErrors[] = "Row {$row}: Name is required";
                $this->errorCount++;
                $row++;
                continue;
            }

            if (!isset($rowData['price']) || $rowData['price'] === '') {
                $this->importErrors[] = "Row {$row}: Price is required";
                $this->errorCount++;
                $row++;
                continue;
            }

            if (!isset($rowData['stock_quantity']) || $rowData['stock_quantity'] === '') {
                $this->importErrors[] = "Row {$row}: Stock quantity is required";
                $this->errorCount++;
                $row++;
                continue;
            }

            try {
                // Check if we're in update mode and have an SKU to match
                $existingProduct = null;
                if ($this->updateMode && !empty($rowData['sku'])) {
                    $existingProduct = Product::where('user_id', Auth::id())
                        ->where('sku', $rowData['sku'])
                        ->first();
                }

                if ($existingProduct) {
                    // Update existing product
                    $existingProduct->update([
                        'name' => $rowData['name'],
                        'price' => (float) $rowData['price'],
                        'sale_price' => isset($rowData['sale_price']) && $rowData['sale_price'] !== '' ? (float) $rowData['sale_price'] : null,
                        'stock_quantity' => (int) $rowData['stock_quantity'],
                        'category' => $rowData['category'] ?? $existingProduct->category,
                        'description' => $rowData['description'] ?? $existingProduct->description,
                        'is_featured' => isset($rowData['is_featured']) ? filter_var($rowData['is_featured'], FILTER_VALIDATE_BOOLEAN) : $existingProduct->is_featured,
                        'is_active' => isset($rowData['is_active']) ? filter_var($rowData['is_active'], FILTER_VALIDATE_BOOLEAN) : $existingProduct->is_active,
                        'is_custom_order' => isset($rowData['is_custom_order']) ? filter_var($rowData['is_custom_order'], FILTER_VALIDATE_BOOLEAN) : $existingProduct->is_custom_order,
                        'sizes' => isset($rowData['sizes']) && $rowData['sizes'] !== '' ? explode(',', $rowData['sizes']) : $existingProduct->sizes,
                        'colors' => isset($rowData['colors']) && $rowData['colors'] !== '' ? explode(',', $rowData['colors']) : $existingProduct->colors,
                        'materials' => isset($rowData['materials']) && $rowData['materials'] !== '' ? explode(',', $rowData['materials']) : $existingProduct->materials,
                        'tags' => isset($rowData['tags']) && $rowData['tags'] !== '' ? explode(',', $rowData['tags']) : $existingProduct->tags,
                    ]);
                    $this->updatedCount++;
                } else {
                    // Create new product
                    Product::create([
                        'user_id' => Auth::id(),
                        'name' => $rowData['name'],
                        'sku' => $rowData['sku'] ?? 'SKU-' . \Illuminate\Support\Str::random(8),
                        'category' => $rowData['category'] ?? null,
                        'description' => $rowData['description'] ?? null,
                        'price' => (float) $rowData['price'],
                        'sale_price' => isset($rowData['sale_price']) && $rowData['sale_price'] !== '' ? (float) $rowData['sale_price'] : null,
                        'stock_quantity' => (int) $rowData['stock_quantity'],
                        'is_featured' => isset($rowData['is_featured']) ? filter_var($rowData['is_featured'], FILTER_VALIDATE_BOOLEAN) : false,
                        'is_active' => isset($rowData['is_active']) ? filter_var($rowData['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                        'is_custom_order' => isset($rowData['is_custom_order']) ? filter_var($rowData['is_custom_order'], FILTER_VALIDATE_BOOLEAN) : false,
                        'images' => [],
                        'primary_image' => null,
                        'sizes' => isset($rowData['sizes']) && $rowData['sizes'] !== '' ? explode(',', $rowData['sizes']) : [],
                        'colors' => isset($rowData['colors']) && $rowData['colors'] !== '' ? explode(',', $rowData['colors']) : [],
                        'materials' => isset($rowData['materials']) && $rowData['materials'] !== '' ? explode(',', $rowData['materials']) : [],
                        'tags' => isset($rowData['tags']) && $rowData['tags'] !== '' ? explode(',', $rowData['tags']) : [],
                    ]);
                    $this->importedCount++;
                }
            } catch (\Exception $e) {
                $this->importErrors[] = "Row {$row}: " . $e->getMessage();
                $this->errorCount++;
            }

            $row++;
        }

        fclose($file);
        $this->processing = false;

        if ($this->importedCount > 0 || $this->updatedCount > 0) {
            $message = [];
            if ($this->importedCount > 0) {
                $message[] = "{$this->importedCount} products imported successfully.";
            }
            if ($this->updatedCount > 0) {
                $message[] = "{$this->updatedCount} products updated successfully.";
            }
            session()->flash('status', implode(' ', $message));
        }
    }

    public function downloadSampleCsv()
    {
        return response()->streamDownload(function () {
            $headers = ['name', 'sku', 'category', 'description', 'price', 'sale_price', 'stock_quantity', 'is_featured', 'is_active', 'is_custom_order', 'sizes', 'colors', 'materials', 'tags'];
            $sample = [
                ['Blue T-Shirt', 'TSH-001', 'Clothing', 'Comfortable cotton t-shirt', '29.99', '24.99', '100', 'true', 'true', 'false', 'S,M,L,XL', 'Blue,Navy', 'Cotton', 'summer,casual'],
                ['Designer Jeans', 'JNS-002', 'Clothing', 'Premium denim jeans', '89.99', '', '50', 'false', 'true', 'false', '30,32,34,36', 'Black,Blue', 'Denim', 'premium,casual'],
            ];

            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            foreach ($sample as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, 'product_import_sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
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

    @if (session()->has('status'))
    <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
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

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Import Products</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Import products from a CSV file or update existing products</p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Instructions</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    Please upload a CSV file with the following columns:
                </p>
                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg mb-4">
                    <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-1">
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">name</span> (required) - Product name</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">sku</span> (optional) - Stock keeping unit or product code (used to match existing products for updates)</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">category</span> (optional) - Product category</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">description</span> (optional) - Product description</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">price</span> (required) - Regular price</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">sale_price</span> (optional) - Sale price</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">stock_quantity</span> (required) - Available quantity</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">is_featured</span> (optional, default: false) - Featured product (true/false)</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">is_active</span> (optional, default: true) - Active product (true/false)</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">is_custom_order</span> (optional, default: false) - Custom order product (true/false)</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">sizes</span> (optional) - Available sizes (comma-separated)</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">colors</span> (optional) - Available colors (comma-separated)</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">materials</span> (optional) - Materials used (comma-separated)</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">tags</span> (optional) - Product tags (comma-separated)</li>
                    </ul>
                </div>
                <div class="flex">
                    <button wire:click="downloadSampleCsv" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Download Sample CSV
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Import Mode</h2>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="updateMode" value="0" class="form-radio h-4 w-4 text-orange-600">
                        <span class="ml-2 text-zinc-700 dark:text-zinc-300">Create new products only</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="updateMode" value="1" class="form-radio h-4 w-4 text-orange-600">
                        <span class="ml-2 text-zinc-700 dark:text-zinc-300">Create new and update existing products (by SKU)</span>
                    </label>
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                    When updating existing products, the system will match products by SKU. If a product with the same SKU exists, it will be updated; otherwise, a new product will be created.
                </p>
            </div>

            <form wire:submit="import" class="space-y-6">
                <div>
                    <label for="csvFile" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">CSV File</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-zinc-300 dark:border-zinc-600 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-zinc-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-zinc-600 dark:text-zinc-400">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-zinc-800 rounded-md font-medium text-orange-600 dark:text-orange-500 hover:text-orange-500 dark:hover:text-orange-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                    <span>Upload a file</span>
                                    <input wire:model="csvFile" id="file-upload" name="file-upload" type="file" class="sr-only" accept=".csv,.txt">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                CSV file up to 1MB
                            </p>
                        </div>
                    </div>
                    @error('csvFile') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                @if ($csvFile)
                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-zinc-700 dark:text-zinc-300">{{ $csvFile->getClientOriginalName() }}</span>
                    </div>
                </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <a href="{{ route('store.products.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="import">Import Products</span>
                        <span wire:loading wire:target="import">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>

            @if ($processing)
            <div class="mt-6">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-orange-600"></div>
                </div>
                <p class="text-center mt-2 text-zinc-600 dark:text-zinc-400">Processing your file...</p>
            </div>
            @endif

            @if ($importedCount > 0 || $updatedCount > 0 || $errorCount > 0)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Import Results</h3>

                @if ($importedCount > 0)
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 dark:text-green-200">
                                Successfully imported {{ $importedCount }} new products.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if ($updatedCount > 0)
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 dark:text-green-200">
                                Successfully updated {{ $updatedCount }} existing products.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if ($errorCount > 0)
                <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-200">
                                Failed to import {{ $errorCount }} products.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg max-h-60 overflow-y-auto">
                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Error Details:</h4>
                    <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-1">
                        @foreach ($importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
