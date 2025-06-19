@section('breadcrumbs')
    <flux:breadcrumbs.item href="{{ route('settings.profile') }}">{{ __('Settings') }}</flux:breadcrumbs.item>
    <flux:breadcrumbs.item href="{{ route('settings.public-booking') }}" current>{{ __('Public Booking') }}</flux:breadcrumbs.item>
@endsection

<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $bookingUrl = '';
    public bool $showCopiedMessage = false;

    public function mount()
    {
        $user = Auth::user();
        $this->bookingUrl = $user->getBookingUrl() ?? '';
    }

    public function generateBookingUrl()
    {
        $user = Auth::user();
        $this->bookingUrl = $user->generateBookingHash();
        $this->bookingUrl = $user->getBookingUrl();
    }

    public function resetBookingUrl()
    {
        $user = Auth::user();
        $user->update(['booking_hash' => null]);
        $this->bookingUrl = '';
    }

    public function showCopiedMessage()
    {
        $this->showCopiedMessage = true;

        // Hide the message after 3 seconds
        $this->dispatch('hideCopiedMessage');
    }
}; ?>

<div class="w-full">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Public Appointment Booking</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage your public appointment booking URL</p>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Your Public Booking URL</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    Share this URL with your clients to allow them to book appointments with you directly.
                    The URL is unique to your account and can be regenerated if needed.
                </p>

                @if ($bookingUrl)
                    <div class="flex flex-col sm:flex-row gap-2 mb-4">
                        <div class="flex-grow">
                            <div class="relative">
                                <input type="text" readonly value="{{ $bookingUrl }}"
                                    class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-700 rounded-md bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                                <div x-data="{ copied: @entangle('showCopiedMessage') }"
                                    x-init="$wire.on('hideCopiedMessage', () => { setTimeout(() => { copied = false }, 3000) })"
                                    class="absolute right-2 top-2">
                                    <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $bookingUrl }}'); $wire.showCopiedMessage()"
                                        class="text-orange-600 hover:text-orange-700 dark:text-orange-500 dark:hover:text-orange-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                        </svg>
                                    </button>
                                    <span x-show="copied" x-transition class="absolute right-0 top-8 bg-zinc-800 text-white text-xs px-2 py-1 rounded">
                                        Copied!
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="{{ $bookingUrl }}" target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                </svg>
                                Open Booking Page
                            </a>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button wire:click="generateBookingUrl" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                            Regenerate URL
                        </button>
                        <button wire:click="resetBookingUrl" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Disable Booking URL
                        </button>
                    </div>
                @else
                    <div class="flex items-center justify-center p-6 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">No booking URL generated</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Generate a public booking URL to allow clients to book appointments with you.
                            </p>
                            <div class="mt-4">
                                <button wire:click="generateBookingUrl" wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Generate Booking URL
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">How It Works</h2>
                <div class="space-y-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-500">
                                1
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Generate your booking URL</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Create a unique URL that clients can use to book appointments with you.
                            </p>
                        </div>
                    </div>
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-500">
                                2
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Share with clients</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Share your booking URL with clients via email, social media, or your website.
                            </p>
                        </div>
                    </div>
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-500">
                                3
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Clients book appointments</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Clients can select available time slots and book appointments directly.
                            </p>
                        </div>
                    </div>
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-500">
                                4
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Manage appointments</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                View and manage all appointments in your appointment dashboard.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
