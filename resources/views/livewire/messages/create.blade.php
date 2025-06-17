<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $subject = '';
    public string $message = '';
    public string $recipient_id = '';
    public ?string $reply_to = null;

    public function mount()
    {
        // Check if this is a reply to an existing message
        if (request()->has('reply_to')) {
            $this->reply_to = request()->get('reply_to');
            $originalMessage = Message::find($this->reply_to);

            if ($originalMessage && ($originalMessage->sender_id === Auth::id() || $originalMessage->recipient_id === Auth::id())) {
                // Set the recipient to the sender of the original message
                $this->recipient_id = (string) $originalMessage->sender_id;

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
        $validated = $this->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $message = new Message();
        $message->sender_id = Auth::id();
        $message->user_id = Auth::id();
        $message->recipient_id = $validated['recipient_id'];
        $message->subject = $validated['subject'];
        $message->message = $validated['message'];
        $message->save();

        session()->flash('status', 'Message sent successfully!');
        $this->redirect(route('messages.index'));
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
            'users' => User::where('id', '!=', Auth::id())
                ->orderBy('name')
                ->get(),
        ];
    }
}; ?>

<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $reply_to ? 'Reply to Message' : 'New Message' }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">{{ $reply_to ? 'Send a reply to the original message' : 'Compose a new message to a client or team member' }}</p>
        </div>
        <a href="{{ route('messages.index') }}" class="inline-flex items-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Messages
        </a>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="send" class="p-6">
            <div class="space-y-6">
                <div>
                    <label for="recipient_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient <span class="text-red-500">*</span></label>
                    <select
                        wire:model="recipient_id"
                        id="recipient_id"
                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                        {{ $reply_to ? 'disabled' : '' }}
                        required
                    >
                        <option value="">Select Recipient</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('recipient_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject <span class="text-red-500">*</span></label>
                    <input wire:model="subject" type="text" id="subject" class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required>
                    @error('subject') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Message <span class="text-red-500">*</span></label>
                    <textarea
                        wire:model="message"
                        id="message"
                        rows="10"
                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                        required
                    ></textarea>
                    @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-end">
                    <a href="{{ route('messages.index') }}" class="inline-flex justify-center py-2 px-4 border border-zinc-300 dark:border-zinc-600 shadow-sm text-sm font-medium rounded-md text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 mr-3">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                        Send Message
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
