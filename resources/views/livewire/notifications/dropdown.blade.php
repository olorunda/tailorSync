<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = true;

    public function mount($showDropdown = true)
    {
        $this->showDropdown = $showDropdown;
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        $this->unreadCount = $user->unreadNotifications->count();
        $this->notifications = $user->notifications()->latest()->take(5)->get();
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }
}; ?>

<div>
    @if($showDropdown)
        <flux:dropdown position="top" align="end">
            <button class="relative p-2 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors duration-200 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-600 dark:text-zinc-300 group-hover:text-orange-500 dark:group-hover:text-orange-400 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-orange-600 rounded-full animate-pulse">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>

            <flux:menu class="shadow-xl rounded-xl overflow-hidden w-80">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Inbox</h3>
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead" class="text-sm text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Mark all as read
                        </button>
                    @endif
                </div>

                <div class="max-h-96 overflow-y-auto">
                    @forelse($notifications as $notification)
                        <div class="p-4 {{ $notification->read_at ? 'bg-white dark:bg-zinc-800' : 'bg-orange-50 dark:bg-orange-900/10' }} border-b border-zinc-200 dark:border-zinc-700 relative hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer mail-ui-message {{ !$notification->read_at ? 'unread' : '' }}">
                            @if(!$notification->read_at)
                                <div class="absolute top-4 right-4 h-2 w-2 rounded-full bg-orange-500 animate-pulse"></div>
                            @endif
                            <div class="flex items-start gap-3">
                                <!-- Sender Avatar -->
                                <div class="flex-shrink-0">
                                    @if(isset($notification->data['new_status']) && $notification->data['new_status'] === 'completed')
                                        <div class="h-9 w-9 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shadow-sm mail-ui-avatar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @elseif(str_contains($notification->type, 'OrderStatus'))
                                        <div class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shadow-sm mail-ui-avatar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @elseif(str_contains($notification->type, 'Appointment'))
                                        <div class="h-9 w-9 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center shadow-sm mail-ui-avatar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center shadow-sm mail-ui-avatar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <!-- Message Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <div class="truncate">
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                                                @if(str_contains($notification->type, 'OrderStatus'))
                                                    Order Status Update
                                                @elseif(str_contains($notification->type, 'Appointment'))
                                                    Appointment Reminder
                                                @else
                                                    {{ class_basename($notification->type) }}
                                                @endif
                                            </p>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-1.5 text-sm mail-ui-message-preview">
                                        @if(isset($notification->data['mail']))
                                            <!-- Email-like notification display -->
                                            <div class="flex flex-col gap-1">
                                                <!-- Subject as title if not already shown -->
                                                @if(isset($notification->data['mail']['subject']) && !str_contains($notification->type, 'OrderStatus') && !str_contains($notification->type, 'Appointment'))
                                                    <div class="flex items-center text-zinc-700 dark:text-zinc-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                                        </svg>
                                                        <span class="font-medium">{{ $notification->data['mail']['subject'] }}</span>
                                                    </div>
                                                @endif

                                                <!-- First line of message or custom display for specific notification types -->
                                                @if(str_contains($notification->type, 'OrderStatus'))
                                                    <div class="flex items-center text-zinc-600 dark:text-zinc-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>Order #{{ $notification->data['order_number'] ?? '' }}</span>
                                                    </div>
                                                    <div class="flex items-center mt-1 text-zinc-600 dark:text-zinc-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600 dark:text-orange-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>Status: <span class="font-medium px-1.5 py-0.5 rounded-full text-xs
                                                            @if(($notification->data['new_status'] ?? '') === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                                            @elseif(($notification->data['new_status'] ?? '') === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                                            @elseif(($notification->data['new_status'] ?? '') === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                                            @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                                            @endif">
                                                            {{ ucfirst(str_replace('_', ' ', $notification->data['new_status'] ?? '')) }}</span>
                                                        </span>
                                                    </div>
                                                @elseif(str_contains($notification->type, 'Appointment'))
                                                    <div class="flex items-center text-zinc-600 dark:text-zinc-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600 dark:text-purple-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>{{ $notification->data['title'] ?? 'Appointment' }}</span>
                                                    </div>
                                                    @if(isset($notification->data['start_time']))
                                                        <div class="flex items-center mt-1 text-zinc-600 dark:text-zinc-300">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600 dark:text-orange-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                            </svg>
                                                            <span>{{ \Carbon\Carbon::parse($notification->data['start_time'])->format('M d, Y \a\t g:i A') }}</span>
                                                        </div>
                                                    @endif
                                                @elseif(isset($notification->data['mail']['lines']) && is_array($notification->data['mail']['lines']) && count($notification->data['mail']['lines']) > 0)
                                                    <p class="text-zinc-600 dark:text-zinc-300">
                                                        {{ $notification->data['mail']['lines'][0] }}
                                                    </p>
                                                    @if(count($notification->data['mail']['lines']) > 1)
                                                        <p class="text-zinc-500 dark:text-zinc-400 text-xs">
                                                            {{ count($notification->data['mail']['lines']) - 1 }} more line(s)...
                                                        </p>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <!-- Fallback to original display -->
                                            @if(str_contains($notification->type, 'OrderStatus'))
                                                <div class="flex items-center text-zinc-600 dark:text-zinc-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Order #{{ $notification->data['order_number'] ?? '' }}</span>
                                                </div>
                                                <div class="flex items-center mt-1 text-zinc-600 dark:text-zinc-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600 dark:text-orange-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Status: <span class="font-medium px-1.5 py-0.5 rounded-full text-xs
                                                        @if(($notification->data['new_status'] ?? '') === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                                        @elseif(($notification->data['new_status'] ?? '') === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                                        @elseif(($notification->data['new_status'] ?? '') === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                                        @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                                        @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $notification->data['new_status'] ?? '')) }}</span>
                                                    </span>
                                                </div>
                                            @elseif(str_contains($notification->type, 'Appointment'))
                                                <div class="flex items-center text-zinc-600 dark:text-zinc-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600 dark:text-purple-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>{{ $notification->data['title'] ?? 'Appointment' }}</span>
                                                </div>
                                                @if(isset($notification->data['start_time']))
                                                    <div class="flex items-center mt-1 text-zinc-600 dark:text-zinc-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600 dark:text-orange-400 mr-1.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>{{ \Carbon\Carbon::parse($notification->data['start_time'])->format('M d, Y \a\t g:i A') }}</span>
                                                    </div>
                                                @endif
                                            @else
                                                <p class="text-zinc-600 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-700/50 p-2 rounded text-xs overflow-hidden text-ellipsis">
                                                    {{ json_encode($notification->data) }}
                                                </p>
                                            @endif
                                        @endif
                                    </div>

                                    @if(!$notification->read_at)
                                        <button wire:click="markAsRead('{{ $notification->id }}')" class="text-xs text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 mt-2 inline-flex items-center gap-1 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Mark as read
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 px-4 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                No notifications yet
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 text-center">
                    <a href="{{ route('notifications.index') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        View All Notifications
                    </a>
                </div>
            </flux:menu>
        </flux:dropdown>
    @else
        <!-- Just show the notification badge for mobile navigation -->
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-orange-600 rounded-full animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    @endif
</div>
