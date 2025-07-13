<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscribe to') }} {{ $plan['name'] }} {{ __('Plan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Plan Summary -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Plan Summary</h3>

                            <div class="bg-gray-50 dark:bg-zinc-700 p-4 rounded-lg mb-6">
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Plan:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $plan['name'] }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Price:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ \App\Services\SubscriptionService::getCurrencySymbol() }}{{ number_format(\App\Services\SubscriptionService::convertPriceForInternationalUsers($plan['price']), \App\Services\SubscriptionService::getCurrencyCode() === 'USD' ? 2 : 0) }}/month</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Duration:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $plan['duration'] }} days</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Transaction Fee:</span>
                                    <span class="text-gray-900 dark:text-gray-100">No transaction fees</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Max Products:</span>
                                    <span class="text-gray-900 dark:text-gray-100">
                                        @if($plan['features']['max_products'] === 'unlimited')
                                            Unlimited
                                        @else
                                            {{ $plan['features']['max_products'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Max Designs:</span>
                                    <span class="text-gray-900 dark:text-gray-100">
                                        @if($plan['features']['max_designs'] === 'unlimited')
                                            Unlimited
                                        @else
                                            {{ $plan['features']['max_designs'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Online Store:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $plan['features']['store_enabled'] ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Appointments:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $plan['features']['appointments_enabled'] ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Public Appointment Booking:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $plan['features']['public_appointments_enabled'] ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Tax Reports:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $plan['features']['tax_reports_enabled'] ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Team Members:</span>
                                    <span class="text-gray-900 dark:text-gray-100">
                                        @if($plan['features']['max_team_members'] === 'unlimited')
                                            Unlimited
                                        @else
                                            {{ $plan['features']['max_team_members'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Custom Domain:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $plan['features']['custom_domain'] ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Payment Gateways:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ implode(', ', array_map('ucfirst', $plan['features']['payment_gateways'])) }}</span>
                                </div>
                            </div>

                            <div class="border-t dark:border-zinc-600 pt-4">
                                <h4 class="font-medium mb-2 text-gray-900 dark:text-gray-100">Important Notes:</h4>
                                <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                    <li>Your subscription will be active immediately after payment.</li>
                                    <li>You can cancel your subscription at any time.</li>
                                    <li>After cancellation, your subscription will remain active until the end of the current billing period.</li>
                                    <li>After your subscription ends, you will be automatically downgraded to the Free plan.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Payment Details</h3>

                            <form action="{{ route('subscriptions.process', $planKey) }}" method="POST">
                                @csrf

                                <div class="mb-6">
                                    <p class="text-gray-700 dark:text-gray-300 mb-2">You will be redirected to our secure payment gateway to complete your payment.</p>
                                    <p class="text-gray-700 dark:text-gray-300 mb-4">The total amount to be charged is <span class="font-semibold">{{ \App\Services\SubscriptionService::getCurrencySymbol() }}{{ number_format(\App\Services\SubscriptionService::convertPriceForInternationalUsers($plan['price']), \App\Services\SubscriptionService::getCurrencyCode() === 'USD' ? 2 : 0) }}</span>.</p>

                                    <div class="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 dark:border-yellow-500 p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400 dark:text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                                    By proceeding, you agree to our terms of service and subscription policies.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <a href="{{ route('subscriptions.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                        Cancel
                                    </a>
                                    <button type="submit" onclick="gtag_report_conversion('{{ route('subscriptions.index') }}')" class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white py-2 px-6 rounded">
                                        Proceed to Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
