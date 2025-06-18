<?php

use App\Models\Message;
use App\Models\User;
use App\Models\Client;
use App\Notifications\EmailMessageNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Component;

new class extends Component {
    public string $subject = '';
    public string $message = '';
    public string $recipient_type = 'user';
    public string $recipient_id = '';
    public ?string $reply_to = null;
    public bool $send_email_notification = true;

    public function mount()
    {
        // Check if this is a reply to an existing message
        if (request()->has('reply_to')) {
            $this->reply_to = request()->get('reply_to');
            $originalMessage = Message::find($this->reply_to);

            if ($originalMessage && ($originalMessage->sender_id === Auth::id() || $originalMessage->recipient_id === Auth::id())) {
                // Determine if the original message was to/from a client or user
                if ($originalMessage->client_id) {
                    // Message was to/from a client
                    $this->recipient_type = 'client';
                    $this->recipient_id = (string) $originalMessage->client_id;
                } else {
                    // Message was to/from a user
                    $this->recipient_type = 'user';
                    // Set the recipient to the sender of the original message
                    $this->recipient_id = (string) $originalMessage->sender_id;
                }

                // Pre-fill the subject with "Re: " prefix if it doesn't already have it
                $this->subject = !str_starts_with($originalMessage->subject, 'Re: ')
                    ? 'Re: ' . $originalMessage->subject
                    : $originalMessage->subject;

                // Pre-fill the message with a quote of the original message
                $this->message = "\n\n\n----- Original Message -----\n" . $originalMessage->message;
            }
        }
    }

    public function send()
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

        session()->flash('status', 'Message sent successfully!');
        $this->redirect(route('notifications.index'));
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

        return [
            'users' => User::where('id', '!=', Auth::id())
                ->orderBy('name')
                ->get(),
            'clients' => $user->allClients()
                ->orderBy('name')
                ->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $reply_to ? 'Reply to Email' : 'Send Email Notification' }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">{{ $reply_to ? 'Send a reply to the original email' : 'Send an email notification to a client or team member' }}</p>
        </div>
        <a href="{{ route('notifications.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Notifications
        </a>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="send" class="p-6 space-y-6">
            <!-- Recipient Information -->
            <div>
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Recipient Information</h2>

                <div class="space-y-4">
                    <div>
                        <label for="recipient_type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Recipient Type <span class="text-red-500">*</span></label>
                        <select
                            wire:model.live="recipient_type"
                            id="recipient_type"
                            class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                            {{ $reply_to ? 'disabled' : '' }}
                            required
                        >
                            <option value="user">Team Member</option>
                            <option value="client">Client</option>
                        </select>
                        @error('recipient_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="recipient_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Recipient <span class="text-red-500">*</span></label>
                        <select
                            wire:model="recipient_id"
                            id="recipient_id"
                            class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                            {{ $reply_to ? 'disabled' : '' }}
                            required
                        >
                            <option value="">Select Recipient</option>
                            @if ($recipient_type === 'user')
                                @foreach (Auth::user()->allTeamMembers()->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            @else
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('recipient_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div>
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Message Content</h2>

                <div class="space-y-4">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Subject <span class="text-red-500">*</span></label>
                        <input
                            wire:model="subject"
                            type="text"
                            id="subject"
                            class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                            required
                        >
                        @error('subject') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea
                            wire:model="message"
                            id="message"
                            rows="10"
                            class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"
                            required
                        ></textarea>
                        @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Notification Options -->
            <div>
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Notification Options</h2>

                <div class="space-y-4">
                    <div class="flex items-center">
                        <input
                            wire:model="send_email_notification"
                            id="send_email_notification"
                            type="checkbox"
                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-zinc-300 dark:border-zinc-700 rounded"
                        >
                        <label for="send_email_notification" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                            Send email notification to recipient
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-zinc-200 dark:border-zinc-700 mt-6">
                <a href="{{ route('notifications.index') }}" class="inline-flex justify-center py-2 px-4 border border-zinc-300 dark:border-zinc-600 shadow-sm text-sm font-medium rounded-md text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 mr-3">
                    Cancel
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                    Send Email
                </button>
            </div>
        </form>
    </div>
</div>
