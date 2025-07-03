<?php

use App\Models\Design;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;

new class extends Component {
    public Design $design;
    public ?string $previewImage = null;
    public bool $showFullImage = false;

    public function mount(Design $design): void
    {
        if (!auth()->user()->hasPermission('view_designs')) {
            session()->flash('error', 'You do not have permission to view designs.');
            $this->redirect(route('designs.index'));
            return;
        }

        // Check if the design belongs to the current user or their parent
        $user = auth()->user();

        // If the design belongs to the current user, proceed normally
        if ($design->user_id === $user->id) {
            $this->design = $design;
            $this->design->loadCount('orders');
            return;
        }

        // If the user is a child user and the design belongs to their parent, allow access
        if ($user->parent_id && $design->user_id === $user->parent_id) {
            $this->design = $design;
            $this->design->loadCount('orders');
            return;
        }

        // If the user is a parent user and the design belongs to one of their children, allow access
        if (!$user->parent_id) {
            $childrenIds = $user->children()->pluck('id')->toArray();
            if (in_array($design->user_id, $childrenIds)) {
                $this->design = $design;
                $this->design->loadCount('orders');
                return;
            }
        }

        // If none of the above conditions are met, redirect with access denied message
        session()->flash('error', 'You do not have permission to view this design.');
        $this->redirect(route('designs.index'));
    }

    public function toggleFullImage(?string $imagePath = null): void
    {
        if ($imagePath) {
            $this->previewImage = $imagePath;
            $this->showFullImage = true;
        } else {
            $this->showFullImage = false;
            $this->previewImage = null;
        }
    }

    public function delete()
    {
        if (!auth()->user()->hasPermission('delete_designs')) {
            session()->flash('error', 'You do not have permission to delete designs.');
            return;
        }

        try {
            // For debugging
            \Log::info('Deleting design', [
                'design_id' => $this->design->id,
                'name' => $this->design->name
            ]);

            // Delete images from storage
            if ($this->design->primary_image && Storage::disk('public')->exists($this->design->primary_image)) {
                Storage::disk('public')->delete($this->design->primary_image);
            }

            if ($this->design->images) {
                foreach ($this->design->images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            $designName = $this->design->name;
            $this->design->delete();

            \Log::info('Design deleted successfully', ['name' => $designName]);
            session()->flash('success', "Design '{$designName}' was deleted successfully!");

            return redirect()->route('designs.index');
        } catch (\Exception $e) {
            \Log::error('Error deleting design', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error deleting design: ' . $e->getMessage());
        }
    }
}; ?>

<div class="w-full">
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Global image preview overlay -->
    @if($showFullImage && $previewImage)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 overflow-y-auto" wire:click="toggleFullImage()">
            <div class="max-w-4xl my-8 p-4 relative">
                <img src="{{ Storage::url($previewImage) }}" alt="Full size image" class="max-w-full object-contain">
                <button class="absolute top-4 right-4 text-white hover:text-gray-300" wire:click="toggleFullImage()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $design->name }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                @if ($design->category)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400">
                        {{ ucfirst($design->category) }}
                    </span>
                @endif
                <span class="ml-2">Created {{ $design->created_at->format('M d, Y') }}</span>
            </p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit_designs'))
            <a href="{{ route('designs.edit', $design) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Design
            </a>
            @endif
            @if(auth()->user()->hasPermission('create_orders'))
            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Create Order
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Design Images -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Primary Image -->
            @if ($design->primary_image)
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="aspect-video w-full overflow-hidden relative group">
                        <img src="{{ Storage::url($design->primary_image) }}" alt="{{ $design->name }}" class="w-full h-full object-cover cursor-pointer" wire:click="toggleFullImage('{{ $design->primary_image }}')">
                    </div>
                </div>
            @endif

            <!-- Additional Images -->
            @if ($design->images && count($design->images) > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Additional Images</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach ($design->images as $image)
                            <div class="block relative">
                                <img src="{{ Storage::url($image) }}" alt="Design image" class="w-full aspect-square object-cover rounded-md hover:opacity-90 transition-opacity cursor-pointer" wire:click="toggleFullImage('{{ $image }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if ($design->description)
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Description</h2>
                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ $design->description }}</p>
                </div>
            @endif
        </div>

        <!-- Design Details -->
        <div class="space-y-6">
            <!-- Materials -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Materials</h2>
                @if ($design->materials && count($design->materials) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($design->materials as $material)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                {{ $material }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">No materials specified</p>
                @endif
            </div>

            <!-- Tags -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Tags</h2>
                @if ($design->tags && count($design->tags) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($design->tags as $tag)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">No tags specified</p>
                @endif
            </div>

            <!-- Orders using this design -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Orders</h2>
                @if ($design->orders_count > 0)
                    <p class="text-zinc-700 dark:text-zinc-300">This design has been used in {{ $design->orders_count }} {{ Str::plural('order', $design->orders_count) }}.</p>
                    @if(auth()->user()->hasPermission('view_orders'))
                    <a href="{{ route('orders.index') }}" class="mt-3 inline-block text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 font-medium">
                        View Orders
                    </a>
                    @endif
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">This design hasn't been used in any orders yet.</p>
                    @if(auth()->user()->hasPermission('create_orders'))
                    <a href="{{ route('orders.create') }}" class="mt-3 inline-flex items-center text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Create Order with this Design
                    </a>
                    @endif
                @endif
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Actions</h2>
                <div class="space-y-3">
                    @if(auth()->user()->hasPermission('edit_designs'))
                    <a href="{{ route('designs.edit', $design) }}" class="block w-full text-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                        Edit Design
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('create_orders'))
                    <a href="{{ route('orders.create') }}" class="block w-full text-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">
                        Create Order
                    </a>
                    @endif
                    <a href="{{ route('designs.index') }}" class="block w-full text-center px-4 py-2 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-200 rounded-md text-sm font-medium transition-colors">
                        Back to Designs
                    </a>
                    @if(auth()->user()->hasPermission('delete_designs'))
                    <button wire:click="delete" wire:confirm="Are you sure you want to delete this design? This action cannot be undone." class="block w-full text-center px-4 py-2 bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-md text-sm font-medium transition-colors">
                        Delete Design
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
