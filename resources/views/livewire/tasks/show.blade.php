<?php

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Task $task;

    public function mount(Task $task)
    {
        if (!auth()->user()->hasPermission('view_tasks')) {
            session()->flash('error', 'You do not have permission to view tasks.');
            return $this->redirect(route('dashboard'));
        }


        if (!in_array($task->user_id,[Auth::id(),Auth::user()->parent_id]) && $task->assigned_to !== Auth::id()) {
            return $this->redirect(route('tasks.index'));
        }

        $this->task = $task;
    }

    public function delete()
    {
        if (!auth()->user()->hasPermission('delete_tasks')) {
            session()->flash('error', 'You do not have permission to delete tasks.');
            return;
        }

        $this->task->delete();

        session()->flash('status', 'Task deleted successfully!');
        $this->redirect(route('tasks.index'));
    }

    public function markAsCompleted()
    {
        if (!auth()->user()->hasPermission('edit_tasks')) {
            session()->flash('error', 'You do not have permission to update tasks.');
            return;
        }

        $this->task->complete();

        session()->flash('status', 'Task marked as completed!');
        $this->redirect(route('tasks.show', $this->task));
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

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Task Details</h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                Created on {{ $task->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
            @if(auth()->user()->hasPermission('edit_tasks'))
            <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit
            </a>
            @endif
        </div>
    </div>

    <!-- Task Actions -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden p-6 mb-6">
        <div class="flex flex-wrap gap-3">
            @if($task->status !== 'completed' && auth()->user()->hasPermission('edit_tasks'))
            <button
                wire:click="markAsCompleted"
                wire:confirm="Are you sure you want to mark this task as completed?"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Mark as Completed
            </button>
            @endif

            @if(auth()->user()->hasPermission('delete_tasks'))
            <button
                wire:click="delete"
                wire:confirm="Are you sure you want to delete this task? This action cannot be undone."
                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete
            </button>
            @endif

            <a
                href="#"
                onclick="window.print(); return false;"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                </svg>
                Print
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Task Details -->
        <div class="md:col-span-2 space-y-6">
            <!-- Task Information -->
            <div id="printable-task" class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between mb-8">
                        <div class="mb-4 md:mb-0">
                            <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mb-1">{{ $task->title }}</h2>
                            <div class="mt-4 space-y-1">
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    <span class="font-medium">Due Date:</span> {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}
                                </p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    <span class="font-medium">Priority:</span> {{ ucfirst($task->priority) }}
                                </p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    <span class="font-medium">Status:</span> {{ ucfirst($task->status) }}
                                </p>
                                @if($task->type)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    <span class="font-medium">Type:</span> {{ ucfirst($task->type) }}
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">Your Company Name</p>
                                <p>123 Business Street</p>
                                <p>City, State ZIP</p>
                                <p>Phone: (123) 456-7890</p>
                                <p>Email: contact@yourcompany.com</p>
                            </div>
                        </div>
                    </div>

                    <!-- Task Status -->
                    <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Status:</span>
                            <span class="px-3 py-1 rounded-full
                                @if($task->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                @elseif($task->status === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                @elseif($task->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                @else bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400
                                @endif text-sm font-medium">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>

                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Priority:</span>
                            <span class="px-3 py-1 rounded-full
                                @if($task->priority === 'high') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                @elseif($task->priority === 'medium') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                @else bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                @endif text-sm">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>

                        @if($task->isOverdue())
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Status:</span>
                            <span class="px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 text-sm">
                                Overdue
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Task Description -->
                    <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Description</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-line">{{ $task->description }}</p>
                    </div>

                    @if($task->notes)
                    <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Notes</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-line">{{ $task->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Task Summary -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Task Summary</h2>
                </div>
                <div class="p-6">
                    <dl class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Title</dt>
                            <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Due Date</dt>
                            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Priority</dt>
                            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($task->priority) }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
                            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($task->status) }}</dd>
                        </div>
                        @if($task->type)
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Type</dt>
                            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($task->type) }}</dd>
                        </div>
                        @endif
                        @if($task->completed_date)
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Completed Date</dt>
                            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ $task->completed_date->format('M d, Y') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Assignment Information -->
            @if($task->assigned_to)
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Assigned To</h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ \App\Models\User::find($task->assigned_to)->name ?? 'Unknown User' }}</p>
                </div>
            </div>
            @endif

            <!-- Related Order -->
            @if($task->order_id)
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Related Order</h2>
                </div>
                <div class="p-6">
                    <a href="{{ route('orders.show', $task->order_id) }}" class="inline-flex items-center text-sm font-medium text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                        </svg>
                        View Order
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
