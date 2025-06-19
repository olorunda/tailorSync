<?php

use App\Models\Client;
use Livewire\Volt\Component;

new class extends Component {
    public Client $client;

    public function mount(Client $client)
    {
        if (!auth()->user()->hasPermission('view_clients')) {
            session()->flash('error', 'You do not have permission to view clients.');
            return $this->redirect(route('clients.index'));
        }

        $this->authorize('view', $client);
        $this->client = $client;
    }

    public function with(): array
    {
        return [
            'measurements' => $this->client->measurements()->latest()->take(5)->get(),
            'orders' => $this->client->orders()->latest()->take(5)->get(),
        ];
    }

    public function delete()
    {
        if (!auth()->user()->hasPermission('delete_clients')) {
            session()->flash('error', 'You do not have permission to delete clients.');
            return;
        }

        $this->client->delete();
        return redirect()->route('clients.index');
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

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $client->name }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Client details and information</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('create_measurements'))
            <a href="{{ route('measurements.create', $client) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd" />
                </svg>
                Add Measurement
            </a>
            @endif
            @if(auth()->user()->hasPermission('edit_clients'))
            <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Client
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Client Info Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    @if ($client->photo)
                        <img src="{{ Storage::url($client->photo) }}" alt="{{ $client->name }}" class="h-20 w-20 rounded-full object-cover mr-4">
                    @else
                        <div class="h-20 w-20 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mr-4">
                            <span class="text-orange-600 dark:text-orange-500 font-bold text-xl">{{ strtoupper(substr($client->name, 0, 2)) }}</span>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $client->name }}</h2>
                        <p class="text-zinc-500 dark:text-zinc-400">Client since {{ $client->created_at->format('M Y') }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @if ($client->email)
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-500 dark:text-zinc-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Email</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $client->email }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($client->phone)
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-500 dark:text-zinc-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Phone</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $client->phone }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($client->address)
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-500 dark:text-zinc-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Address</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $client->address }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($client->notes)
                        <div class="flex items-start pt-2 border-t border-zinc-200 dark:border-zinc-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-500 dark:text-zinc-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Notes</p>
                                <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-line">{{ $client->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    @if(auth()->user()->hasPermission('delete_clients'))
                    <button wire:click="delete" wire:confirm="Are you sure you want to delete this client? This action cannot be undone." class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Delete Client
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Measurements Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Recent Measurements</h2>
                    @if(auth()->user()->hasPermission('create_measurements'))
                    <a href="{{ route('measurements.create', $client) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 text-sm font-medium">
                        Add New
                    </a>
                    @endif
                </div>

                @if ($measurements->count() > 0)
                    <div class="space-y-4">
                        @foreach ($measurements as $measurement)
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $measurement->name ?? 'Measurement ' . $measurement->id }}</h3>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $measurement->created_at->format('M d, Y') }}</p>
                                    </div>
                                    @if(auth()->user()->hasPermission('edit_measurements'))
                                    <a href="{{ route('measurements.edit', [$client, $measurement]) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <p class="text-zinc-500 dark:text-zinc-400 mb-4">No measurements recorded yet</p>
                        @if(auth()->user()->hasPermission('create_measurements'))
                        <a href="{{ route('measurements.create', $client) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add First Measurement
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Recent Orders</h2>
                    @if(auth()->user()->hasPermission('create_orders'))
                    <a href="{{ route('orders.create') }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 text-sm font-medium">
                        Create Order
                    </a>
                    @endif
                </div>

                @if ($orders->count() > 0)
                    <div class="space-y-4">
                        @foreach ($orders as $order)
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Order #{{ $order->id }}</h3>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $order->created_at->format('M d, Y') }}</p>
                                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Amount: {{Auth::user()->getCurrencySymbol()}}{{ number_format($order->total_amount, 2) }}</p>
                                        <div class="mt-1">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($order->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                                @elseif($order->status === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                                @elseif($order->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                                @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                    @if(auth()->user()->hasPermission('view_orders'))
                                    <a href="{{ route('orders.show', $order) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="text-zinc-500 dark:text-zinc-400 mb-4">No orders created yet</p>
                        @if(auth()->user()->hasPermission('create_orders'))
                        <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create First Order
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
