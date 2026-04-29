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
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Adjust Inventory Quantities</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update stock levels in bulk using a CSV file</p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Instructions</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    Upload a CSV file to update existing inventory items. You can either set an absolute quantity or provide an adjustment value (e.g., +10 or -5).
                </p>
                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg mb-4">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Required Columns (at least one identifier and one quantity field):</h3>
                    <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-1">
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">sku</span>, <span class="font-medium text-zinc-900 dark:text-zinc-100">name</span>, or <span class="font-medium text-zinc-900 dark:text-zinc-100">id</span> - Used to identify the item</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">new_quantity</span> or <span class="font-medium text-zinc-900 dark:text-zinc-100">quantity</span> - Sets the stock to this exact number</li>
                        <li><span class="font-medium text-zinc-900 dark:text-zinc-100">adjustment</span> - Adds or subtracts from the current stock (e.g. 10 to add, -5 to subtract)</li>
                    </ul>
                </div>
                <div class="flex">
                    <button wire:click="downloadTemplate" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Download Import template
                    </button>
                </div>
            </div>

            <form wire:submit="adjust" class="space-y-6">
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
                    <a href="{{ route('inventory.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="adjust">Adjust Quantities</span>
                        <span wire:loading wire:target="adjust">
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

            @if ($adjustedCount > 0 || $errorCount > 0)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Adjustment Results</h3>

                @if ($adjustedCount > 0)
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 dark:text-green-200">
                                Successfully adjusted {{ $adjustedCount }} inventory items.
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
                                Failed to adjust {{ $errorCount }} inventory items.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg max-h-60 overflow-y-auto">
                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Error Details:</h4>
                    <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-1">
                        @foreach ($adjustmentErrors as $error)
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
