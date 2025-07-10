<div class="min-h-screen bg-gradient-to-br from-orange-50 to-zinc-50 dark:from-zinc-900 dark:to-zinc-800 py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">Welcome to TailorFit</h1>
            <p class="text-lg text-zinc-600 dark:text-zinc-300">Let's set up your account in just a few steps</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-10">
            <div class="flex justify-between mb-2">
                @for ($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $i < $currentStep ? 'bg-green-500 text-white' : ($i === $currentStep ? 'bg-orange-500 text-white' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400') }}">
                            @if ($i < $currentStep)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        <span class="text-xs mt-1 text-zinc-500 dark:text-zinc-400">
                            @switch($i)
                                @case(1)
                                    Welcome
                                    @break
                                @case(2)
                                    Business Info
                                    @break
                                @case(3)
                                    Logo
                                    @break
                                @case(4)
                                    Appointments
                                    @break
                                @case(5)
                                    Team
                                    @break
                                @case(6)
                                    Clients
                                    @break
                                @case(7)
                                    Complete
                                    @break
                            @endswitch
                        </span>
                    </div>

                    @if ($i < $totalSteps)
                        <div class="flex-1 flex items-center">
                            <div class="h-1 w-full bg-zinc-200 dark:bg-zinc-700 {{ $i < $currentStep ? 'bg-green-500' : '' }}"></div>
                        </div>
                    @endif
                @endfor
            </div>
        </div>

        <!-- Wizard Content -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl overflow-hidden">
            <div class="p-8">
                <!-- Step 1: Welcome -->
                @if ($currentStep === 1)
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-orange-500 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Welcome to Your Tailoring Business Hub</h2>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-8">
                            We're excited to help you manage your tailoring business more efficiently. This quick setup will help you get started with all the essential features.
                        </p>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-8">
                            In the next few steps, you'll set up your business information, upload your logo, add team members, and import your clients to get started quickly.
                        </p>
                        <button wire:click="nextStep" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                            Let's Get Started
                        </button>
                    </div>
                @endif

                <!-- Step 2: Business Information -->
                @if ($currentStep === 2)
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Business Information</h2>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">
                            Let's set up your business details. This information will be used on your invoices and client communications.
                        </p>

                        <div class="space-y-4">
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
                        </div>
                    </div>
                @endif

                <!-- Step 3: Logo Upload -->
                @if ($currentStep === 3)
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Upload Your Logo</h2>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">
                            Your logo will appear on invoices, receipts, and your dashboard. Upload a high-quality image for the best results.
                        </p>

                        <div class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 mb-6">
                            @if ($logo)
                                <div class="mb-4">
                                    <img src="{{ $logo->temporaryUrl() }}" alt="Logo Preview" class="max-w-xs max-h-48 rounded">
                                </div>
                                <button wire:click="$set('logo', null)" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                    Remove Logo
                                </button>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <label for="logo" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors cursor-pointer">
                                    Choose Logo
                                </label>
                                <input type="file" id="logo" wire:model="logo" class="hidden">
                                <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-2">PNG, JPG or GIF up to 1MB</p>
                            @endif

                            @error('logo') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                        </div>

                        <p class="text-zinc-600 dark:text-zinc-300 text-sm">
                            Note: You can skip this step and upload your logo later from the settings page.
                        </p>
                    </div>
                @endif

                <!-- Step 4: Appointment Settings -->
                @if ($currentStep === 4)
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Appointment Settings</h2>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">
                            Configure your business hours and available days for appointments. These settings will determine when clients can book appointments with you.
                        </p>

                        <div class="space-y-6">
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

                        <p class="mt-6 text-zinc-600 dark:text-zinc-300 text-sm">
                            Note: You can update these settings later from the business settings page.
                        </p>
                    </div>
                @endif

                <!-- Step 5: Team Members -->
                @if ($currentStep === 5)
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Add Team Members</h2>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">
                            Add team members who will have access to your TailorFit account. You can set their permissions later.
                        </p>

                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="teamMemberName" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
                                    <input type="text" id="teamMemberName" wire:model="teamMemberName" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('teamMemberName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="teamMemberEmail" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
                                    <input type="email" id="teamMemberEmail" wire:model="teamMemberEmail" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                                    @error('teamMemberEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <button wire:click="addTeamMember" class="px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white rounded-lg font-medium transition-colors">
                                Add Team Member
                            </button>
                        </div>

                        @if (count($teamMembers) > 0)
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden mb-6">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($teamMembers as $index => $member)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">{{ $member['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $member['email'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button wire:click="removeTeamMember({{ $index }})" class="text-red-500 hover:text-red-700">
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <p class="text-zinc-600 dark:text-zinc-300 text-sm">
                            Note: You can skip this step and add team members later from the team management page.
                        </p>
                    </div>
                @endif

                <!-- Step 6: Client Import -->
                @if ($currentStep === 6)
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Import Clients</h2>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">
                            Import your existing clients from a CSV file. This will help you get started quickly with your client database.
                        </p>

                        <div class="mb-6">
                            <h3 class="text-xl font-medium text-zinc-900 dark:text-white mb-3">Instructions</h3>
                            <p class="text-base text-zinc-600 dark:text-zinc-300 mb-4">
                                Please upload a CSV file with the following columns:
                            </p>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg mb-5">
                                <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2">
                                    <li><span class="font-medium text-zinc-900 dark:text-zinc-100">name</span> (required) - Client's full name</li>
                                    <li><span class="font-medium text-zinc-900 dark:text-zinc-100">email</span> (optional) - Client's email address</li>
                                    <li><span class="font-medium text-zinc-900 dark:text-zinc-100">phone</span> (optional) - Client's phone number</li>
                                    <li><span class="font-medium text-zinc-900 dark:text-zinc-100">address</span> (optional) - Client's address</li>
                                    <li><span class="font-medium text-zinc-900 dark:text-zinc-100">notes</span> (optional) - Additional notes about the client</li>
                                </ul>
                            </div>
                            <div class="flex">
                                <button wire:click="downloadSampleCsv" class="inline-flex items-center px-5 py-3 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-lg text-base font-medium transition-colors shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Download Sample CSV
                                </button>
                            </div>
                        </div>

                        <div class="mt-8">
                            <label for="csvFile" class="block text-base font-medium text-zinc-700 dark:text-zinc-300 mb-2">CSV File</label>
                            <div class="mt-2 flex justify-center px-4 sm:px-6 pt-6 pb-8 border-2 border-zinc-300 dark:border-zinc-600 border-dashed rounded-lg">
                                <div class="space-y-2 text-center">
                                    <svg class="mx-auto h-16 w-16 text-zinc-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex flex-col sm:flex-row items-center justify-center text-base text-zinc-600 dark:text-zinc-400">
                                        <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-zinc-800 rounded-lg font-medium text-orange-600 dark:text-orange-500 hover:text-orange-500 dark:hover:text-orange-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500 px-4 py-2 mb-2 sm:mb-0">
                                            <span>Upload a file</span>
                                            <input wire:model="csvFile" id="file-upload" name="file-upload" type="file" class="sr-only" accept=".csv,.txt">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        CSV file up to 1MB
                                    </p>
                                </div>
                            </div>
                            @error('csvFile') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                        </div>

                        @if ($csvFile)
                        <div class="mt-4 bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-base text-zinc-700 dark:text-zinc-300">{{ $csvFile->getClientOriginalName() }}</span>
                            </div>
                            <div class="mt-4">
                                <button wire:click="importClients" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="importClients">Import Clients</span>
                                    <span wire:loading wire:target="importClients">
                                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                </button>
                            </div>
                        </div>
                        @endif

                        @if ($processing)
                        <div class="mt-8">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-16 w-16 border-t-3 border-b-3 border-orange-600"></div>
                            </div>
                            <p class="text-center mt-4 text-base text-zinc-600 dark:text-zinc-400">Processing your file...</p>
                        </div>
                        @endif

                        @if ($importedCount > 0 || $errorCount > 0)
                        <div class="mt-8">
                            <h3 class="text-xl font-medium text-zinc-900 dark:text-zinc-100 mb-4">Import Results</h3>

                            @if ($importedCount > 0)
                            <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 p-4 mb-5 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-base text-green-700 dark:text-green-200">
                                            Successfully imported {{ $importedCount }} clients.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if ($errorCount > 0)
                            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 mb-5 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-base text-red-700 dark:text-red-200">
                                            Failed to import {{ $errorCount }} clients.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 bg-zinc-50 dark:bg-zinc-700 p-5 rounded-lg max-h-72 overflow-y-auto shadow-sm">
                                <h4 class="text-base font-medium text-zinc-900 dark:text-zinc-100 mb-3">Error Details:</h4>
                                <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2">
                                    @foreach ($importErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                        @endif

                        <p class="mt-6 text-zinc-600 dark:text-zinc-300 text-sm">
                            Note: You can skip this step and import clients later from the clients page.
                        </p>
                    </div>
                @endif

                <!-- Step 7: Completion -->
                @if ($currentStep === 7)
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-green-500 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">You're All Set!</h2>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-8">
                            Congratulations! Your TailorFit account is now set up and ready to use. You can now start managing your tailoring business more efficiently.
                        </p>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-8">
                            You can always update your business information, logo, and team members from the settings page.
                        </p>
                        <button wire:click="completeOnboarding" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            Go to Dashboard
                        </button>
                    </div>
                @endif
            </div>

            <!-- Navigation Buttons -->
            <div class="px-8 py-4 bg-zinc-50 dark:bg-zinc-700 flex justify-between">
                @if ($currentStep > 1)
                    <button wire:click="previousStep" class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 rounded-lg font-medium hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors">
                        Previous
                    </button>
                @else
                    <div></div>
                @endif

                @if ($currentStep < $totalSteps)
                    <button wire:click="nextStep" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                        Next
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
