<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Client;
use App\Notifications\EmailMessageNotification;
use Illuminate\Support\Facades\Notification;

new class extends Component {
    use WithPagination;

    public $filter = 'all';
    public $expandedNotifications = [];

    // Email form properties
    public string $subject = '';
    public string $message = '';
    public string $recipient_type = 'user';
    public string $recipient_id = '';
    public bool $send_email_notification = true;
    public bool $showEmailModal = false;

    public function mount()
    {
        // Set page title
        $this->title = 'Notifications';
    }

    public function openEmailModal()
    {
        $this->reset(['subject', 'message', 'recipient_type', 'recipient_id']);
        $this->send_email_notification = true;
        $this->showEmailModal = true;
    }

    public function closeEmailModal()
    {
        $this->showEmailModal = false;
    }

    public function sendEmail()
    {
        $validationRules = [
            'recipient_type' => 'required|in:user,client',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'send_email_notification' => 'boolean',
        ];

        // Validate recipient_id based on recipient_type
        if ($this->recipient_type === 'user') {
            $validationRules['recipient_id'] = 'required|exists:users,id';
        } else {
            $validationRules['recipient_id'] = 'required|exists:clients,id';
        }

        $validated = $this->validate($validationRules);

        // Send email notification
        if ($this->recipient_type === 'user') {
            $recipient = User::find($validated['recipient_id']);
            if ($recipient) {
                Notification::send($recipient, new EmailMessageNotification(
                    $validated['subject'],
                    $validated['message'],
                    Auth::user()->name
                ));
            }
        } else {
            $client = Client::find($validated['recipient_id']);
            if ($client && $client->email) {
                Notification::route('mail', $client->email)
                    ->notify(new EmailMessageNotification(
                        $validated['subject'],
                        $validated['message'],
                        Auth::user()->name
                    ));
            }
        }

        $this->closeEmailModal();
        session()->flash('status', 'Message sent successfully!');
    }

    public function toggleExpand($id)
    {
        if (in_array($id, $this->expandedNotifications)) {
            $this->expandedNotifications = array_diff($this->expandedNotifications, [$id]);
        } else {
            $this->expandedNotifications[] = $id;
        }
    }

    public function getNotificationsProperty()
    {
        $query = Auth::user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->latest()->paginate(10);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification($id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();
    }

    public function deleteAllNotifications()
    {
        if ($this->filter === 'read') {
            Auth::user()->readNotifications()->delete();
        } elseif ($this->filter === 'unread') {
            Auth::user()->unreadNotifications()->delete();
        } else {
            Auth::user()->notifications()->delete();
        }
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $user = Auth::user();

        return [
            'users' => $user->allTeamMembers()->get(),
            'clients' => $user->allClients()->get(),
        ];
    }
}; ?>

<div>
<div class="w-full">
    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('status') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Inbox</h1>
            <p class="text-zinc-600 dark:text-zinc-400">View and manage your notifications</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <button x-data="{}" x-on:click="$dispatch('open-modal', 'email-modal')" class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow w-full sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                </svg>
                Send Email
            </button>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                @if($this->notifications->total() > 0)
                    <button wire:click="markAllAsRead" class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Mark All as Read
                    </button>

                    <button wire:click="deleteAllNotifications" wire:confirm="Are you sure you want to delete all {{ $this->filter }} notifications?" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Delete All
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar / Folders -->
        <div class="w-full md:w-64 flex-shrink-0 mail-ui-sidebar">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Folders</h3>
                </div>
                <div class="p-2">
                    <button wire:click="$set('filter', 'all')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left {{ $filter === 'all' ? 'bg-orange-100 dark:bg-orange-900/20 text-orange-800 dark:text-orange-300' : 'hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                            <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
                        </svg>
                        <span>All Messages</span>
                    </button>

                    <button wire:click="$set('filter', 'unread')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left {{ $filter === 'unread' ? 'bg-orange-100 dark:bg-orange-900/20 text-orange-800 dark:text-orange-300' : 'hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                        </svg>
                        <span>Unread</span>
                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="ml-auto bg-orange-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <button wire:click="$set('filter', 'read')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left {{ $filter === 'read' ? 'bg-orange-100 dark:bg-orange-900/20 text-orange-800 dark:text-orange-300' : 'hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Read</span>
                    </button>
                </div>
            </div>

{{--            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden border border-zinc-200 dark:border-zinc-700">--}}
{{--                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">--}}
{{--                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Categories</h3>--}}
{{--                </div>--}}
{{--                <div class="p-2">--}}
{{--                    <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300">--}}
{{--                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">--}}
{{--                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />--}}
{{--                        </svg>--}}
{{--                        <span>Orders</span>--}}
{{--                    </div>--}}

{{--                    <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300">--}}
{{--                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400" viewBox="0 0 20 20" fill="currentColor">--}}
{{--                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />--}}
{{--                        </svg>--}}
{{--                        <span>Appointments</span>--}}
{{--                    </div>--}}

{{--                    <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300">--}}
{{--                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">--}}
{{--                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />--}}
{{--                        </svg>--}}
{{--                        <span>Completed</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden border border-zinc-200 dark:border-zinc-700">
                <!-- Search bar -->
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-10 p-2.5" placeholder="Search messages...">
                    </div>
                </div>

                @if($this->notifications->total() > 0)
                    <div>
                        @foreach($this->notifications as $notification)
                            <div wire:click="toggleExpand('{{ $notification->id }}')" class="p-3 sm:p-4 mb-3 {{ $notification->read_at ? 'bg-white dark:bg-zinc-800' : 'bg-orange-50 dark:bg-orange-900/10' }} hover:bg-zinc-50 dark:hover:bg-zinc-700/50 relative cursor-pointer mail-ui-message {{ !$notification->read_at ? 'unread' : '' }} {{ in_array($notification->id, $expandedNotifications) ? 'expanded' : 'collapsed' }} shadow rounded-lg">
                                @if(!$notification->read_at)
                                    <div class="absolute top-3 sm:top-4 right-3 sm:right-4 h-3 w-3 rounded-full bg-orange-500 animate-pulse"></div>
                                @endif

                                <div class="flex items-start gap-4">
                                    <!-- Sender Avatar -->
                                    <div class="flex-shrink-0">
                                        @if(isset($notification->data['new_status']) && $notification->data['new_status'] === 'completed')
                                            <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shadow-sm mail-ui-avatar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @elseif(str_contains($notification->type, 'OrderStatus'))
                                            <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shadow-sm mail-ui-avatar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @elseif(str_contains($notification->type, 'Appointment'))
                                            <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center shadow-sm mail-ui-avatar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center shadow-sm mail-ui-avatar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Message Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                            <div class="truncate mb-2 sm:mb-0">
                                                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                                                    @if(str_contains($notification->type, 'OrderStatus'))
                                                        Order Status Update
                                                    @elseif(str_contains($notification->type, 'Appointment'))
                                                        Appointment Reminder
                                                    @else
                                                        {{ class_basename($notification->type) }}
                                                    @endif
                                                </h3>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                                                    <span class="truncate">{{ $notification->created_at->format('M d, Y') }}</span>
                                                    <span class="mx-1">•</span>
                                                    <span>{{ $notification->created_at->format('h:i A') }}</span>
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2 sm:ml-2">
                                                <button wire:click.stop="toggleExpand('{{ $notification->id }}')" class="text-sm text-zinc-600 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-300 inline-flex items-center gap-1 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform {{ in_array($notification->id, $expandedNotifications) ? 'rotate-180' : '' }}" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                @if(!$notification->read_at)
                                                    <button wire:click.stop="markAsRead('{{ $notification->id }}')" class="text-sm text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 inline-flex items-center gap-1 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                @endif
                                                <button wire:click.stop="deleteNotification('{{ $notification->id }}')" wire:confirm="Are you sure you want to delete this notification?" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 inline-flex items-center gap-1 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Preview of message content -->
                                        <div class="mt-2 text-zinc-600 dark:text-zinc-300 {{ in_array($notification->id, $expandedNotifications) ? '' : 'md:line-clamp-2' }} mail-ui-message-preview mail-ui-message-content break-words overflow-x-auto">
                                    @if(isset($notification->data['mail']))
                                        <!-- Email-like notification display -->
                                        <div class="flex flex-col gap-3 overflow-x-auto">
                                            <!-- Subject -->
                                            @if(isset($notification->data['mail']['subject']))
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                                    </svg>
                                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $notification->data['mail']['subject'] }}</span>
                                                </div>
                                            @endif

                                            <!-- Greeting -->
                                            @if(isset($notification->data['mail']['greeting']))
                                                <p class="text-zinc-700 dark:text-zinc-300 font-medium">
                                                    {{ $notification->data['mail']['greeting'] }}
                                                </p>
                                            @endif

                                            <!-- Message Lines -->
                                            @if(isset($notification->data['mail']['lines']) && is_array($notification->data['mail']['lines']))
                                                <div class="space-y-2 mt-1">
                                                    @foreach($notification->data['mail']['lines'] as $line)
                                                        <p class="text-zinc-600 dark:text-zinc-400">{{ $line }}</p>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <!-- Action Button -->
                                            @if(isset($notification->data['mail']['action']))
                                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-600">
                                                    <a href="{{ $notification->data['mail']['action']['url'] }}"
                                                       class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors shadow-sm"
                                                       wire:navigate>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ $notification->data['mail']['action']['text'] }}
                                                    </a>
                                                </div>
                                            @elseif(str_contains($notification->type, 'OrderStatus') && isset($notification->data['order_id']))
                                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-600">
                                                    <a href="{{ route('orders.show', $notification->data['order_id']) }}" class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors shadow-sm" wire:navigate>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        View Order Details
                                                    </a>
                                                </div>
                                            @elseif(str_contains($notification->type, 'Appointment') && isset($notification->data['appointment_id']))
                                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-600">
                                                    <a href="{{ route('appointments.show', $notification->data['appointment_id']) }}" class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors shadow-sm" wire:navigate>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        View Appointment Details
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Fallback to the original display if mail data is not available -->
                                        @if(str_contains($notification->type, 'OrderStatus'))
                                            <div class="flex flex-col gap-2 overflow-x-auto">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="font-medium">Order #{{ $notification->data['order_number'] ?? '' }}</span>
                                                </div>

                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 dark:text-orange-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Status changed
                                                    @if(isset($notification->data['old_status']))
                                                        from <span class="font-medium px-2 py-1 rounded-full text-xs
                                                        @if($notification->data['old_status'] === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                                        @elseif($notification->data['old_status'] === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                                        @elseif($notification->data['old_status'] === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                                        @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                                        @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $notification->data['old_status'])) }}</span>
                                                    @endif
                                                    to <span class="font-medium px-2 py-1 rounded-full text-xs
                                                    @if(($notification->data['new_status'] ?? '') === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                                    @elseif(($notification->data['new_status'] ?? '') === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                                    @elseif(($notification->data['new_status'] ?? '') === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                                    @else bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-400
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $notification->data['new_status'] ?? '')) }}</span>
                                                    </span>
                                                </div>
                                            </div>

                                            @if(isset($notification->data['order_id']))
                                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-600">
                                                    <a href="{{ route('orders.show', $notification->data['order_id']) }}" class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors shadow-sm" wire:navigate>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        View Order Details
                                                    </a>
                                                </div>

                                            @endif
                                        @elseif(str_contains($notification->type, 'Appointment'))
                                            <div class="flex flex-col gap-2 overflow-x-auto">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="font-medium">{{ $notification->data['title'] ?? 'Appointment' }}</span>
                                                </div>

                                                @if(isset($notification->data['start_time']))
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 dark:text-orange-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>{{ \Carbon\Carbon::parse($notification->data['start_time'])->format('l, F j, Y') }}</span>
                                                    </div>

                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>{{ \Carbon\Carbon::parse($notification->data['start_time'])->format('g:i A') }}
                                                        @if(isset($notification->data['end_time']))
                                                            - {{ \Carbon\Carbon::parse($notification->data['end_time'])->format('g:i A') }}
                                                        @endif
                                                        </span>
                                                    </div>
                                                @endif

                                                @if(isset($notification->data['location']) && $notification->data['location'])
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>{{ $notification->data['location'] }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(isset($notification->data['appointment_id']))
                                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-600">
                                                    <a href="{{ route('appointments.show', $notification->data['appointment_id']) }}" class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors shadow-sm" wire:navigate>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        View Appointment Details
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex flex-col gap-2 overflow-x-auto">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-600 dark:text-zinc-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="font-medium">Notification Details</span>
                                                </div>

                                                <div class="mt-2 bg-zinc-100 dark:bg-zinc-700 p-3 rounded-md overflow-auto overflow-x-auto">
                                                    @foreach($notification->data as $key => $value)
                                                        <div class="mb-2">
                                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                            <span class="text-zinc-600 dark:text-zinc-400">
                                                                @if(is_array($value))
                                                                    <pre class="text-xs mt-1">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                @elseif(is_bool($value))
                                                                    {{ $value ? 'Yes' : 'No' }}
                                                                @elseif(is_null($value))
                                                                    <em>Not specified</em>
                                                                @elseif(preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value))
                                                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y \a\t h:i A') }}
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $this->notifications->links() }}
            </div>
        @else
            <div class="py-16 px-6 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-900/30 mb-6 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">No notifications found</h3>
                <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                    @if($this->filter === 'unread')
                        You don't have any unread notifications at the moment. Check back later or view your read notifications.
                    @elseif($this->filter === 'read')
                        You don't have any read notifications. When you mark notifications as read, they'll appear here.
                    @else
                        You don't have any notifications yet. When you receive notifications, they'll appear here.
                    @endif
                </p>

                @if($this->filter !== 'all')
                    <button wire:click="$set('filter', 'all')" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
                        </svg>
                        View All Notifications
                    </button>
                @endif
            </div>
        @endif
            </div>
        </div>
    </div>
</div>

<!-- Email Modal -->
    <x-modal name="email-modal" maxWidth="full">
        <div class="p-0 max-h-[80vh] overflow-y-auto my-12 w-full max-w-[400vw] mx-auto">
            <!-- Modal Header with Close Button -->
            <div
                class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-t-lg p-4 flex justify-between items-center">
                <h3 class="text-xl font-bold tracking-tight">Send Email Notification</h3>
                <button type="button" x-on:click="$dispatch('close')"
                        class="text-white hover:text-zinc-200 focus:outline-none transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Email Form -->
            <div class="p-6">
                <form id="emailForm" wire:submit.prevent="sendEmail; $dispatch('close')" class="space-y-6">
                    <!-- Recipient Information -->
                    <div>
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Recipient
                            Information</h2>

                        <div class="space-y-12">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 md:col-span-6">
                                    <label for="recipient_type"
                                           class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Recipient
                                        Type <span class="text-red-500">*</span></label>
                                    <select
                                        wire:model.live="recipient_type"
                                        id="recipient_type"
                                        class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                        required
                                    >
                                        <option value="user">Team Member</option>
                                        <option value="client">Client</option>
                                    </select>
                                    @error('recipient_type') <span
                                        class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-span-12 md:col-span-6">
                                    <label for="recipient_id"
                                           class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Recipient
                                        <span class="text-red-500">*</span></label>
                                    <select
                                        wire:model="recipient_id"
                                        id="recipient_id"
                                        class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                        required
                                    >
                                        <option value="">Select Recipient</option>
                                        @if ($recipient_type === 'user')
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('recipient_id') <span
                                        class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div>
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Message Content</h2>

                        <div class="space-y-6">
                            <div class="col-span-12">
                                <label for="subject"
                                       class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Subject
                                    <span class="text-red-500">*</span></label>
                                <input
                                    wire:model="subject"
                                    type="text"
                                    id="subject"
                                    class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                    required
                                >
                                @error('subject') <span
                                    class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-12">
                                <label for="message"
                                       class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Message
                                    <span class="text-red-500">*</span></label>
                                <textarea
                                    wire:model="message"
                                    id="message"
                                    rows="12"
                                    class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                                    required
                                ></textarea>
                                @error('message') <span
                                    class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="bg-zinc-50 dark:bg-zinc-800 px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-end">
                    <button type="button" x-on:click="$dispatch('close')"
                            class="inline-flex justify-center py-2 px-4 border border-zinc-300 dark:border-zinc-600 shadow-sm text-sm font-medium rounded-md text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 mr-3">
                        Cancel
                    </button>
                    <button type="submit" form="emailForm"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                        Send Email
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
</div>
