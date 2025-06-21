<?php

use App\Models\Client;
use App\Models\Measurement;
use App\Models\MeasurementType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Client $client;
    public Measurement $measurement;
    public string $name = '';
    public array $measurementData = [
        'chest' => '',
        'waist' => '',
        'hip' => '',
        'shoulder' => '',
        'sleeve' => '',
        'inseam' => '',
        'neck' => '',
        'thigh' => '',
    ];
    public ?string $notes = '';
    public array $existingPhotos = [];
    public $newPhotos = [];
    public array $photosToDelete = [];
    public array $customMeasurements = [];
    public array $measurementTypes = [];

    public function mount(Client $client, Measurement $measurement): void
    {
        $this->client = $client;
        $this->measurement = $measurement;
        $this->name = $measurement->name ?? '';
        $this->notes = $measurement->notes ?? '';
        $this->existingPhotos = $measurement->photos ?? [];

        // Load existing measurements
        $measurements = $measurement->measurements ?? [];

        // Load custom measurements
        $this->loadMeasurementTypes();
        $this->loadCustomMeasurements();

        // For debugging
        \Log::info('Loading measurement data', [
            'measurement_id' => $measurement->id,
            'measurements' => $measurements,
            'additional_measurements' => $this->measurement->additional_measurements
        ]);

        foreach ($this->measurementData as $key => $value) {
            if (isset($measurements[$key])) {
                $this->measurementData[$key] = $measurements[$key];
            }
        }
    }

    public function loadMeasurementTypes(): void
    {
        $this->measurementTypes = MeasurementType::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadCustomMeasurements(): void
    {
        // Initialize custom measurements array with empty values
        foreach ($this->measurementTypes as $type) {
            $this->customMeasurements[$type['name']] = '';
        }

        // Load existing custom measurements
        $additionalMeasurements = $this->measurement->additional_measurements ?? [];
        foreach ($additionalMeasurements as $key => $value) {
            if (isset($this->customMeasurements[$key])) {
                $this->customMeasurements[$key] = $value;
            }
        }
    }

    public function save(): void
    {
        $validationRules = [
            'name' => ['nullable', 'string', 'max:255'],
            'measurementData.chest' => ['nullable', 'string', 'max:50'],
            'measurementData.waist' => ['nullable', 'string', 'max:50'],
            'measurementData.hip' => ['nullable', 'string', 'max:50'],
            'measurementData.shoulder' => ['nullable', 'string', 'max:50'],
            'measurementData.sleeve' => ['nullable', 'string', 'max:50'],
            'measurementData.inseam' => ['nullable', 'string', 'max:50'],
            'measurementData.neck' => ['nullable', 'string', 'max:50'],
            'measurementData.thigh' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'newPhotos.*' => ['nullable', 'image', 'max:1024'],
        ];

        // Add validation rules for custom measurements
        foreach (array_keys($this->customMeasurements) as $key) {
            $validationRules["customMeasurements.$key"] = ['nullable', 'string', 'max:50'];
        }

        $validated = $this->validate($validationRules);

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
            $photoPath = $photo->store('measurement-photos', 'public');
            $updatedPhotos[] = $photoPath;
        }

        // For debugging
        \Log::info('Saving measurement data', [
            'measurement_id' => $this->measurement->id,
            'name' => $this->name,
            'measurementData' => $this->measurementData,
            'customMeasurements' => $this->customMeasurements,
            'notes' => $this->notes,
            'photos' => $updatedPhotos
        ]);

        $this->measurement->name = $this->name ?: 'Measurement ' . date('Y-m-d');
        $this->measurement->measurements = $this->measurementData;
        $this->measurement->additional_measurements = $this->customMeasurements;
        $this->measurement->notes = $this->notes;
        $this->measurement->photos = $updatedPhotos;
        $this->measurement->measurement_date = now()->toDateString();

        try {
            $this->measurement->save();
            \Log::info('Measurement saved successfully', ['id' => $this->measurement->id]);
            session()->flash('success', 'Measurement updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Error saving measurement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving measurement: ' . $e->getMessage());
            return;
        }

        $this->redirect(route('clients.show', $this->client));
    }

    public function togglePhotoDelete($index): void
    {
        if (in_array($index, $this->photosToDelete)) {
            $this->photosToDelete = array_diff($this->photosToDelete, [$index]);
        } else {
            $this->photosToDelete[] = $index;
        }
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Measurements for {{ $client->name }}</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update client measurements</p>
    </div>

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Measurement Name</label>
                <input wire:model="name" type="text" id="name" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Upper Body</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="chest" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Chest</label>
                            <input wire:model="measurementData.chest" type="text" id="chest" placeholder="e.g. 40 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.chest') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="waist" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Waist</label>
                            <input wire:model="measurementData.waist" type="text" id="waist" placeholder="e.g. 34 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.waist') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="shoulder" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Shoulder</label>
                            <input wire:model="measurementData.shoulder" type="text" id="shoulder" placeholder="e.g. 18 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.shoulder') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="sleeve" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sleeve Length</label>
                            <input wire:model="measurementData.sleeve" type="text" id="sleeve" placeholder="e.g. 25 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.sleeve') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Lower Body</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="hip" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Hip</label>
                            <input wire:model="measurementData.hip" type="text" id="hip" placeholder="e.g. 42 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.hip') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="inseam" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Inseam</label>
                            <input wire:model="measurementData.inseam" type="text" id="inseam" placeholder="e.g. 32 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.inseam') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="thigh" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Thigh</label>
                            <input wire:model="measurementData.thigh" type="text" id="thigh" placeholder="e.g. 22 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.thigh') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="neck" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Neck</label>
                            <input wire:model="measurementData.neck" type="text" id="neck" placeholder="e.g. 16 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurementData.neck') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Measurements Section -->
            @if(count($measurementTypes) > 0)
                <div class="mt-8">
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Custom Measurements</h3>
                    <flux:separator variant="subtle" class="mb-4" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($measurementTypes as $type)
                            <div>
                                <label for="custom-{{ $type['id'] }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">{{ $type['name'] }} ({{ $type['unit'] }})</label>
                                <input
                                    wire:model="customMeasurements.{{ $type['name'] }}"
                                    type="text"
                                    id="custom-{{ $type['id'] }}"
                                    placeholder="e.g. 25 {{ $type['unit'] }}"
                                    class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                >
                                @error('customMeasurements.' . $type['name']) <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                @if($type['description'])
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $type['description'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2 text-sm">
                        <a href="{{ route('settings.measurements') }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-700 dark:hover:text-orange-400" target="_blank">
                            {{ __('Manage custom measurement types') }} →
                        </a>
                    </div>
                </div>
            @endif

            <!-- Existing Photos -->
            @if (count($existingPhotos) > 0)
                <div>
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
            <div>
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

            <div>
                <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                <textarea wire:model="notes" id="notes" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('clients.show', $client) }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Update Measurements
                </button>
            </div>
        </form>
    </div>
</div>
