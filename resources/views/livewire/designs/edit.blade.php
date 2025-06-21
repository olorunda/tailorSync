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
    public $canvas_image = null;
    public $use_canvas = false;

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

    public function saveCanvasImage($imageData)
    {

        // Remove the data URL prefix
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);

        // Decode the base64 data
        $imageData = base64_decode($imageData);

        // Generate a unique filename
        $filename = 'canvas_' . time() . '.png';

        // Save the file to storage
        $path = 'public/design-images/' . $filename;
        Storage::disk('public')->put('design-images/' . $filename, $imageData);


        // Return the public path
        $this->canvas_image = 'design-images/' . $filename;

        // Update the primary image if it's not set
        if (!$this->newPrimaryImage && !$this->design->primary_image) {
            $this->newPrimaryImage = $this->canvas_image;
        }
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
            'newPrimaryImage' => ['nullable'],
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

                if (is_string($this->newPrimaryImage) && strpos($this->newPrimaryImage, 'design-images/') === 0) {
                    // Canvas image already stored
                    $primaryImagePath = $this->newPrimaryImage;
                } else {
                    // Uploaded image
                    $primaryImagePath = $this->newPrimaryImage->store('design-images', 'public');
                }
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

            // Add canvas image to images array if it exists
            if ($this->canvas_image) {
                $updatedImages[] = $this->canvas_image;
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
    <style>
        .cursor-eraser {
            cursor: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2'><path d='M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6'></path><path d='M15.5 3.5a2.12 2.12 0 0 1 3 3L12 13 9 10l6.5-6.5z'></path></svg>") 0 24, auto;
        }
    </style>
    <script>
        function canvasDrawing() {
            return {
                canvas: null,
                ctx: null,
                isDrawing: false,
                color: '#000000',
                brushSize: 3,
                lastX: 0,
                lastY: 0,
                isEraser: false,
                isFullScreen: false,

                init() {
                    this.canvas = document.getElementById('design-canvas');
                    this.ctx = this.canvas.getContext('2d');
                    this.ctx.fillStyle = 'white';
                    this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
                    this.setupCanvas();

                    // Add event listener for fullscreenchange event
                    document.addEventListener('fullscreenchange', () => {
                        if (!document.fullscreenElement) {
                            // User exited full screen mode
                            this.isFullScreen = false;
                        }
                    });

                    // Add event listeners for browser-specific fullscreenchange events
                    document.addEventListener('webkitfullscreenchange', () => {
                        if (!document.webkitFullscreenElement) {
                            // User exited full screen mode
                            this.isFullScreen = false;
                        }
                    });

                    document.addEventListener('mozfullscreenchange', () => {
                        if (!document.mozFullScreenElement) {
                            // User exited full screen mode
                            this.isFullScreen = false;
                        }
                    });

                    document.addEventListener('msfullscreenchange', () => {
                        if (!document.msFullscreenElement) {
                            // User exited full screen mode
                            this.isFullScreen = false;
                        }
                    });
                },

                setupCanvas() {
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';
                    this.ctx.strokeStyle = this.color;
                    this.ctx.lineWidth = this.brushSize;
                },

                setColor(color) {
                    this.color = color;
                    this.ctx.strokeStyle = color;
                },

                setBrushSize(size) {
                    this.brushSize = size;
                    this.ctx.lineWidth = size;
                },

                toggleEraser() {
                    this.isEraser = !this.isEraser;
                    if (this.isEraser) {
                        // Store the current color and set to white for erasing
                        this._previousColor = this.color;
                        this.ctx.strokeStyle = 'white';
                        // Change cursor to eraser
                        this.canvas.classList.add('cursor-eraser');
                        this.canvas.classList.remove('cursor-crosshair');
                    } else {
                        // Restore the previous color
                        this.color = this._previousColor || '#000000';
                        this.ctx.strokeStyle = this.color;
                        // Change cursor back to crosshair
                        this.canvas.classList.remove('cursor-eraser');
                        this.canvas.classList.add('cursor-crosshair');
                    }
                },

                startDrawing(e) {
                    this.isDrawing = true;
                    const rect = this.canvas.getBoundingClientRect();
                    const scaleX = this.canvas.width / rect.width;
                    const scaleY = this.canvas.height / rect.height;

                    // Get coordinates from either mouse, touch, or pointer event
                    const clientX = e.clientX || (e.touches && e.touches[0].clientX) || (e.changedTouches && e.changedTouches[0].clientX);
                    const clientY = e.clientY || (e.touches && e.touches[0].clientY) || (e.changedTouches && e.changedTouches[0].clientY);

                    this.lastX = (clientX - rect.left) * scaleX;
                    this.lastY = (clientY - rect.top) * scaleY;
                },

                draw(e) {
                    if (!this.isDrawing) return;

                    const rect = this.canvas.getBoundingClientRect();
                    const scaleX = this.canvas.width / rect.width;
                    const scaleY = this.canvas.height / rect.height;

                    // Get coordinates from either mouse, touch, or pointer event
                    const clientX = e.clientX || (e.touches && e.touches[0].clientX) || (e.changedTouches && e.changedTouches[0].clientX);
                    const clientY = e.clientY || (e.touches && e.touches[0].clientY) || (e.changedTouches && e.changedTouches[0].clientY);

                    const currentX = (clientX - rect.left) * scaleX;
                    const currentY = (clientY - rect.top) * scaleY;

                    // Adjust line width based on pressure if available (for digital pens)
                    if (e.pressure && e.pressure !== 0.5) {
                        // Pressure is usually between 0 and 1, with 0.5 being the default
                        const pressureAdjustedSize = this.brushSize * (e.pressure * 1.5);
                        this.ctx.lineWidth = Math.max(1, pressureAdjustedSize);
                    } else {
                        this.ctx.lineWidth = this.brushSize;
                    }

                    this.ctx.beginPath();
                    this.ctx.moveTo(this.lastX, this.lastY);
                    this.ctx.lineTo(currentX, currentY);
                    this.ctx.stroke();

                    this.lastX = currentX;
                    this.lastY = currentY;
                },

                handlePointerStart(e) {
                    // For digital pen and pointer devices
                    e.preventDefault();
                    this.startDrawing(e);
                },

                handlePointerMove(e) {
                    // For digital pen and pointer devices
                    e.preventDefault();
                    this.draw(e);
                },

                handleTouchStart(e) {
                    e.preventDefault();
                    if (e.touches.length === 1) {
                        const touch = e.touches[0];
                        const mouseEvent = new MouseEvent('mousedown', {
                            clientX: touch.clientX,
                            clientY: touch.clientY
                        });
                        this.startDrawing(mouseEvent);
                    }
                },

                handleTouchMove(e) {
                    e.preventDefault();
                    if (e.touches.length === 1) {
                        const touch = e.touches[0];
                        const mouseEvent = new MouseEvent('mousemove', {
                            clientX: touch.clientX,
                            clientY: touch.clientY
                        });
                        this.draw(mouseEvent);
                    }
                },

                stopDrawing() {
                    this.isDrawing = false;
                },

                clearCanvas() {
                    this.ctx.fillStyle = 'white';
                    this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
                },

                toggleFullScreen(event) {
                    // Toggle the fullscreen state
                    this.isFullScreen = !this.isFullScreen;
                    const canvasContainer = this.canvas.parentElement;

                    if (this.isFullScreen) {
                        // Enter full screen
                        if (canvasContainer.requestFullscreen) {
                            canvasContainer.requestFullscreen();
                        } else if (canvasContainer.webkitRequestFullscreen) { /* Safari */
                            canvasContainer.webkitRequestFullscreen();
                        } else if (canvasContainer.msRequestFullscreen) { /* IE11 */
                            canvasContainer.msRequestFullscreen();
                        }
                    } else {
                        // Exit full screen - prevent default behavior that might trigger save
                        try {
                            if (document.exitFullscreen) {
                                document.exitFullscreen();
                            } else if (document.webkitExitFullscreen) { /* Safari */
                                document.webkitExitFullscreen();
                            } else if (document.msExitFullscreen) { /* IE11 */
                                document.msExitFullscreen();
                            }
                        } catch (e) {
                            console.error('Error exiting fullscreen:', e);
                        }
                    }

                    // Prevent event from bubbling up which might trigger other handlers
                    if (event) {
                        event.stopPropagation();
                        event.preventDefault();
                    }
                    return false;
                },

                saveCanvasImage(event) {
                    // If event is provided, prevent default form submission
                    if (event) {
                        event.preventDefault();
                    }

                    const imageData = this.canvas.toDataURL('image/png');
                    // Find the specific Livewire component that contains the canvas
                    const canvasContainer = this.canvas.closest('[wire\\:id]');
                    if (canvasContainer) {
                        const livewireComponent = Livewire.find(canvasContainer.getAttribute('wire:id'));
                        livewireComponent.call('saveCanvasImage', imageData)
                            .then(() => {
                                // After the canvas image is saved, submit the form using Livewire
                                if (event) {
                                    // Call the save method directly
                                    livewireComponent.call('save');
                                }
                            });
                    } else {
                        console.error('Could not find the Livewire component containing the canvas');
                    }
                }
            }
        }
    </script>
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
        <form wire:submit="save" class="savefor p-6 space-y-6" x-data="canvasDrawing()">
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

                <!-- Design Images Section -->
                <div class="md:col-span-2" x-data="{ activeTab: 'upload' }">
                    <div class="mb-4 border-b border-zinc-200 dark:border-zinc-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="mr-2">
                                <a href="#"
                                   @click.prevent="activeTab = 'upload'; $wire.use_canvas = false"
                                   :class="activeTab === 'upload' ? 'text-orange-600 border-orange-600 dark:text-orange-500 dark:border-orange-500' : 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent'"
                                   class="inline-block p-4 border-b-2 rounded-t-lg">
                                    Upload Images
                                </a>
                            </li>
                            <li class="mr-2">
                                <a href="#"
                                   @click.prevent="activeTab = 'sketch'; $wire.use_canvas = true"
                                   :class="activeTab === 'sketch' ? 'text-orange-600 border-orange-600 dark:text-orange-500 dark:border-orange-500' : 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent'"
                                   class="inline-block p-4 border-b-2 rounded-t-lg">
                                    Sketch Design
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Upload Images Tab -->
                    <div x-show="activeTab === 'upload'">
                        <!-- Primary Image -->
                        <div class="mb-6">
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

                            @if ($newPrimaryImage && !is_string($newPrimaryImage))
                                <div class="mt-2">
                                    <img src="{{ $newPrimaryImage->temporaryUrl() }}" class="h-40 w-auto object-cover rounded-md">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Sketch Design Tab -->
                    <div x-show="activeTab === 'sketch'" class="mb-6">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sketch Your Design</label>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">Use the canvas below to sketch your design idea</p>

                            <div class="flex flex-wrap gap-2 mb-2">
                                <button type="button" @click="setColor('#000000')" class="w-6 h-6 bg-black rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                <button type="button" @click="setColor('#FF0000')" class="w-6 h-6 bg-red-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                <button type="button" @click="setColor('#0000FF')" class="w-6 h-6 bg-blue-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                <button type="button" @click="setColor('#008000')" class="w-6 h-6 bg-green-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                <button type="button" @click="setColor('#FFA500')" class="w-6 h-6 bg-orange-500 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                <button type="button" @click="setColor('#800080')" class="w-6 h-6 bg-purple-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                <button type="button" @click="setColor('#A52A2A')" class="w-6 h-6 bg-red-800 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                <button type="button" @click="setColor('#FFFF00')" class="w-6 h-6 bg-yellow-400 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                            </div>

                            <div class="flex gap-2 mb-3">
                                <div>
                                    <label for="brush-size" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Brush Size</label>
                                    <input type="range" id="brush-size" min="1" max="20" value="3" @input="setBrushSize($event.target.value)" class="w-32">
                                </div>
                                <button type="button" @click="toggleEraser" :class="{'bg-orange-100 dark:bg-orange-900': isEraser}" class="px-3 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="isEraser ? 'Drawing Mode' : 'Eraser Mode'">Eraser Mode</span>
                                </button>
                                <button type="button" @click="clearCanvas" class="px-3 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    Clear Canvas
                                </button>
                                <button type="button" @click="toggleFullScreen($event)" :class="{'bg-orange-100 dark:bg-orange-900': isFullScreen}" class="px-3 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="isFullScreen ? 'Exit Full Screen' : 'Full Screen'">Full Screen</span>
                                </button>
                            </div>

                            <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden bg-white relative">
                                <canvas
                                    id="design-canvas"
                                    width="800"
                                    height="600"
                                    :class="{'w-full h-auto cursor-crosshair': !isEraser, 'w-full h-auto cursor-eraser': isEraser}"
                                    @mousedown="startDrawing"
                                    @mousemove="draw"
                                    @mouseup="stopDrawing"
                                    @mouseleave="stopDrawing"
                                    @touchstart="handleTouchStart"
                                    @touchmove="handleTouchMove"
                                    @touchend="stopDrawing"
                                    @pointerdown="handlePointerStart"
                                    @pointermove="handlePointerMove"
                                    @pointerup="stopDrawing"
                                    @pointerleave="stopDrawing"
                                    touch-action="none"
                                ></canvas>
                                <!-- Floating controls for full screen mode -->
                                <div
                                    x-show="isFullScreen"
                                    class="absolute top-4 left-4 right-4 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 p-3 rounded-md shadow-lg border border-zinc-300 dark:border-zinc-600 z-50"
                                >
                                    <!-- Color selection -->
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <button type="button" @click="setColor('#000000')" class="w-6 h-6 bg-black rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                        <button type="button" @click="setColor('#FF0000')" class="w-6 h-6 bg-red-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                        <button type="button" @click="setColor('#0000FF')" class="w-6 h-6 bg-blue-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                        <button type="button" @click="setColor('#008000')" class="w-6 h-6 bg-green-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                        <button type="button" @click="setColor('#FFA500')" class="w-6 h-6 bg-orange-500 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                        <button type="button" @click="setColor('#800080')" class="w-6 h-6 bg-purple-600 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                        <button type="button" @click="setColor('#A52A2A')" class="w-6 h-6 bg-red-800 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                        <button type="button" @click="setColor('#FFFF00')" class="w-6 h-6 bg-yellow-400 rounded-full border border-zinc-300 dark:border-zinc-600"></button>
                                    </div>

                                    <!-- Canvas controls -->
                                    <div class="flex flex-wrap gap-2">
                                        <div>
                                            <label for="brush-size-fs" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Brush Size</label>
                                            <input type="range" id="brush-size-fs" min="1" max="20" value="3" @input="setBrushSize($event.target.value)" class="w-32">
                                        </div>
                                        <button type="button" @click="toggleEraser" :class="{'bg-orange-100 dark:bg-orange-900': isEraser}" class="px-3 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                            <span x-text="isEraser ? 'Drawing Mode' : 'Eraser Mode'">Eraser Mode</span>
                                        </button>
                                        <button type="button" @click="clearCanvas" class="px-3 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                            Clear Canvas
                                        </button>
                                        <button type="button" @click="toggleFullScreen($event)" class="px-3 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                            Exit Full Screen
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="button" @click="saveCanvasImage()" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    Save Sketch as Design Image
                                </button>
                            </div>

                            @if ($canvas_image)
                                <div class="mt-3">
                                    <p class="text-sm text-green-600 dark:text-green-400">Sketch saved successfully!</p>
                                </div>
                            @endif
                        </div>
                    </div>
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
                <button type="button" @click="saveCanvasImage($event)" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Update Design
                </button>
            </div>
        </form>
    </div>
</div>
