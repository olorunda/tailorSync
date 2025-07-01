<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Tax Report</h2>

            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    Generate a tax report for a specific date range to see your tax liability based on invoices, expenses, and payments.
                </p>
            </div>

            <!-- Date Range Selection -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-2 mb-4">
                    <button wire:click="setDateRange('current_month')" class="px-3 py-1 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-md transition-colors">
                        Current Month
                    </button>
                    <button wire:click="setDateRange('previous_month')" class="px-3 py-1 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-md transition-colors">
                        Previous Month
                    </button>
                    <button wire:click="setDateRange('current_quarter')" class="px-3 py-1 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-md transition-colors">
                        Current Quarter
                    </button>
                    <button wire:click="setDateRange('year_to_date')" class="px-3 py-1 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-md transition-colors">
                        Year to Date
                    </button>
                    <button wire:click="setDateRange('previous_year')" class="px-3 py-1 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-md transition-colors">
                        Previous Year
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="startDate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Start Date</label>
                        <input type="date" id="startDate" wire:model="startDate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                        @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="endDate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">End Date</label>
                        <input type="date" id="endDate" wire:model="endDate" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                        @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button wire:click="generateReport" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                    <span wire:loading.remove wire:target="generateReport">Generate Report</span>
                    <span wire:loading wire:target="generateReport">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>

            <!-- Tax Report Results -->
            @if($taxSummary)
                @if(isset($taxSummary['error']))
                    <div class="bg-red-100 dark:bg-red-900/30 p-4 rounded-lg mb-6">
                        <p class="text-red-700 dark:text-red-300">{{ $taxSummary['error'] }}</p>
                    </div>
                @else
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden mb-6">
                        <div class="bg-zinc-50 dark:bg-zinc-800 p-4 border-b border-zinc-200 dark:border-zinc-700">
                            <h3 class="font-medium text-zinc-900 dark:text-zinc-100">
                                Tax Summary for {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}
                            </h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                Tax Country: {{ ucfirst($taxSummary['tax_country']) }}
                            </p>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Revenue</p>
                                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['total_revenue'], 2) }}</p>
                                </div>

                                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Tax Collected</p>
                                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['total_tax_collected'], 2) }}</p>
                                </div>

                                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Expenses</p>
                                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['total_expenses'], 2) }}</p>
                                </div>

                                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Payments</p>
                                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['total_payments'], 2) }}</p>
                                </div>
                            </div>

                            <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg mb-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-medium text-orange-800 dark:text-orange-300">Tax Payable</h4>
                                        <p class="text-sm text-orange-700 dark:text-orange-400 mt-1">
                                            Estimated tax liability for the selected period
                                        </p>
                                    </div>
                                    <div class="text-2xl font-bold text-orange-800 dark:text-orange-300">
                                        {{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['tax_payable'], 2) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Tax Details based on country -->
                            <div class="mt-6">
                                <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Tax Calculation Details</h4>

                                @if($taxSummary['tax_country'] === 'canada')
                                    <div class="space-y-2">
                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">GST Collected</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['gst_collected'], 2) }}</span>
                                        </div>

                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">PST Collected</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['pst_collected'], 2) }}</span>
                                        </div>

                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">HST Collected</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['hst_collected'], 2) }}</span>
                                        </div>

                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">Input Tax Credits</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['input_tax_credits'], 2) }}</span>
                                        </div>
                                    </div>
                                @elseif($taxSummary['tax_country'] === 'us')
                                    <div class="space-y-2">
                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">State Tax</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['state_tax'], 2) }}</span>
                                        </div>

                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">Local Tax</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['local_tax'], 2) }}</span>
                                        </div>
                                    </div>
                                @elseif($taxSummary['tax_country'] === 'uk')
                                    <div class="space-y-2">
                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">VAT Collected</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['vat_collected'], 2) }}</span>
                                        </div>

                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">Input VAT</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['input_vat'], 2) }}</span>
                                        </div>
                                    </div>
                                @elseif($taxSummary['tax_country'] === 'nigeria')
                                    <div class="space-y-2">
                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">VAT Collected</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['vat_collected'], 2) }}</span>
                                        </div>

                                        <div class="flex justify-between py-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <span class="text-zinc-600 dark:text-zinc-400">Input VAT</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($taxSummary['tax_liability']['details']['input_vat'], 2) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-6 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    <strong>Note:</strong> This tax report is for informational purposes only. Please consult with a tax professional for accurate tax filing advice.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-6 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <p class="text-zinc-600 dark:text-zinc-400">Select a date range and generate a report to see your tax summary.</p>
                </div>
            @endif
        </div>
    </div>
</div>
