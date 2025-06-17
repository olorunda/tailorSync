<?php

use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Appointment $appointment;
    public string $title = '';
    public string $description = '';
    public string $date = '';
    public string $time = '';
    public ?int $client_id = null;
    public string $location = '';
    public string $status = '';

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment;
        $this->title = $appointment->title;
        $this->description = $appointment->description ?? '';
        $this->date = $appointment->date->format('Y-m-d');
        $this->time = $appointment->date->format('H:i');
        $this->client_id = $appointment->client_id;
        $this->location = $appointment->location ?? '';
        $this->status = $appointment->status;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled'],
        ]);

        // Combine date and time
        $dateTime = $this->date . ' ' . $this->time . ':00';

        $this->appointment->title = $this->title;
        $this->appointment->description = $this->description;
        $this->appointment->date = $dateTime;
        $this->appointment->client_id = $this->client_id;
        $this->appointment->location = $this->location;
        $this->appointment->status = $this->status;
        $this->appointment->save();

        $this->redirect(route('appointments.index'));
    }

    public function with(): array
    {
        return [
            'clients' => Client::where('user_id', Auth::id())->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Appointment</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update appointment details</p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Title</label>
                    <input wire:model="title" type="text" id="title" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="client_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Client (Optional)</label>
                    <select wire:model="client_id" id="client_id" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="">Select a client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Date</label>
                    <input wire:model="date" type="date" id="date" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="time" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Time</label>
                    <input wire:model="time" type="time" id="time" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Location</label>
                    <input wire:model="location" type="text" id="location" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('location') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                    <select wire:model="status" id="status" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                    <textarea wire:model="description" id="description" rows="4" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('appointments.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
