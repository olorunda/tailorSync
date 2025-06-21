<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>

        <x-slot:heading>
            {{ __('Business Profile') }}
        </x-slot>
        <x-slot:subheading>
            {{ __('View your business information and public booking QR code') }}
        </x-slot>

        <div class="space-y-6">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Business Details</h2>

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
                    @if($successMessage)
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-lg">
                            {{ $successMessage }}
                        </div>
                    @endif
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Social Media Handles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="facebookHandle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/>
                                    </svg>
                                    Facebook
                                </span>
                            </label>
                            <input type="text" id="facebookHandle" wire:model="facebookHandle" placeholder="Your Facebook handle (e.g., @yourbusiness)" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            @error('facebookHandle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="instagramHandle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                    Instagram
                                </span>
                            </label>
                            <input type="text" id="instagramHandle" wire:model="instagramHandle" placeholder="Your Instagram handle (e.g., @yourbusiness)" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            @error('instagramHandle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="tiktokHandle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-black dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                    </svg>
                                    TikTok
                                </span>
                            </label>
                            <input type="text" id="tiktokHandle" wire:model="tiktokHandle" placeholder="Your TikTok handle (e.g., @yourbusiness)" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            @error('tiktokHandle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="whatsappHandle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                    </svg>
                                    WhatsApp
                                </span>
                            </label>
                            <input type="text" id="whatsappHandle" wire:model="whatsappHandle" placeholder="Your WhatsApp number (e.g., +1234567890)" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            @error('whatsappHandle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
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
    </x-settings.layout>
</section>
