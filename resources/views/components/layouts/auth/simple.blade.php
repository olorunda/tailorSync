<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-zinc-900 dark:to-zinc-800 text-zinc-900 dark:text-zinc-100 min-h-screen antialiased">
        <header class="w-full max-w-7xl mx-auto px-4 py-6 flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('home') }}" wire:navigate>
                    <div class="text-3xl font-bold text-orange-600 dark:text-orange-500">{{env('APP_NAME','ThreadNix')}}</div>
                </a>
            </div>

            <nav class="flex items-center gap-4">
                @if (Route::has('login') && Route::currentRouteName() !== 'login')
                    <a
                        href="{{ route('login') }}" @click="tawk"
                        class="inline-block px-5 py-2 text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 rounded-md text-sm font-medium transition-colors"
                        wire:navigate
                    >
                        Log in
                    </a>
                @endif

                @if (Route::has('register') && Route::currentRouteName() !== 'register')
                    <a
                        href="{{ route('register') }}"
                        class="inline-block px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors"
                        wire:navigate
                    >
                        Register
                    </a>
                @endif
            </nav>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex min-h-[calc(100vh-300px)] flex-col items-center justify-center gap-6 p-6 md:p-10">
                <div class="flex w-full max-w-sm flex-col gap-2 bg-white dark:bg-zinc-800 p-8 rounded-xl shadow-md">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                        <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-orange-600 dark:text-orange-500" />
                        </span>
                        <span class="text-xl font-bold text-orange-600 dark:text-orange-500">{{env('APP_NAME','ThreadNix')}}</span>
                    </a>
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-zinc-100 dark:bg-zinc-900 py-8 mt-auto">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-500 mb-2">{{env('APP_NAME','ThreadNix')}}</div>
                        <p class="text-zinc-600 dark:text-zinc-400">Tailoring Management System</p>
                    </div>
                </div>
                <div class="border-t border-zinc-200 dark:border-zinc-800 mt-8 pt-8 text-center text-zinc-500 dark:text-zinc-400">
                    <p>&copy; {{ date('Y') }} {{env('APP_NAME','ThreadNix')}}. All rights reserved.</p>
                </div>
            </div>
        </footer>
        @fluxScripts
    </body>
</html>
