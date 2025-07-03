<?php

use App\Models\Design;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public ?string $previewImage = null;
    public bool $showFullImage = false;

    public function mount()
    {
        if (!auth()->user()->hasPermission('view_designs')) {
            session()->flash('error', 'You do not have permission to view designs.');
            $this->redirect(route('dashboard'));
            return;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
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

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center h-96">
            <div class="flex flex-col items-center gap-2">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-orange-600"></div>
                <span class="text-orange-600 text-lg">Loading...</span>
            </div>
        </div>
        HTML;
    }

    public function with(): array
    {
        $user = Auth::user();

        // Use the allDesigns method to get designs from both the user and their parent/children
        $designsQuery = $user->allDesigns();

        return [
            'designs' => $designsQuery
                ->when($this->search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->when($this->category, function ($query, $category) {
                    return $query->where('category', $category);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(12),
            'categories' => $user->allDesigns()
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category')
                ->toArray(),
        ];
    }
}; ?>

<div class="w-full">
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Global image preview overlay -->
{{--    @if($showFullImage && $previewImage)--}}
{{--        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 overflow-y-auto" wire:click="toggleFullImage()">--}}
{{--            <div class="max-w-4xl my-8 p-4 relative">--}}
{{--                <img src="{{ Storage::url($previewImage) }}" alt="Full size image" class="max-w-full object-contain">--}}
{{--                <button class="absolute top-4 right-4 text-white hover:text-gray-300" wire:click="toggleFullImage()">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />--}}
{{--                    </svg>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endif--}}

    @php
        // Get subscription plan details
        $user = auth()->user();
        $businessDetail = $user->businessDetail;
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = \App\Services\SubscriptionService::getPlan($planKey);
        $maxDesigns = $plan['features']['max_designs'] ?? 5;
        $currentDesignCount = \App\Models\Design::where('user_id', $user->id)->count();
        $isUnlimited = $maxDesigns === 'unlimited';
        $isNearLimit = !$isUnlimited && $currentDesignCount >= ($maxDesigns * 0.8); // 80% of limit
        $isAtLimit = !$isUnlimited && $currentDesignCount >= $maxDesigns;
    @endphp

    @if($isAtLimit)
        <x-subscription-limit-notice
            feature="design limit"
            message="You have reached the maximum number of designs ({{ $maxDesigns }}) allowed for your {{ ucfirst($planKey) }} plan."
        />
    @elseif($isNearLimit)
        <x-subscription-limit-notice
            feature="design limit"
            message="You are approaching the maximum number of designs allowed for your {{ ucfirst($planKey) }} plan. You have used {{ $currentDesignCount }} out of {{ $maxDesigns }} available design slots."
        />
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Design Board</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage your design sketches and inspiration</p>
        </div>
        @if(auth()->user()->hasPermission('create_designs'))
        <a href="{{ route('designs.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add Design
        </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="p-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-10 p-2.5" placeholder="Search designs...">
                    </div>
                </div>
                <div>
                    <select wire:model.live="category" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Design Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($designs as $design)
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <a href="{{ route('designs.show', $design) }}" class="block">
                    <div class="aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-700 relative">
                        @if ($design->primary_image)
                            <img src="{{ Storage::url($design->primary_image) }}" alt="{{ $design->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer"  >
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-orange-50 dark:bg-orange-900/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-orange-300 dark:text-orange-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-1">{{ $design->name }}</h3>
                            @if ($design->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400">
                                    {{ ucfirst($design->category) }}
                                </span>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            @if(auth()->user()->hasPermission('edit_designs'))
                            <a href="{{ route('designs.edit', $design) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>
                    @if ($design->description)
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">{{ $design->description }}</p>
                    @endif
                    <div class="mt-3 flex justify-between items-center">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $design->created_at->format('M d, Y') }}
                        </div>
                        @if ($design->orders_count > 0)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $design->orders_count }} {{ Str::plural('order', $design->orders_count) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-8 text-center">
                <div class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">No designs found</p>
                    @if(auth()->user()->hasPermission('create_designs'))
                    <a href="{{ route('designs.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Your First Design
                    </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $designs->links() }}
    </div>
</div>
