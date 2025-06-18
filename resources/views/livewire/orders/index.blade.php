<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
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
            'orders' => Auth::user()->allOrders()
//                ->where('user_id', Auth::id())
                ->when($this->search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->whereHas('client', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->when($this->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->with('client')
                ->paginate(10),
            'statusCounts' => [
                'all' =>Auth::user()->allOrders()->count(),
                'pending' => Auth::user()->allOrders()->where('status', 'pending')->count(),
                'in_progress' => Auth::user()->allOrders()->where('status', 'in_progress')->count(),
                'ready' => Auth::user()->allOrders()->where('status', 'ready')->count(),
                'completed' => Auth::user()->allOrders()->where('status', 'completed')->count(),
                'cancelled' => Auth::user()->allOrders()->where('status', 'cancelled')->count(),
            ],
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

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Orders</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage and track your client orders</p>
        </div>
        <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            New Order
        </a>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <!-- Filters -->
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-10 p-2.5" placeholder="Search orders...">
                    </div>
                </div>
                <div>
                    <select wire:model.live="status" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="">All Statuses ({{ $statusCounts['all'] }})</option>
                        <option value="pending">Pending ({{ $statusCounts['pending'] }})</option>
                        <option value="in_progress">In Progress ({{ $statusCounts['in_progress'] }})</option>
                        <option value="ready">Ready ({{ $statusCounts['ready'] }})</option>
                        <option value="completed">Completed ({{ $statusCounts['completed'] }})</option>
                        <option value="cancelled">Cancelled ({{ $statusCounts['cancelled'] }})</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('id')">
                            <div class="flex items-center">
                                Order #
                                @if ($sortField === 'id')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('due_date')">
                            <div class="flex items-center">
                                Due Date
                                @if ($sortField === 'due_date')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_amount')">
                            <div class="flex items-center">
                                Amount
                                @if ($sortField === 'total_amount')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Order #">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">#{{ $order->id }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $order->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Client">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-orange-600 dark:text-orange-500 font-medium text-sm">{{ strtoupper(substr($order->client->name ?? 'NA', 0, 2)) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->client->name ?? 'No Client' }}</div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate max-w-xs">{{ Str::limit($order->description, 30) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Due Date">
                                @if ($order->due_date)
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $order->due_date->format('M d, Y') }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        @if ($order->due_date->isPast())
                                            <span class="text-red-600 dark:text-red-400">Overdue</span>
                                        @else
                                            {{ $order->due_date->diffForHumans() }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Not set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Status">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($order->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                    @elseif($order->status === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                    @elseif($order->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                    @elseif($order->status === 'ready') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400
                                    @elseif($order->status === 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                    @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Amount">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $order->total_amount ? '$' . number_format($order->total_amount, 2) : 'Not set' }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    @if ($order->deposit_amount)
                                        Deposit: ${{ number_format($order->deposit_amount, 2) }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" data-label="Actions">
                                <a href="{{ route('orders.show', $order) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 mr-3">View</a>
                                <a href="{{ route('orders.edit', $order) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">No orders found</p>
                                    <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        Create Your First Order
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $orders->links() }}
        </div>
    </div>
</div>
