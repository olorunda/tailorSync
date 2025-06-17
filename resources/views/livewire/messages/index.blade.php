<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
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

    public function markAsRead($messageId)
    {
        $user = Auth::user();
        $userId = $user->id;

        // Use allMessages to get the message, ensuring parent/child relationships are respected
        $message = $user->allMessages()
            ->where('id', $messageId)
            ->first();

        if ($message && $message->recipient_id === $userId && !$message->read_at) {
            $message->read_at = now();
            $message->save();
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
        $userId = $user->id;

        // Get all messages that the user and their parent/children can access
        $allMessages = $user->allMessages();

        $messagesQuery = $allMessages
            ->when($this->search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($this->filter === 'sent', function ($query) use ($userId) {
                return $query->where('sender_id', $userId);
            })
            ->when($this->filter === 'received', function ($query) use ($userId) {
                return $query->where('recipient_id', $userId);
            })
            ->when($this->filter === 'unread', function ($query) use ($userId) {
                return $query->where('recipient_id', $userId)
                    ->whereNull('read_at');
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return [
            'messages' => $messagesQuery->paginate(10),
            'unreadCount' => $user->allMessages()
                ->where('recipient_id', $userId)
                ->whereNull('read_at')
                ->count(),
            'sentCount' => $user->allMessages()
                ->where('sender_id', $userId)
                ->count(),
            'receivedCount' => $user->allMessages()
                ->where('recipient_id', $userId)
                ->count(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Messages</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage your communications with clients</p>
        </div>
        <a href="{{ route('messages.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            New Message
        </a>
    </div>

    <!-- Message Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 rounded-full p-3 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Messages</p>
                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $sentCount + $receivedCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/30 rounded-full p-3 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Sent</p>
                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $sentCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/30 rounded-full p-3 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Unread</p>
                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $unreadCount }}</p>
                </div>
            </div>
        </div>
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
                        <input wire:model.live.debounce.300ms="search" type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-10 p-2.5" placeholder="Search messages...">
                    </div>
                </div>
                <div>
                    <select wire:model.live="filter" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                        <option value="all">All Messages</option>
                        <option value="sent">Sent</option>
                        <option value="received">Received</option>
                        <option value="unread">Unread</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                            <div class="flex items-center">
                                Date
                                @if ($sortField === 'created_at')
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">From/To</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('subject')">
                            <div class="flex items-center">
                                Subject
                                @if ($sortField === 'subject')
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
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($messages as $message)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ $message->recipient_id === Auth::id() && !$message->read_at ? 'bg-orange-50 dark:bg-orange-900/10' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $message->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if ($message->sender_id === Auth::id())
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            To: {{ $message->recipient->name ?? 'Unknown' }}
                                        </div>
                                    @else
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            From: {{ $message->sender->name ?? 'Unknown' }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $message->subject }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($message->message), 50) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($message->sender_id === Auth::id())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                        Sent
                                    </span>
                                @elseif ($message->read_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        Read
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400">
                                        Unread
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    x-data="{}"
                                    x-on:click="
                                        $wire.markAsRead('{{ $message->id }}');
                                        $dispatch('open-modal', 'view-message-{{ $message->id }}');
                                    "
                                    class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-md text-sm font-medium transition-all duration-200 shadow-sm hover:shadow transform hover:-translate-y-0.5"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">No messages found</p>
                                    <a href="{{ route('messages.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        Send Your First Message
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $messages->links() }}
        </div>
    </div>

    <!-- Message Modals (Outside Table) -->
    @foreach ($messages as $message)
        <x-modal name="view-message-{{ $message->id }}" :show="false" maxWidth="2xl">
            <div class="p-0">
                <!-- Modal Header with Close Button -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-t-lg p-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold tracking-tight">{{ $message->subject }}</h3>
                    <button type="button" x-on:click="$dispatch('close')" class="text-white hover:text-zinc-200 focus:outline-none transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Message Content Container -->
                <div class="p-6">
                    <!-- Status Indicator -->
                    <div class="mb-4 flex justify-end">
                        @if ($message->sender_id === Auth::id())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Sent
                            </span>
                        @elseif ($message->read_at)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Read on {{ \Carbon\Carbon::parse($message->read_at)->format('M d, Y H:i') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                Unread
                            </span>
                        @endif
                    </div>

                    <!-- Message Metadata with Icons -->
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 mb-6 shadow-sm transform transition-all duration-300 hover:shadow-md"
                         x-data="{show: false}"
                         x-init="setTimeout(() => show = true, 100)"
                         :class="{'opacity-0 scale-95': !show, 'opacity-100 scale-100': show}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 rounded-full p-2 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">From</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $message->sender->name ?? 'Unknown' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/30 rounded-full p-2 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">To</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $message->recipient->name ?? 'Unknown' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/30 rounded-full p-2 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Date</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $message->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900/30 rounded-full p-2 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Time</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $message->created_at->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-5 mb-6 shadow-sm transform transition-all duration-500"
                         x-data="{show: false}"
                         x-init="setTimeout(() => show = true, 300)"
                         :class="{'opacity-0 translate-y-4': !show, 'opacity-100 translate-y-0': show}">
                        <div class="prose dark:prose-invert prose-orange prose-sm sm:prose-base max-w-none">
                            {!! nl2br(e($message->message)) !!}
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 mt-6"
                         x-data="{show: false}"
                         x-init="setTimeout(() => show = true, 500)"
                         :class="{'opacity-0 translate-y-4': !show, 'opacity-100 translate-y-0': show}"
                         class="transform transition-all duration-500">
                        @if ($message->recipient_id === Auth::id())
                            <a href="{{ route('messages.create') }}?reply_to={{ $message->id }}"
                               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-md text-sm font-medium transition-all duration-200 shadow-sm hover:shadow transform hover:-translate-y-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Reply
                            </a>
                        @endif
                        <button type="button"
                                x-on:click="$dispatch('close')"
                                class="inline-flex justify-center items-center py-2.5 px-5 border border-zinc-300 dark:border-zinc-600 shadow-sm text-sm font-medium rounded-md text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </x-modal>
    @endforeach
</div>
