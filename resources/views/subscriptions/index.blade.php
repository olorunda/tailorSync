<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscription Plans') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('subscription_limit_reached') && session('subscription_feature'))
                <x-subscription-limit-notice
                    feature="{{ session('subscription_feature') }}"
                    plan="{{ session('subscription_plan', $currentPlan) }}"
                    message="{{ session('error') }}"
                />
            @elseif (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Your Current Plan</h3>

                <div class="mb-8">
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-zinc-700 p-4 rounded-lg">
                        <div>
                            <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $plans[$currentPlan]['name'] }} Plan</h4>
                            <p class="text-gray-600 dark:text-gray-300">
                                @if($isActive)
                                    <span class="text-green-600 dark:text-green-400">Active</span>
                                    @if($currentPlan !== 'free' && $businessDetail->subscription_end_date)
                                        until {{ $businessDetail->subscription_end_date->format('F j, Y') }}
                                    @endif
                                @else
                                    <span class="text-red-600 dark:text-red-400">Inactive</span>
                                @endif
                            </p>
                        </div>

                        @if($currentPlan !== 'free' && $isActive)
                            <form action="{{ route('subscriptions.cancel') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription?');">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                                    Cancel Subscription
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Available Plans</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($plans as $key => $plan)
                        <div class="border dark:border-zinc-600 rounded-lg overflow-hidden {{ $currentPlan === $key ? 'border-blue-500 ring-2 ring-blue-500' : '' }}">
                            <div class="bg-gray-50 dark:bg-zinc-700 p-4 border-b dark:border-zinc-600">
                                <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $plan['name'] }}</h4>
                                <p class="text-2xl font-bold mt-2 text-gray-900 dark:text-gray-100">
                                    @if($plan['price'] > 0)
                                        {{ \App\Services\SubscriptionService::getCurrencySymbol() }}{{ number_format(\App\Services\SubscriptionService::convertPriceForInternationalUsers($plan['price']), \App\Services\SubscriptionService::getCurrencyCode() === 'USD' ? 2 : 0) }}<span class="text-sm font-normal text-gray-600 dark:text-gray-300">/month</span>
                                    @else
                                        Free
                                    @endif
                                </p>
                            </div>

                            <div class="p-4 dark:bg-zinc-800">
                                <ul class="space-y-2 text-gray-900 dark:text-gray-100">

                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 {{ $plan['features']['max_products']!=0 ? 'text-green-500' : 'text-red-500' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            @if($plan['features']['max_products']>0 || $plan['features']['max_products'] === 'unlimited')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            @endif
                                        </svg>
                                        <span>
                                            @if($plan['features']['max_products'] === 'unlimited')
                                                Unlimited products
                                            @elseif($plan['features']['max_products']>0)
                                                Up to {{ $plan['features']['max_products'] }} products
                                            @else
                                                0 Products
                                            @endif
                                        </span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>
                                            @if($plan['features']['max_designs'] === 'unlimited')
                                                Unlimited designs
                                            @else
                                                Up to {{ $plan['features']['max_designs'] }} designs
                                            @endif
                                        </span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>No transaction fees</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 {{ $plan['features']['store_enabled'] ? 'text-green-500' : 'text-red-500' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            @if($plan['features']['store_enabled'])
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            @endif
                                        </svg>
                                        <span>Online store</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 {{ $plan['features']['appointments_enabled'] ? 'text-green-500' : 'text-red-500' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            @if($plan['features']['appointments_enabled'])
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            @endif
                                        </svg>
                                        <span>Appointments</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 {{ $plan['features']['public_appointments_enabled'] ? 'text-green-500' : 'text-red-500' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            @if($plan['features']['public_appointments_enabled'])
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            @endif
                                        </svg>
                                        <span>Public appointment booking</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 {{ $plan['features']['tax_reports_enabled'] ? 'text-green-500' : 'text-red-500' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            @if($plan['features']['tax_reports_enabled'])
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            @endif
                                        </svg>
                                        <span>Tax reports</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 {{ $plan['features']['ai_style_suggestions'] ? 'text-green-500' : 'text-red-500' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            @if($plan['features']['ai_style_suggestions'])
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            @endif
                                        </svg>
                                        <span>AI style suggestions</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>
                                            @if($plan['features']['max_team_members'] === 'unlimited')
                                                Unlimited team members
                                            @else
                                                {{ $plan['features']['max_team_members'] }} team member{{ $plan['features']['max_team_members'] > 1 ? 's' : '' }}
                                            @endif
                                        </span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 {{ $plan['features']['custom_domain'] ? 'text-green-500' : 'text-red-500' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            @if($plan['features']['custom_domain'])
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            @endif
                                        </svg>
                                        <span>Custom domain</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>
                                            {{ count($plan['features']['payment_gateways']) }} payment gateway{{ count($plan['features']['payment_gateways']) > 1 ? 's' : '' }}
                                        </span>
                                    </li>
                                </ul>

                                <div class="mt-6">
                                    @if($currentPlan === $key && $isActive)
                                        <button disabled class="w-full bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 px-4 rounded cursor-not-allowed">
                                            Current Plan
                                        </button>
                                    @elseif($isActive && $isDowngrade($currentPlan, $key))
                                        <button disabled class="w-full bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 px-4 rounded cursor-not-allowed">
                                            Cannot Downgrade
                                            <span class="block text-xs mt-1">Cancel current plan first</span>
                                        </button>
                                    @else
                                        <a href="{{ route('subscriptions.checkout', $key) }}" class="block w-full bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-center py-2 px-4 rounded">
                                            @if($key === 'free')
                                                Switch to Free
                                            @else
                                                Subscribe
                                            @endif
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
