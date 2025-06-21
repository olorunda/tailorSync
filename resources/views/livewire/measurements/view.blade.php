<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('clients.index') }}">{{ __('Clients') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('clients.show', $client) }}">{{ $client->name }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item current>{{ __('Measurement Details') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="mt-4">
                <flux:heading size="xl">{{ __('Measurement Details') }}</flux:heading>
                <flux:subheading size="lg">{{ $measurement->name ?? __('Measurement from') . ' ' . $measurement->measurement_date->format('M d, Y') }}</flux:subheading>
            </div>
        </div>

        <div class="mt-4 md:mt-0 flex space-x-2">
            @if(auth()->user()->hasPermission('edit_measurements'))
                <a href="{{ route('measurements.edit', [$client, $measurement]) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 dark:bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 dark:hover:bg-orange-600 focus:bg-orange-700 dark:focus:bg-orange-600 active:bg-orange-900 dark:active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('Edit Measurement') }}
                </a>
            @endif

            <a href="{{ route('clients.show', $client) }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 border border-transparent rounded-md font-semibold text-xs text-zinc-900 dark:text-zinc-100 uppercase tracking-widest hover:bg-zinc-300 dark:hover:bg-zinc-600 focus:bg-zinc-300 dark:focus:bg-zinc-600 active:bg-zinc-400 dark:active:bg-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('Back to Client') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Standard Measurements -->
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Standard Measurements') }}</flux:heading>
                    <flux:separator variant="subtle" class="mb-4" />

                    @if(count($standardMeasurements) > 0)
                        <div class="space-y-4">
                            @foreach($standardMeasurements as $key => $value)
                                <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-2">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ __(ucfirst(str_replace('_', ' ', $key))) }}</span>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('No standard measurements recorded.') }}</p>
                    @endif
                </div>

                <!-- Custom Measurements -->
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Custom Measurements') }}</flux:heading>
                    <flux:separator variant="subtle" class="mb-4" />

                    @if(count($customMeasurements) > 0)
                        <div class="space-y-4">
                            @foreach($customMeasurements as $key => $value)
                                <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-2">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ __(ucfirst(str_replace('_', ' ', $key))) }}</span>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('No custom measurements recorded.') }}</p>
                    @endif

                    @if(count($measurementTypes) > 0 && auth()->user()->hasPermission('edit_measurements'))
                        <div class="mt-6">
                            <a href="{{ route('measurements.edit', [$client, $measurement]) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-700 dark:hover:text-orange-400">
                                {{ __('Add custom measurements') }} →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Measurement Notes -->
            @if($measurement->notes)
                <div class="mt-8">
                    <flux:heading size="md" class="mb-4">{{ __('Notes') }}</flux:heading>
                    <flux:separator variant="subtle" class="mb-4" />

                    <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                        <p class="text-zinc-600 dark:text-zinc-300 whitespace-pre-line">{{ $measurement->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Measurement Photos -->
            @if($measurement->photos && count($measurement->photos) > 0)
                <div class="mt-8">
                    <flux:heading size="md" class="mb-4">{{ __('Photos') }}</flux:heading>
                    <flux:separator variant="subtle" class="mb-4" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($measurement->photos as $photo)
                            <div class="relative group">
                                <img src="{{ Storage::url($photo) }}" alt="{{ __('Measurement photo') }}" class="w-full h-48 object-cover rounded-lg">
                                <a href="{{ Storage::url($photo) }}" target="_blank" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Measurement Metadata -->
            <div class="mt-8 text-sm text-zinc-500 dark:text-zinc-400">
                <p>{{ __('Created') }}: {{ $measurement->created_at->format('M d, Y h:i A') }}</p>
                @if($measurement->created_at != $measurement->updated_at)
                    <p>{{ __('Last updated') }}: {{ $measurement->updated_at->format('M d, Y h:i A') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
