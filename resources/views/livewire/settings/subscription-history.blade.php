<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>
        <x-slot:heading>
            {{ __('Subscription History') }}
        </x-slot>
        <x-slot:subheading>
            {{ __('View your subscription history and changes over time') }}
        </x-slot>

        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Subscription Records</h2>

                    @if($subscriptionHistory->isEmpty())
                        <div class="bg-zinc-100 dark:bg-zinc-700 p-4 rounded-lg">
                            <p class="text-zinc-600 dark:text-zinc-400 text-center">
                                No subscription history records found.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Plan
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Period
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Payment Method
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($subscriptionHistory as $record)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $record->created_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($record->subscription_plan == 'premium')
                                                        bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                                    @elseif($record->subscription_plan == 'basic')
                                                        bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                    @else
                                                        bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200
                                                    @endif
                                                ">
                                                    {{ ucfirst($record->subscription_plan) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($record->subscription_active)
                                                        bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @else
                                                        bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @endif
                                                ">
                                                    {{ $record->subscription_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                                @if($record->subscription_start_date && $record->subscription_end_date)
                                                    {{ $record->subscription_start_date->format('M d, Y') }} - {{ $record->subscription_end_date->format('M d, Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $record->subscription_payment_method ? ucfirst($record->subscription_payment_method) : 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
