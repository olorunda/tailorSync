<x-layouts.storefront :businessDetail="$businessDetail" :cart="$cart" :currencySymbol="$currencySymbol" title="Register">
    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100 mb-6">Create an Account</h2>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('storefront.register.process', $businessDetail->store_slug) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                                   class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 shadow-sm focus:border-primary-custom focus:ring focus:ring-primary-custom focus:ring-opacity-50">
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                   class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 shadow-sm focus:border-primary-custom focus:ring focus:ring-primary-custom focus:ring-opacity-50">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
                            <input type="password" name="password" id="password" required
                                   class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 shadow-sm focus:border-primary-custom focus:ring focus:ring-primary-custom focus:ring-opacity-50">
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Minimum 8 characters</p>
                        </div>

                        <div class="mb-6">
                            <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 shadow-sm focus:border-primary-custom focus:ring focus:ring-primary-custom focus:ring-opacity-50">
                        </div>

                        <div>
                            <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-custom hover:bg-secondary-custom focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-custom">
                                Register
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Already have an account?
                            <a href="{{ route('storefront.login', $businessDetail->store_slug) }}" class="text-primary-custom hover:text-secondary-custom">
                                Login here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.storefront>
