<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-zinc-900 dark:to-zinc-800 text-zinc-900 dark:text-zinc-100 min-h-screen antialiased">
        <header class="w-full max-w-7xl mx-auto px-4 py-6 flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('home') }}" wire:navigate>
                    <div class="text-3xl font-bold text-orange-600 dark:text-orange-500">TailorSync</div>
                </a>
            </div>

            <nav class="flex items-center gap-4">
                @if (Route::has('login'))
                    <a
                        href="{{ route('login') }}"
                        class="inline-block px-5 py-2 text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 rounded-md text-sm font-medium transition-colors"
                        wire:navigate
                    >
                        Log in
                    </a>
                @endif

                @if (Route::has('register'))
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
            {{ $slot }}
        </main>

        <footer class="bg-zinc-100 dark:bg-zinc-900 py-8 mt-auto">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-500 mb-2">TailorSync</div>
                        <p class="text-zinc-600 dark:text-zinc-400">Tailoring Management System</p>
                    </div>
                </div>
                <div class="border-t border-zinc-200 dark:border-zinc-800 mt-8 pt-8 text-center text-zinc-500 dark:text-zinc-400">
                    <p>&copy; {{ date('Y') }} TailorSync. All rights reserved.</p>
                </div>
            </div>
        </footer>
        @fluxScripts
    </body>
</html>
