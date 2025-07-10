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
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Appointment Settings</h3>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg mb-4">
                        <p class="text-sm text-orange-800 dark:text-orange-300">
                            Configure your business hours and available days for appointments. These settings will determine when clients can book appointments.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="businessHoursStart" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Business Hours Start</label>
                                <input type="time" id="businessHoursStart" wire:model="businessHoursStart" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                @error('businessHoursStart') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="businessHoursEnd" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Business Hours End</label>
                                <input type="time" id="businessHoursEnd" wire:model="businessHoursEnd" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                @error('businessHoursEnd') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Available Days</label>
                            <div class="grid grid-cols-4 md:grid-cols-7 gap-2">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <label class="flex items-center p-2 border border-zinc-300 dark:border-zinc-600 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                        <input type="checkbox" wire:model="availableDays" value="{{ strtolower($day) }}" class="rounded border-zinc-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $day }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('availableDays') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Tax Settings</h3>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg mb-4">
                        <p class="text-sm text-orange-800 dark:text-orange-300">
                            Configure tax settings for invoices according to your country's tax laws. These settings will affect how taxes are calculated on invoices.
                        </p>
                    </div>

                    @if(!$canManageTaxSettings)
                        <div class="bg-zinc-100 dark:bg-zinc-800 p-4 rounded-lg mb-4">
                            <p class="text-zinc-600 dark:text-zinc-400 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                You don't have permission to manage tax settings. Please contact your administrator.
                            </p>
                        </div>
                    @endif

                    <div class="space-y-4" @if(!$canManageTaxSettings) x-data="" x-on:click.prevent="" class="opacity-60 pointer-events-none" @endif>
                        <div>
                            <label for="taxEnabled" class="flex items-center">
                                <input type="checkbox" id="taxEnabled" wire:model="taxEnabled" class="rounded border-zinc-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Enable tax calculation on invoices</span>
                            </label>
                        </div>

                        <div>
                            <label for="taxNumber" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tax Identification Number</label>
                            <input type="text" id="taxNumber" wire:model="taxNumber" placeholder="e.g., VAT number, EIN, GST number" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            @error('taxNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="taxCountry" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tax Country</label>
                            <select id="taxCountry" wire:model="taxCountry"
                                    wire:change="setTaxCountry($event.target.value)"
                                    class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                <option value="none">None</option>
                                <option value="canada">Canada</option>
                                <option value="us">United States</option>
                                <option value="uk">United Kingdom</option>
                                <option value="nigeria">Nigeria</option>
                            </select>
                            @error('taxCountry') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Canadian Tax Settings -->
                        @if($taxCountry === 'canada')
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Canadian Tax Settings</h4>

                            <div class="space-y-3">
                                <div>
                                    <label for="canadaProvince" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Province</label>
                                    <select id="canadaProvince" wire:model="canadaProvince" wire:change="setProvince($event.target.value)"class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                        <option value="">Select Province</option>
                                        <option value="alberta">Alberta</option>
                                        <option value="british_columbia">British Columbia</option>
                                        <option value="manitoba">Manitoba</option>
                                        <option value="new_brunswick">New Brunswick</option>
                                        <option value="newfoundland_and_labrador">Newfoundland and Labrador</option>
                                        <option value="northwest_territories">Northwest Territories</option>
                                        <option value="nova_scotia">Nova Scotia</option>
                                        <option value="nunavut">Nunavut</option>
                                        <option value="ontario">Ontario</option>
                                        <option value="prince_edward_island">Prince Edward Island</option>
                                        <option value="quebec">Quebec</option>
                                        <option value="saskatchewan">Saskatchewan</option>
                                        <option value="yukon">Yukon</option>
                                    </select>
                                    @error('canadaProvince') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- GST/HST/PST Rates -->
                                @if(in_array($canadaProvince, ['ontario', 'new_brunswick', 'newfoundland_and_labrador', 'nova_scotia', 'prince_edward_island']))
                                    <div>
                                        <label for="canadaHstRate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">HST Rate (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" id="canadaHstRate" wire:model="canadaHstRate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                        @error('canadaHstRate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <div>
                                        <label for="canadaGstRate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">GST Rate (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" id="canadaGstRate" wire:model="canadaGstRate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                        @error('canadaGstRate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="canadaPstRate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">PST/QST Rate (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" id="canadaPstRate" wire:model="canadaPstRate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                        @error('canadaPstRate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- US Tax Settings -->
                        @if($taxCountry === 'us')
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">US Tax Settings</h4>

                            <div class="space-y-3">
                                <div>
                                    <label for="usState" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">State</label>
                                    <select id="usState" wire:model="usState" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                        <option value="">Select State</option>
                                        <option value="alabama">Alabama</option>
                                        <option value="alaska">Alaska</option>
                                        <option value="arizona">Arizona</option>
                                        <option value="arkansas">Arkansas</option>
                                        <option value="california">California</option>
                                        <option value="colorado">Colorado</option>
                                        <option value="connecticut">Connecticut</option>
                                        <option value="delaware">Delaware</option>
                                        <option value="florida">Florida</option>
                                        <option value="georgia">Georgia</option>
                                        <option value="hawaii">Hawaii</option>
                                        <option value="idaho">Idaho</option>
                                        <option value="illinois">Illinois</option>
                                        <option value="indiana">Indiana</option>
                                        <option value="iowa">Iowa</option>
                                        <option value="kansas">Kansas</option>
                                        <option value="kentucky">Kentucky</option>
                                        <option value="louisiana">Louisiana</option>
                                        <option value="maine">Maine</option>
                                        <option value="maryland">Maryland</option>
                                        <option value="massachusetts">Massachusetts</option>
                                        <option value="michigan">Michigan</option>
                                        <option value="minnesota">Minnesota</option>
                                        <option value="mississippi">Mississippi</option>
                                        <option value="missouri">Missouri</option>
                                        <option value="montana">Montana</option>
                                        <option value="nebraska">Nebraska</option>
                                        <option value="nevada">Nevada</option>
                                        <option value="new_hampshire">New Hampshire</option>
                                        <option value="new_jersey">New Jersey</option>
                                        <option value="new_mexico">New Mexico</option>
                                        <option value="new_york">New York</option>
                                        <option value="north_carolina">North Carolina</option>
                                        <option value="north_dakota">North Dakota</option>
                                        <option value="ohio">Ohio</option>
                                        <option value="oklahoma">Oklahoma</option>
                                        <option value="oregon">Oregon</option>
                                        <option value="pennsylvania">Pennsylvania</option>
                                        <option value="rhode_island">Rhode Island</option>
                                        <option value="south_carolina">South Carolina</option>
                                        <option value="south_dakota">South Dakota</option>
                                        <option value="tennessee">Tennessee</option>
                                        <option value="texas">Texas</option>
                                        <option value="utah">Utah</option>
                                        <option value="vermont">Vermont</option>
                                        <option value="virginia">Virginia</option>
                                        <option value="washington">Washington</option>
                                        <option value="west_virginia">West Virginia</option>
                                        <option value="wisconsin">Wisconsin</option>
                                        <option value="wyoming">Wyoming</option>
                                        <option value="district_of_columbia">District of Columbia</option>
                                    </select>
                                    @error('usState') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="usStateRate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">State Tax Rate (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" id="usStateRate" wire:model="usStateRate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('usStateRate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="usLocalRate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Local Tax Rate (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" id="usLocalRate" wire:model="usLocalRate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('usLocalRate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- UK Tax Settings -->
                        @if($taxCountry === 'uk')
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">UK Tax Settings</h4>

                            <div>
                                <label for="ukVatRate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">VAT Rate (%)</label>
                                <input type="number" step="0.01" min="0" max="100" id="ukVatRate" wire:model="ukVatRate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                @error('ukVatRate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif

                        <!-- Nigeria Tax Settings -->
                        @if($taxCountry === 'nigeria')
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Nigeria Tax Settings</h4>

                            <div>
                                <label for="nigeriaVatRate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">VAT Rate (%)</label>
                                <input type="number" step="0.01" min="0" max="100" id="nigeriaVatRate" wire:model="nigeriaVatRate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                @error('nigeriaVatRate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Payment Settings</h3>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg mb-4">
                        <p class="text-sm text-orange-800 dark:text-orange-300">
                            Configure payment gateway settings to accept payments for invoices and store purchases. These settings will determine how payments are processed.
                        </p>
                    </div>

                    @if(!$canManagePaymentSettings)
                        <div class="bg-zinc-100 dark:bg-zinc-800 p-4 rounded-lg mb-4">
                            <p class="text-zinc-600 dark:text-zinc-400 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                You don't have permission to manage payment settings. Please contact your administrator.
                            </p>
                        </div>
                    @endif

                    <div class="space-y-4" @if(!$canManagePaymentSettings) x-data="" x-on:click.prevent="" class="opacity-60 pointer-events-none" @endif>
                        <div>
                            <label for="paymentEnabled" class="flex items-center">
                                <input type="checkbox" id="paymentEnabled" wire:model="paymentEnabled" class="rounded border-zinc-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Enable payment processing</span>
                            </label>
                        </div>

                        <div>
                            <label for="defaultPaymentGateway" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Default Payment Gateway</label>
                            <select id="defaultPaymentGateway" wire:model="defaultPaymentGateway"
                                    wire:change="setPaymentGateway($event.target.value)"
                                    class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                <option value="none">None</option>
                                <option value="paystack">Paystack</option>
                                <option value="flutterwave">Flutterwave</option>
                                <option value="stripe">Stripe</option>
                            </select>
                            @error('defaultPaymentGateway') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Paystack Settings -->
                        @if($defaultPaymentGateway === 'paystack')
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Paystack Settings</h4>

                            <div class="space-y-3">
                                <div>
                                    <label for="paystackPublicKey" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Public Key</label>
                                    <input type="text" id="paystackPublicKey" wire:model="paystackPublicKey" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('paystackPublicKey') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="paystackSecretKey" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Secret Key</label>
                                    <input type="password" id="paystackSecretKey" wire:model="paystackSecretKey" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('paystackSecretKey') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Flutterwave Settings -->
                        @if($defaultPaymentGateway === 'flutterwave')
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Flutterwave Settings</h4>

                            <div class="space-y-3">
                                <div>
                                    <label for="flutterwavePublicKey" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Public Key</label>
                                    <input type="text" id="flutterwavePublicKey" wire:model="flutterwavePublicKey" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('flutterwavePublicKey') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="flutterwaveSecretKey" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Secret Key</label>
                                    <input type="password" id="flutterwaveSecretKey" wire:model="flutterwaveSecretKey" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('flutterwaveSecretKey') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Stripe Settings -->
                        @if($defaultPaymentGateway === 'stripe')
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Stripe Settings</h4>

                            <div class="space-y-3">
                                <div>
                                    <label for="stripePublicKey" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Publishable Key</label>
                                    <input type="text" id="stripePublicKey" wire:model="stripePublicKey" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('stripePublicKey') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="stripeSecretKey" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Secret Key</label>
                                    <input type="password" id="stripeSecretKey" wire:model="stripeSecretKey" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('stripeSecretKey') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
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
