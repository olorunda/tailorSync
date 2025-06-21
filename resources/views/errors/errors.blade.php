<x-layouts.auth.simple title="Error">
    <div class="flex flex-col items-center justify-center text-center">
        <div class="mb-6">
            <svg class="h-32 w-32 text-orange-600 dark:text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-zinc-900 dark:text-white mb-2">@yield('code', 'Error')</h1>
        <h2 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-200 mb-4">@yield('title', 'Something went wrong')</h2>
        <p class="text-zinc-600 dark:text-zinc-400 mb-8">
            @yield('message', 'We encountered an error while processing your request.')
        </p>
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-base font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Back to Home
        </a>
    </div>
</x-layouts.auth.simple>
