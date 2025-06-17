<?php

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;

new class extends Component {
    public User $teamMember;

    public function mount(User $teamMember): void
    {
        $this->teamMember = $teamMember;
    }

    public function with(): array
    {
        return [
            'assignedTasks' => Task::where('assigned_to', $this->teamMember->id)
                ->latest()
                ->take(5)
                ->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $teamMember->name }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">{{ $teamMember->position ?? 'Team Member' }}</p>
        </div>
        <a href="{{ route('team.edit', $teamMember) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
            </svg>
            Edit Profile
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Information -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col items-center mb-6">
                        <div class="h-32 w-32 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mb-4">
                            @if($teamMember->profile_photo_path)
                                <img src="{{ Storage::url($teamMember->profile_photo_path) }}" alt="{{ $teamMember->name }}" class="h-32 w-32 rounded-full object-cover">
                            @else
                                <span class="text-purple-600 dark:text-purple-500 font-bold text-4xl">{{ strtoupper(substr($teamMember->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $teamMember->name }}</h2>
                        <span class="px-3 py-1 mt-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                            {{ ucfirst(str_replace('_', ' ', $teamMember->role ?? 'team_member')) }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $teamMember->email }}</p>
                        </div>

                        @if($teamMember->phone)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $teamMember->phone }}</p>
                        </div>
                        @endif

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Joined</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $teamMember->created_at->format('M d, Y') }}</p>
                        </div>

                        @if($teamMember->notes)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Notes</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $teamMember->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks and Activity -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Assigned Tasks</h2>
                        <a href="{{ route('tasks.create') }}" class="text-sm text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">Assign New Task</a>
                    </div>

                    @if($assignedTasks->count() > 0)
                        <div class="space-y-4">
                            @foreach($assignedTasks as $task)
                                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                                    <div class="flex justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</h3>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ Str::limit($task->description, 100) }}</p>
                                        </div>
                                        <div>
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
                                    </div>
                                    <div class="flex justify-between items-center mt-3">
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            @if($task->due_date)
                                                Due: {{ $task->due_date->format('M d, Y') }}
                                            @else
                                                No due date
                                            @endif
                                        </div>
                                        <div>
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
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-center">
                            <a href="{{ route('tasks.index') }}" class="text-sm text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">View All Tasks</a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <p class="text-zinc-500 dark:text-zinc-400 mb-4">No tasks assigned to this team member</p>
                            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Assign a Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Activity Stats -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Activity Overview</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-500">{{ $assignedTasks->where('status', 'completed')->count() }}</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Completed Tasks</div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-500">{{ $assignedTasks->where('status', 'in_progress')->count() }}</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">In Progress</div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-500">{{ $assignedTasks->where('status', 'pending')->count() }}</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Pending Tasks</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
