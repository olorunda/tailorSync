<?php

use App\Models\Client;
use App\Models\Measurement;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Client $client;
    public string $name = '';
    public array $measurements = [
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
    public $photos = [];

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'measurements.chest' => ['nullable', 'string', 'max:50'],
            'measurements.waist' => ['nullable', 'string', 'max:50'],
            'measurements.hip' => ['nullable', 'string', 'max:50'],
            'measurements.shoulder' => ['nullable', 'string', 'max:50'],
            'measurements.sleeve' => ['nullable', 'string', 'max:50'],
            'measurements.inseam' => ['nullable', 'string', 'max:50'],
            'measurements.neck' => ['nullable', 'string', 'max:50'],
            'measurements.thigh' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos.*' => ['nullable', 'image', 'max:1024'],
        ]);

        $photosPaths = [];
        foreach ($this->photos as $photo) {
            $photosPaths[] = $photo->store('measurement-photos', 'public');
        }

        // For debugging
        \Log::info('Creating new measurement', [
            'client_id' => $this->client->id,
            'user_id' => Auth::id(),
            'name' => $this->name,
            'measurements' => $this->measurements,
            'notes' => $this->notes,
            'photos' => $photosPaths
        ]);

        try {
            $measurement = Measurement::create([
                'client_id' => $this->client->id,
                'user_id' => Auth::id(),
                'name' => $this->name ?: 'Measurement ' . date('Y-m-d'),
                'measurements' => $this->measurements,
                'notes' => $this->notes,
                'photos' => $photosPaths,
                'measurement_date' => now()->toDateString(),
            ]);

            \Log::info('Measurement created successfully', ['id' => $measurement->id]);
            session()->flash('success', 'Measurement created successfully!');
        } catch (\Exception $e) {
            \Log::error('Error creating measurement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error creating measurement: ' . $e->getMessage());
            return;
        }

        $this->redirect(route('clients.show', $this->client));
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Add Measurements for {{ $client->name }}</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Record client measurements for future reference</p>
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
                <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Measurement Name (Optional)</label>
                <input wire:model="name" type="text" id="name" placeholder="e.g. Summer 2023 Suit" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Upper Body</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="chest" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Chest</label>
                            <input wire:model="measurements.chest" type="text" id="chest" placeholder="e.g. 40 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.chest') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="waist" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Waist</label>
                            <input wire:model="measurements.waist" type="text" id="waist" placeholder="e.g. 34 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.waist') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="shoulder" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Shoulder</label>
                            <input wire:model="measurements.shoulder" type="text" id="shoulder" placeholder="e.g. 18 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.shoulder') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="sleeve" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sleeve Length</label>
                            <input wire:model="measurements.sleeve" type="text" id="sleeve" placeholder="e.g. 25 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.sleeve') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Lower Body</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="hip" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Hip</label>
                            <input wire:model="measurements.hip" type="text" id="hip" placeholder="e.g. 42 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.hip') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="inseam" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Inseam</label>
                            <input wire:model="measurements.inseam" type="text" id="inseam" placeholder="e.g. 32 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.inseam') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="thigh" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Thigh</label>
                            <input wire:model="measurements.thigh" type="text" id="thigh" placeholder="e.g. 22 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.thigh') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="neck" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Neck</label>
                            <input wire:model="measurements.neck" type="text" id="neck" placeholder="e.g. 16 inches" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                            @error('measurements.neck') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="photos" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Photos (Optional)</label>
                <input wire:model="photos" type="file" id="photos" multiple class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                @error('photos.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                @if ($photos)
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($photos as $photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-md">
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                <textarea wire:model="notes" id="notes" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="Any additional notes about these measurements"></textarea>
                @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('clients.show', $client) }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Save Measurements
                </button>
            </div>
        </form>
    </div>
</div>
