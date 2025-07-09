<?php

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $search = '';
    public ?string $priority = null;
    public ?string $type = null;
    public ?string $due_date_start = null;
    public ?string $due_date_end = null;

    public $pendingTasks = [];
    public $inProgressTasks = [];
    public $completedTasks = [];

    public function mount()
    {
        if (!auth()->user()->hasPermission('view_tasks')) {
            session()->flash('error', 'You do not have permission to view tasks.');
            return $this->redirect(route('dashboard'));
        }

        $this->loadTasks();
    }

    public function loadTasks()
    {
        $query = Task::query()
            ->where(function ($query) {
                return $query->where('user_id', Auth::id())->orWhere('assigned_to', Auth::id());
            })
            ->with('teamMember')
            ->when($this->search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('priority', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when($this->priority, function ($query, $priority) {
                return $query->where('priority', $priority);
            })
            ->when($this->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($this->due_date_start, function ($query, $date) {
                return $query->where('due_date', '>=', $date);
            })
            ->when($this->due_date_end, function ($query, $date) {
                return $query->where('due_date', '<=', $date);
            })
            ->latest();

        $this->pendingTasks = $query->clone()->where('status', 'pending')->get();
        $this->inProgressTasks = $query->clone()->where('status', 'in_progress')->get();
        $this->completedTasks = $query->clone()->where('status', 'completed')->get();
    }

    public function updatedSearch()
    {
        $this->loadTasks();
    }

    public function updatedPriority()
    {
        $this->loadTasks();
    }

    public function updatedType()
    {
        $this->loadTasks();
    }

    public function updatedDueDateStart()
    {
        $this->loadTasks();
    }

    public function updatedDueDateEnd()
    {
        $this->loadTasks();
    }

    public function resetFilters()
    {
        $this->reset(['priority', 'type', 'due_date_start', 'due_date_end']);
        $this->loadTasks();
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        $task = Task::find($taskId);

        if ($task && auth()->user()->hasPermission('edit_tasks')) {
            $task->status = $newStatus;

            if ($newStatus === 'completed') {
                $task->completed_date = now();
            } else {
                $task->completed_date = null;
            }

            $task->save();
            $this->loadTasks();
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
}; ?>

<div class="w-full">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Task Board</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage your tasks in a Kanban style board</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-200 rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                </svg>
                List View
            </a>
            @if(auth()->user()->hasPermission('create_tasks'))
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Task
            </a>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="relative mb-4">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-10 p-2.5" placeholder="Search tasks...">
            </div>

            <div x-data="{ open: false }" class="mb-2">
                <button @click="open = !open" type="button" class="flex items-center text-sm text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-500 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="open ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>

                <div x-show="open" x-transition class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="priority-filter" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Priority</label>
                        <x-simple-select
                            wire:model.live="priority"
                            id="priority-filter"
                            :options="[
                                ['id' => '', 'name' => 'All Priorities'],
                                ['id' => 'low', 'name' => 'Low'],
                                ['id' => 'medium', 'name' => 'Medium'],
                                ['id' => 'high', 'name' => 'High']
                            ]"
                        />
                    </div>

                    <div>
                        <label for="type-filter" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Type</label>
                        <x-simple-select
                            wire:model.live="type"
                            id="type-filter"
                            :options="[
                                ['id' => '', 'name' => 'All Types'],
                                ['id' => 'cutting', 'name' => 'Cutting'],
                                ['id' => 'sewing', 'name' => 'Sewing'],
                                ['id' => 'fitting', 'name' => 'Fitting'],
                                ['id' => 'delivery', 'name' => 'Delivery'],
                                ['id' => 'other', 'name' => 'Other']
                            ]"
                        />
                    </div>

                    <div>
                        <label for="due-date-start" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Due Date From</label>
                        <input wire:model.live="due_date_start" type="date" id="due-date-start" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2">
                    </div>

                    <div>
                        <label for="due-date-end" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Due Date To</label>
                        <input wire:model.live="due_date_end" type="date" id="due-date-end" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2">
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button wire:click="resetFilters" type="button" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-500 focus:outline-none">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        x-data="{
            draggingTask: null,
            dragOverColumn: null,
            handleDragStart(event, taskId) {
                this.draggingTask = taskId;
                event.dataTransfer.effectAllowed = 'move';
            },
            handleDragOver(event, column) {
                event.preventDefault();
                this.dragOverColumn = column;
            },
            handleDrop(event, column) {
                event.preventDefault();
                if (this.draggingTask) {
                    $wire.updateTaskStatus(this.draggingTask, column);
                }
                this.draggingTask = null;
                this.dragOverColumn = null;
            }
        }"
        class="grid grid-cols-1 md:grid-cols-3 gap-6"
    >
        <!-- Pending Column -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden"
            @dragover="handleDragOver($event, 'pending')"
            @drop="handleDrop($event, 'pending')"
            :class="{ 'border-2 border-dashed border-blue-400': dragOverColumn === 'pending' }"
        >
            <div class="p-4 bg-zinc-50 dark:bg-zinc-700 border-b border-zinc-200 dark:border-zinc-600">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 flex items-center">
                    <span class="w-3 h-3 rounded-full bg-gray-400 mr-2"></span>
                    Pending
                    <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">({{ count($pendingTasks) }})</span>
                </h3>
            </div>
            <div class="p-4 max-h-[calc(100vh-300px)] overflow-y-auto">
                @if(count($pendingTasks) > 0)
                    <div class="space-y-3">
                        @foreach($pendingTasks as $task)
                            <div
                                class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-600 cursor-move"
                                draggable="true"
                                @dragstart="handleDragStart($event, {{ $task->id }})"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</h4>
                                    @if($task->priority === 'high')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                            High
                                        </span>
                                    @elseif($task->priority === 'medium')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                            Medium
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            Low
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">{{ Str::limit($task->description, 100) }}</p>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                            {{ ucfirst($task->type) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($task->due_date)
                                            Due: {{ $task->due_date->format('M d, Y') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 flex justify-between items-center">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($task->teamMember)
                                            Assigned to: {{ $task->teamMember->name }}
                                        @else
                                            Not assigned
                                        @endif
                                    </div>
                                    <div class="flex space-x-1">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">View</a>
                                        @if(auth()->user()->hasPermission('edit_tasks'))
                                            <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-xs text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300">Edit</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">No pending tasks</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- In Progress Column -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden"
            @dragover="handleDragOver($event, 'in_progress')"
            @drop="handleDrop($event, 'in_progress')"
            :class="{ 'border-2 border-dashed border-blue-400': dragOverColumn === 'in_progress' }"
        >
            <div class="p-4 bg-zinc-50 dark:bg-zinc-700 border-b border-zinc-200 dark:border-zinc-600">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 flex items-center">
                    <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                    In Progress
                    <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">({{ count($inProgressTasks) }})</span>
                </h3>
            </div>
            <div class="p-4 max-h-[calc(100vh-300px)] overflow-y-auto">
                @if(count($inProgressTasks) > 0)
                    <div class="space-y-3">
                        @foreach($inProgressTasks as $task)
                            <div
                                class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-600 cursor-move"
                                draggable="true"
                                @dragstart="handleDragStart($event, {{ $task->id }})"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</h4>
                                    @if($task->priority === 'high')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                            High
                                        </span>
                                    @elseif($task->priority === 'medium')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                            Medium
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            Low
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">{{ Str::limit($task->description, 100) }}</p>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                            {{ ucfirst($task->type) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($task->due_date)
                                            Due: {{ $task->due_date->format('M d, Y') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 flex justify-between items-center">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($task->teamMember)
                                            Assigned to: {{ $task->teamMember->name }}
                                        @else
                                            Not assigned
                                        @endif
                                    </div>
                                    <div class="flex space-x-1">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">View</a>
                                        @if(auth()->user()->hasPermission('edit_tasks'))
                                            <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-xs text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300">Edit</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">No tasks in progress</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Completed Column -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden"
            @dragover="handleDragOver($event, 'completed')"
            @drop="handleDrop($event, 'completed')"
            :class="{ 'border-2 border-dashed border-blue-400': dragOverColumn === 'completed' }"
        >
            <div class="p-4 bg-zinc-50 dark:bg-zinc-700 border-b border-zinc-200 dark:border-zinc-600">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 flex items-center">
                    <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                    Completed
                    <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">({{ count($completedTasks) }})</span>
                </h3>
            </div>
            <div class="p-4 max-h-[calc(100vh-300px)] overflow-y-auto">
                @if(count($completedTasks) > 0)
                    <div class="space-y-3">
                        @foreach($completedTasks as $task)
                            <div
                                class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-600 cursor-move"
                                draggable="true"
                                @dragstart="handleDragStart($event, {{ $task->id }})"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</h4>
                                    @if($task->priority === 'high')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                            High
                                        </span>
                                    @elseif($task->priority === 'medium')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                            Medium
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            Low
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">{{ Str::limit($task->description, 100) }}</p>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                            {{ ucfirst($task->type) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($task->completed_date)
                                            Completed: {{ $task->completed_date->format('M d, Y') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 flex justify-between items-center">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($task->teamMember)
                                            Assigned to: {{ $task->teamMember->name }}
                                        @else
                                            Not assigned
                                        @endif
                                    </div>
                                    <div class="flex space-x-1">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">View</a>
                                        @if(auth()->user()->hasPermission('edit_tasks'))
                                            <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-xs text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300">Edit</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">No completed tasks</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
