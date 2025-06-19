<div class="space-y-6">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Business Details</h2>

            @if($successMessage)
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-lg">
                    {{ $successMessage }}
                </div>
            @endif

            <form wire:submit.prevent="updateBusinessDetails" class="space-y-4">
                <div>
                    <label for="businessName" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Business Name</label>
                    <input type="text" id="businessName" wire:model="businessName" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                    @error('businessName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="businessAddress" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Business Address</label>
                    <textarea id="businessAddress" wire:model="businessAddress" rows="3" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white"></textarea>
                    @error('businessAddress') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="businessPhone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Business Phone</label>
                        <input type="text" id="businessPhone" wire:model="businessPhone" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                        @error('businessPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="businessEmail" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Business Email</label>
                        <input type="email" id="businessEmail" wire:model="businessEmail" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                        @error('businessEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Business Logo</label>

                    <div class="mt-2 flex items-center">
                        @if($logo)
                            <div class="mr-4">
                                <img src="{{ asset('storage/' . $logo) }}" alt="Business Logo" class="h-16 w-auto rounded">
                            </div>
                        @endif

                        <label for="newLogo" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg font-medium hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors cursor-pointer">
                            {{ $logo ? 'Change Logo' : 'Upload Logo' }}
                        </label>
                        <input type="file" id="newLogo" wire:model="newLogo" class="hidden">
                    </div>

                    @error('newLogo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                    @if($newLogo)
                        <div class="mt-2">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Preview:</p>
                            <img src="{{ $newLogo->temporaryUrl() }}" alt="New Logo Preview" class="mt-1 h-16 w-auto rounded">
                        </div>
                    @endif
                </div>

                <div class="pt-4">
                    <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                        Save Business Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
