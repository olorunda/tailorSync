<?php

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public ?string $priority = null;
    public ?string $status = null;
    public ?string $type = null;
    public ?string $due_date_start = null;
    public ?string $due_date_end = null;

    public function mount()
    {
        if (!auth()->user()->hasPermission('view_tasks')) {
            session()->flash('error', 'You do not have permission to view tasks.');
            return $this->redirect(route('dashboard'));
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPriority()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function updatingDueDateStart()
    {
        $this->resetPage();
    }

    public function updatingDueDateEnd()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['priority', 'status', 'type', 'due_date_start', 'due_date_end']);
        $this->resetPage();
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
        return [
            'tasks' => Task::query()

                ->where(function ($query){
                    return $query->where('user_id', Auth::id())->orwhere('assigned_to',Auth::id());
                })
                ->with('teamMember') // Load the team member relationship
                ->when($this->search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('priority', 'like', "%{$search}%")
                            ->orWhere('type', 'like', "%{$search}%");
                    });
                })
                ->when($this->priority, function ($query, $priority) {
                    return $query->where('priority', $priority);
                })
                ->when($this->status, function ($query, $status) {
                    return $query->where('status', $status);
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
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="w-full">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Tasks</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage your tasks and to-do items</p>
        </div>
        @if(auth()->user()->hasPermission('create_tasks'))
        <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add Task
        </a>
        @endif
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
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
                        <label for="status-filter" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                        <x-simple-select
                            wire:model.live="status"
                            id="status-filter"
                            :options="[
                                ['id' => '', 'name' => 'All Statuses'],
                                ['id' => 'pending', 'name' => 'Pending'],
                                ['id' => 'in_progress', 'name' => 'In Progress'],
                                ['id' => 'completed', 'name' => 'Completed']
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

        <div class="overflow-x-auto">
            <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Due Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Priority</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Assigned To</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($tasks as $task)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Title">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($task->description, 30) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Due Date">
                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                    @else
                                        No due date
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Priority">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Status">
                                @if($task->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        Completed
                                    </span>
                                @elseif($task->status === 'in_progress')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                        In Progress
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Type">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                    {{ ucfirst($task->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Assigned To">
                                @if($task->teamMember)
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $task->teamMember->name }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ ucfirst($task->teamMember->role) }}
                                    </div>
                                @else
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Not assigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" data-label="Actions">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('tasks.show', $task) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-800/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        View
                                    </a>
                                    @if(auth()->user()->hasPermission('edit_tasks'))
                                    <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-orange-700 bg-orange-100 hover:bg-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-800/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Edit
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">No tasks found</p>
                                    @if(auth()->user()->hasPermission('create_tasks'))
                                    <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        Create Your First Task
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
