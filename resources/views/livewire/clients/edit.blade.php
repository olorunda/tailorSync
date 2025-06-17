<?php

use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Client $client;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public ?string $notes = '';
    public $photo = null;
    public $newPhoto = null;

    public function mount(Client $client)
    {
        if (!auth()->user()->hasPermission('edit_clients')) {
            session()->flash('error', 'You do not have permission to edit clients.');
            return $this->redirect(route('clients.index'));
        }

        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email ?? '';
        $this->phone = $client->phone ?? '';
        $this->address = $client->address ?? '';
        $this->notes = $client->notes ?? '';
        $this->photo = $client->photo;
    }

    public function save()
    {
        if (!auth()->user()->hasPermission('edit_clients')) {
            session()->flash('error', 'You do not have permission to edit clients.');
            return $this->redirect(route('clients.index'));
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'newPhoto' => ['nullable', 'image', 'max:1024'],
        ]);

        if ($this->newPhoto) {
            // Delete old photo if exists
            if ($this->photo && Storage::disk('public')->exists($this->photo)) {
                Storage::disk('public')->delete($this->photo);
            }

            $photoPath = $this->newPhoto->store('client-photos', 'public');
            $this->client->photo = $photoPath;
        }

        $this->client->name = $this->name;
        $this->client->email = $this->email;
        $this->client->phone = $this->phone;
        $this->client->address = $this->address;
        $this->client->notes = $this->notes;
        $this->client->save();

        $this->redirect(route('clients.show', $this->client));
    }

    public function deletePhoto()
    {
        if (!auth()->user()->hasPermission('edit_clients')) {
            session()->flash('error', 'You do not have permission to edit clients.');
            return;
        }

        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            Storage::disk('public')->delete($this->photo);
        }

        $this->client->photo = null;
        $this->client->save();
        $this->photo = null;
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

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Client</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update client information</p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
                    <input wire:model="name" type="text" id="name" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
                    <input wire:model="email" type="email" id="email" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Phone</label>
                    <input wire:model="phone" type="text" id="phone" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Photo</label>

                    @if ($photo && !$newPhoto)
                        <div class="mb-3 flex items-center">
                            <img src="{{ Storage::url($photo) }}" alt="{{ $client->name }}" class="h-16 w-16 rounded-full object-cover mr-3">
                            <button type="button" wire:click="deletePhoto" wire:confirm="Are you sure you want to remove this photo?" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Remove Photo
                            </button>
                        </div>
                    @endif

                    <input wire:model="newPhoto" type="file" id="newPhoto" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('newPhoto') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($newPhoto)
                        <div class="mt-2">
                            <img src="{{ $newPhoto->temporaryUrl() }}" class="h-16 w-16 object-cover rounded-full">
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Address</label>
                    <input wire:model="address" type="text" id="address" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                    <textarea wire:model="notes" id="notes" rows="4" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('clients.show', $client) }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
