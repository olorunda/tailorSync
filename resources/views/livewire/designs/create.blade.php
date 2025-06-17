<?php

use App\Models\Design;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $category = '';
    public string $description = '';
    public array $materials = [];
    public array $tags = [];
    public $images = [];
    public $primary_image = null;

    public function addMaterial()
    {
        $this->materials[] = '';
    }

    public function removeMaterial($index)
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    public function addTag()
    {
        $this->tags[] = '';
    }

    public function removeTag($index)
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
    }

    public function save(): void
    {
        if (!auth()->user()->hasPermission('create_designs')) {
            session()->flash('error', 'You do not have permission to create designs.');
            $this->redirect(route('designs.index'));
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'materials.*' => ['nullable', 'string', 'max:100'],
            'tags.*' => ['nullable', 'string', 'max:50'],
            'images.*' => ['nullable', 'image', 'max:2048'],
            'primary_image' => ['nullable', 'image', 'max:2048'],
        ]);

        // Filter out empty materials and tags
        $materials = array_filter($this->materials, fn($material) => !empty($material));
        $tags = array_filter($this->tags, fn($tag) => !empty($tag));

        // Store images
        $imagesPaths = [];
        foreach ($this->images as $image) {
            $imagesPaths[] = $image->store('design-images', 'public');
        }

        // Store primary image
        $primaryImagePath = null;
        if ($this->primary_image) {
            $primaryImagePath = $this->primary_image->store('design-images', 'public');
        }

        $design = Design::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'materials' => $materials,
            'tags' => $tags,
            'images' => $imagesPaths,
            'primary_image' => $primaryImagePath,
        ]);

        $this->redirect(route('designs.show', $design));
    }

    public function mount(): void
    {
        if (!auth()->user()->hasPermission('create_designs')) {
            session()->flash('error', 'You do not have permission to create designs.');
            $this->redirect(route('designs.index'));
            return;
        }

        $this->addMaterial();
        $this->addTag();
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
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Create New Design</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Add a new design to your collection</p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Design Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Design Name</label>
                    <input wire:model="name" type="text" id="name" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Category</label>
                    <input wire:model="category" type="text" id="category" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="e.g. Formal, Casual, Traditional">
                    @error('category') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                    <textarea wire:model="description" id="description" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="Describe your design"></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Materials -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Materials</label>
                    <div class="space-y-2">
                        @foreach ($materials as $index => $material)
                            <div class="flex items-center gap-2">
                                <input wire:model="materials.{{ $index }}" type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="e.g. Cotton, Silk, Linen">
                                <button type="button" wire:click="removeMaterial({{ $index }})" class="text-red-500 hover:text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            @error('materials.'.$index) <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        @endforeach
                        <button type="button" wire:click="addMaterial" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 text-sm font-medium">
                            + Add Material
                        </button>
                    </div>
                </div>

                <!-- Tags -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tags</label>
                    <div class="space-y-2">
                        @foreach ($tags as $index => $tag)
                            <div class="flex items-center gap-2">
                                <input wire:model="tags.{{ $index }}" type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" placeholder="e.g. Summer, Wedding, Vintage">
                                <button type="button" wire:click="removeTag({{ $index }})" class="text-red-500 hover:text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            @error('tags.'.$index) <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        @endforeach
                        <button type="button" wire:click="addTag" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 text-sm font-medium">
                            + Add Tag
                        </button>
                    </div>
                </div>

                <!-- Primary Image -->
                <div class="md:col-span-2">
                    <label for="primary_image" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Primary Image</label>
                    <input wire:model="primary_image" type="file" id="primary_image" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('primary_image') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($primary_image)
                        <div class="mt-2">
                            <img src="{{ $primary_image->temporaryUrl() }}" class="h-40 w-auto object-cover rounded-md">
                        </div>
                    @endif
                </div>

                <!-- Additional Images -->
                <div class="md:col-span-2">
                    <label for="images" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Additional Images</label>
                    <input wire:model="images" type="file" id="images" multiple class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('images.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($images)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($images as $image)
                                <img src="{{ $image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-md">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('designs.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Create Design
                </button>
            </div>
        </form>
    </div>
</div>
