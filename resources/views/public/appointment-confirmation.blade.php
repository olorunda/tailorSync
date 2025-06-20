<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Appointment Confirmation - {{ $businessName }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    <div class="min-h-screen">
        <header class="bg-white dark:bg-zinc-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="/">
                            <x-app-logo-icon class="block h-9 w-auto" />
                        </a>
                    </div>
                    <h1 class="ml-4 text-xl font-semibold">Appointment Confirmation</h1>
                </div>
                <div>
                    <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                        </svg>
                        Print
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-lg">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-6">Your Appointment Details</h2>

                    <div class="space-y-6">
                        <!-- Business Info -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Business</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $businessName }}</p>
                            </div>
                        </div>

                        <!-- Client Info -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                <span class="text-orange-600 dark:text-orange-500 font-medium text-sm">{{ strtoupper(substr($appointment->client->name ?? 'NA', 0, 2)) }}</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Client</p>
                                <p class="text-zinc-900 dark:text-zinc-100">
                                    {{ $appointment->client->name ?? 'No Client' }}
                                </p>
                                @if($appointment->client && $appointment->client->email)
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $appointment->client->email }}</p>
                                @endif
                                @if($appointment->client && $appointment->client->phone)
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $appointment->client->phone }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Appointment Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Date</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $appointment->date->format('l, F j, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Time</p>
                                <p class="text-zinc-900 dark:text-zinc-100">
                                    {{ $appointment->start_time->format('g:i A') }} - {{ $appointment->end_time->format('g:i A') }}
                                </p>
                            </div>
                        </div>

                        <!-- Location -->
                        @if($appointment->location)
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Location</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $appointment->location }}</p>
                            </div>
                        @endif

                        <!-- Description -->
                        @if($appointment->description)
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Description</p>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $appointment->description }}</p>
                            </div>
                        @endif

                        <!-- Status -->
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Status</p>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                Confirmed
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Thank you for booking an appointment with us. If you need to make any changes to your appointment, please contact us directly.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('appointments.public.booking', ['slug' => $user->getBusinessSlug()]) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                Back to Booking Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-white dark:bg-zinc-800 shadow mt-6">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                    &copy; {{ date('Y') }} {{ $businessName }}. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
