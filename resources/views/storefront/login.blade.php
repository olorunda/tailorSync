<x-layouts.storefront :businessDetail="$businessDetail" :cart="$cart" :currencySymbol="$currencySymbol" title="Login">
    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100 mb-6">Login to Your Account</h2>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('storefront.login.process', $businessDetail->store_slug) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                   class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 shadow-sm focus:border-primary-custom focus:ring focus:ring-primary-custom focus:ring-opacity-50">
                        </div>

                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
                            <input type="password" name="password" id="password" required
                                   class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 shadow-sm focus:border-primary-custom focus:ring focus:ring-primary-custom focus:ring-opacity-50">
                        </div>

                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="remember" id="remember" class="rounded border-zinc-300 dark:border-zinc-700 text-primary-custom focus:ring-primary-custom">
                                <label for="remember" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Remember me</label>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-custom hover:bg-secondary-custom focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-custom">
                                Login
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Don't have an account?
                            <a href="{{ route('storefront.register', $businessDetail->store_slug) }}" class="text-primary-custom hover:text-secondary-custom">
                                Register now
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.storefront>
