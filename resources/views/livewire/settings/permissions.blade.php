<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $permissions = [];
    public $categories = [];
    public $activeCategory = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        // Check if user has permission to manage permissions
        if (!Auth::user()->hasRole('admin')) {
            $this->redirect(route('dashboard'));
            return;
        }

        $this->loadPermissions();
        $this->loadCategories();

        // Set the first category as active by default
        if (count($this->categories) > 0 && !$this->activeCategory) {
            $this->activeCategory = $this->categories[0];
        }
    }

    /**
     * Load all permissions
     */
    public function loadPermissions(): void
    {
        $this->permissions = Permission::all();
    }

    /**
     * Load unique permission categories
     */
    public function loadCategories(): void
    {
        $this->categories = Permission::select('category')->distinct()->pluck('category')->toArray();
    }

    /**
     * Set the active category
     */
    public function setActiveCategory($category): void
    {
        $this->activeCategory = $category;
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Permissions Management')" :subheading="__('View system permissions by category')">
        <div class="my-6 w-full space-y-8">
            <!-- Permissions Management Section -->
            <div>
                <flux:heading size="lg">{{ __('Permissions') }}</flux:heading>
                <flux:subheading>{{ __('System permissions are predefined and cannot be modified') }}</flux:subheading>
                <flux:separator variant="subtle" class="mb-4" />

                <!-- Category Tabs -->
                <div class="mb-6">
                    <div class="border-b border-zinc-200 dark:border-zinc-700">
                        <nav class="-mb-px flex flex-wrap">
                            @foreach($categories as $category)
                                <button
                                    wire:click="setActiveCategory('{{ $category }}')"
                                    class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm {{ $activeCategory === $category ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600' }}"
                                >
                                    {{ ucfirst($category) }}
                                </button>
                            @endforeach
                        </nav>
                    </div>
                </div>

                <!-- Permissions List -->
                <div>
                    @php
                        $permissionsByCategory = $this->permissions->groupBy('category');
                    @endphp

                    @if(isset($permissionsByCategory[$activeCategory]))
                        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-lg shadow-sm p-4 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center mb-3">
                                <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 dark:text-orange-300" viewBox="0 0 20 20" fill="currentColor">
                                        @if($activeCategory == 'settings')
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                        @elseif($activeCategory == 'clients')
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                        @elseif($activeCategory == 'finance')
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                        @elseif($activeCategory == 'team')
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                        @elseif($activeCategory == 'designs')
                                            <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z" clip-rule="evenodd" />
                                        @elseif($activeCategory == 'orders')
                                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                        @elseif($activeCategory == 'inventory')
                                            <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                                            <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                                        @elseif($activeCategory == 'appointments')
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        @elseif($activeCategory == 'messages')
                                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                                            <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
                                        @elseif($activeCategory == 'measurements')
                                            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                </div>
                                <flux:heading size="sm" class="capitalize">{{ $activeCategory }}</flux:heading>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                                @foreach($permissionsByCategory[$activeCategory] as $permission)
                                    <div class="flex items-center p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors">
                                        <div class="flex items-center">
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $permission->name }}
                                                </div>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $permission->description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No permissions found for this category.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
