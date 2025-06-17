<?php

use App\Models\Design;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Design $design;
    public string $name = '';
    public string $category = '';
    public string $description = '';
    public array $materials = [];
    public array $tags = [];
    public array $existingImages = [];
    public $newImages = [];
    public $newPrimaryImage = null;
    public array $imagesToDelete = [];
    public bool $deletePrimaryImage = false;

    public function mount(Design $design): void
    {
        if (!auth()->user()->hasPermission('edit_designs')) {
            session()->flash('error', 'You do not have permission to edit designs.');
            $this->redirect(route('designs.index'));
            return;
        }

        $user = auth()->user();

        // Check if the design belongs to the current user
        if ($design->user_id === $user->id) {
            // Allow access
        }
        // Check if the user is a child user and the design belongs to their parent
        else if ($user->parent_id && $design->user_id === $user->parent_id) {
            // Allow access
        }
        // Check if the user is a parent user and the design belongs to one of their children
        else if (!$user->parent_id) {
            $childrenIds = $user->children()->pluck('id')->toArray();
            if (in_array($design->user_id, $childrenIds)) {
                // Allow access
            } else {
                // Deny access
                session()->flash('error', 'You do not have permission to edit this design.');
                $this->redirect(route('designs.index'));
                return;
            }
        } else {
            // Deny access
            session()->flash('error', 'You do not have permission to edit this design.');
            $this->redirect(route('designs.index'));
            return;
        }

        $this->design = $design;
        $this->name = $design->name;
        $this->category = $design->category ?? '';
        $this->description = $design->description ?? '';
        $this->materials = $design->materials ?? [];
        $this->tags = $design->tags ?? [];
        $this->existingImages = $design->images ?? [];

        // Add empty material and tag if none exist
        if (empty($this->materials)) {
            $this->addMaterial();
        }

        if (empty($this->tags)) {
            $this->addTag();
        }
    }

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

    public function toggleImageDelete($index)
    {
        if (in_array($index, $this->imagesToDelete)) {
            $this->imagesToDelete = array_diff($this->imagesToDelete, [$index]);
        } else {
            $this->imagesToDelete[] = $index;
        }
    }

    public function toggleDeletePrimaryImage()
    {
        $this->deletePrimaryImage = !$this->deletePrimaryImage;
    }

    public function save(): void
    {
        if (!auth()->user()->hasPermission('edit_designs')) {
            session()->flash('error', 'You do not have permission to edit designs.');
            $this->redirect(route('designs.index'));
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'materials.*' => ['nullable', 'string', 'max:100'],
            'tags.*' => ['nullable', 'string', 'max:50'],
            'newImages.*' => ['nullable', 'image', 'max:2048'],
            'newPrimaryImage' => ['nullable', 'image', 'max:2048'],
        ]);

        // Filter out empty materials and tags
        $materials = array_filter($this->materials, fn($material) => !empty($material));
        $tags = array_filter($this->tags, fn($tag) => !empty($tag));

        // For debugging
        \Log::info('Saving design data', [
            'design_id' => $this->design->id,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'materials' => $materials,
            'tags' => $tags,
            'existingImages' => $this->existingImages,
            'imagesToDelete' => $this->imagesToDelete,
            'newImages' => count($this->newImages),
            'deletePrimaryImage' => $this->deletePrimaryImage,
            'newPrimaryImage' => $this->newPrimaryImage ? 'yes' : 'no'
        ]);

        try {
            // Handle primary image
            $primaryImagePath = $this->design->primary_image;

            if ($this->deletePrimaryImage && $primaryImagePath) {
                if (Storage::disk('public')->exists($primaryImagePath)) {
                    Storage::disk('public')->delete($primaryImagePath);
                }
                $primaryImagePath = null;
            }

            if ($this->newPrimaryImage) {
                // Delete old primary image if exists
                if ($primaryImagePath && Storage::disk('public')->exists($primaryImagePath)) {
                    Storage::disk('public')->delete($primaryImagePath);
                }

                $primaryImagePath = $this->newPrimaryImage->store('design-images', 'public');
            }

            // Handle additional images
            $updatedImages = $this->existingImages;

            // Delete images marked for deletion
            foreach ($this->imagesToDelete as $index) {
                if (isset($updatedImages[$index])) {
                    $imagePath = $updatedImages[$index];
                    if (Storage::disk('public')->exists($imagePath)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                    unset($updatedImages[$index]);
                }
            }
            $updatedImages = array_values($updatedImages); // Reindex array

            // Add new images
            foreach ($this->newImages as $image) {
                $imagePath = $image->store('design-images', 'public');
                $updatedImages[] = $imagePath;
            }

            // Update design
            $this->design->name = $this->name;
            $this->design->category = $this->category;
            $this->design->description = $this->description;
            $this->design->materials = $materials;
            $this->design->tags = $tags;
            $this->design->images = $updatedImages;
            $this->design->primary_image = $primaryImagePath;
            $this->design->save();

            \Log::info('Design saved successfully', ['id' => $this->design->id]);
            session()->flash('success', 'Design updated successfully!');

            $this->redirect(route('designs.show', $this->design));
        } catch (\Exception $e) {
            \Log::error('Error saving design', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving design: ' . $e->getMessage());
        }
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Design</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update design details</p>
    </div>

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

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
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Primary Image</label>

                    @if ($design->primary_image && !$deletePrimaryImage)
                        <div class="mb-3">
                            <div class="relative inline-block">
                                <img src="{{ Storage::url($design->primary_image) }}" alt="{{ $design->name }}" class="h-40 w-auto object-cover rounded-md {{ $deletePrimaryImage ? 'opacity-50' : '' }}">
                                <button type="button" wire:click="toggleDeletePrimaryImage" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    <input wire:model="newPrimaryImage" type="file" id="newPrimaryImage" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('newPrimaryImage') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($newPrimaryImage)
                        <div class="mt-2">
                            <img src="{{ $newPrimaryImage->temporaryUrl() }}" class="h-40 w-auto object-cover rounded-md">
                        </div>
                    @endif
                </div>

                <!-- Existing Images -->
                @if (count($existingImages) > 0)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Additional Images</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($existingImages as $index => $image)
                                <div class="relative group">
                                    <img src="{{ Storage::url($image) }}" class="h-24 w-24 object-cover rounded-md {{ in_array($index, $imagesToDelete) ? 'opacity-50' : '' }}">
                                    <button type="button" wire:click="toggleImageDelete({{ $index }})" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-md opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if (in_array($index, $imagesToDelete))
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
                        @if (count($imagesToDelete) > 0)
                            <p class="text-sm text-red-500 mt-2">{{ count($imagesToDelete) }} image(s) marked for deletion</p>
                        @endif
                    </div>
                @endif

                <!-- New Images -->
                <div class="md:col-span-2">
                    <label for="newImages" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Add New Images</label>
                    <input wire:model="newImages" type="file" id="newImages" multiple class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('newImages.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($newImages)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($newImages as $image)
                                <img src="{{ $image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-md">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('designs.show', $design) }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Update Design
                </button>
            </div>
        </form>
    </div>
</div>
