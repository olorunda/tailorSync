<?php

use App\Models\Task;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Task $task;
    public string $title = '';
    public string $description = '';
    public ?string $due_date = '';
    public string $priority = '';
    public string $status = '';
    public string $type = '';
    public ?int $assigned_to = null;
    public ?int $order_id = null;
    public ?string $notes = '';

    public function mount(Task $task)
    {
        if (!auth()->user()->hasPermission('edit_tasks')) {
            session()->flash('error', 'You do not have permission to edit tasks.');
            return $this->redirect(route('tasks.index'));
        }

        $this->task = $task;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->due_date = $task->due_date ? $task->due_date->format('Y-m-d') : null;
        $this->priority = $task->priority;
        $this->status = $task->status;
        $this->type = $task->type;
        $this->assigned_to = $task->assigned_to;
        $this->order_id = $task->order_id;
        $this->notes = $task->notes ?? '';
    }

    public function save()
    {
        if (!auth()->user()->hasPermission('edit_tasks')) {
            session()->flash('error', 'You do not have permission to edit tasks.');
            return $this->redirect(route('tasks.index'));
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'status' => ['required', 'string', 'in:pending,in_progress,completed'],
            'type' => ['required', 'string', 'in:cutting,sewing,fitting,delivery,other'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->task->title = $this->title;
        $this->task->description = $this->description;
        $this->task->due_date = $this->due_date;
        $this->task->priority = $this->priority;
        $this->task->status = $this->status;
        $this->task->type = $this->type;
        $this->task->assigned_to = $this->assigned_to;
        $this->task->order_id = $this->order_id;
        $this->task->notes = $this->notes;
        $this->task->save();

        $this->redirect(route('tasks.index'));
    }

    public function with(): array
    {
        return [
            'team_members' => User::where('id', '!=', Auth::id())->orderBy('name')->get(),
            'orders' => Auth::user()->allOrders()->orderBy('created_at', 'desc')->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Task</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update task details</p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Title</label>
                    <input wire:model="title" type="text" id="title" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Due Date</label>
                    <input wire:model="due_date" type="date" id="due_date" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Priority</label>
                    <x-simple-select
                        wire:model="priority"
                        id="priority"
                        :options="[
                            ['id' => 'low', 'name' => 'Low'],
                            ['id' => 'medium', 'name' => 'Medium'],
                            ['id' => 'high', 'name' => 'High']
                        ]"
                        :required="true"
                    />
                    @error('priority') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                    <x-simple-select
                        wire:model="status"
                        id="status"
                        :options="[
                            ['id' => 'pending', 'name' => 'Pending'],
                            ['id' => 'in_progress', 'name' => 'In Progress'],
                            ['id' => 'completed', 'name' => 'Completed']
                        ]"
                        :required="true"
                    />
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Type</label>
                    <x-simple-select
                        wire:model="type"
                        id="type"
                        :options="[
                            ['id' => 'cutting', 'name' => 'Cutting'],
                            ['id' => 'sewing', 'name' => 'Sewing'],
                            ['id' => 'fitting', 'name' => 'Fitting'],
                            ['id' => 'delivery', 'name' => 'Delivery'],
                            ['id' => 'other', 'name' => 'Other']
                        ]"
                        :required="true"
                    />
                    @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Assign To (Optional)</label>
                    <x-simple-select
                        wire:model="assigned_to"
                        id="assigned_to"
                        :options="$team_members->map(fn($member) => ['id' => $member->id, 'name' => $member->name])->toArray()"
                        placeholder="Assign to someone"
                    />
                    @error('assigned_to') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="order_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Related Order (Optional)</label>
                    <x-simple-select
                        wire:model="order_id"
                        id="order_id"
                        :options="$orders->map(fn($order) => ['id' => $order->id, 'name' => 'Order #' . $order->order_number])->toArray()"
                        placeholder="Select an order"
                    />
                    @error('order_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                    <textarea wire:model="description" id="description" rows="4" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Additional Notes</label>
                    <textarea wire:model="notes" id="notes" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('tasks.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
