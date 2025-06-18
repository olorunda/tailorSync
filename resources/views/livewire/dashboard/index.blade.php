<?php

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {
    public $timeframe = 'month';
    public $orderStatuses = [];
    public $totalRevenue = 0;
    public $totalExpenses = 0;
    public $netProfit = 0;
    public $pendingPayments = 0;
    public $recentOrders = [];
    public $recentInvoices = [];
    public $monthlyRevenue = [];
    public $monthlyExpenses = [];
    public $ordersByStatus = [];

    public function mount()
    {
        // Check if user has permission to view dashboard data
        $user = auth()->user();
        $canViewOrders = $user->hasPermission('view_orders');
        $canViewInvoices = $user->hasPermission('view_invoices');
        $canViewPayments = $user->hasPermission('view_payments');
        $canViewExpenses = $user->hasPermission('view_expenses');

        // If user doesn't have any of these permissions, show error
        if (!$canViewOrders && !$canViewInvoices && !$canViewPayments && !$canViewExpenses) {
            session()->flash('error', 'You do not have permission to view dashboard data.');
            return $this->redirect(route('dashboard'));
        }

        $this->loadData();
    }

    public function loadData()
    {
        $user = Auth::user();
        $canViewOrders = $user->hasPermission('view_orders');
        $canViewInvoices = $user->hasPermission('view_invoices');
        $canViewPayments = $user->hasPermission('view_payments');
        $canViewExpenses = $user->hasPermission('view_expenses');

        // Get date range based on timeframe
        $startDate = now();
        $endDate = now();

        if ($this->timeframe === 'week') {
            $startDate = now()->subWeek();
        } elseif ($this->timeframe === 'month') {
            $startDate = now()->subMonth();
        } elseif ($this->timeframe === 'quarter') {
            $startDate = now()->subMonths(3);
        } elseif ($this->timeframe === 'year') {
            $startDate = now()->subYear();
        }

        // Calculate total revenue (from payments)
        if ($canViewPayments) {
            $this->totalRevenue = $user->allPayments()
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->sum('amount');
        } else {
            $this->totalRevenue = 0;
        }

        // Calculate total expenses
        if ($canViewExpenses) {
            $this->totalExpenses = $user->allExpenses()
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');
        } else {
            $this->totalExpenses = 0;
        }

        // Calculate net profit
        $this->netProfit = $this->totalRevenue - $this->totalExpenses;

        // Calculate pending payments (from unpaid invoices)
        if ($canViewInvoices) {
            $this->pendingPayments = $user->allInvoices()
                ->where('status', '!=', 'paid')
                ->sum('total');
        } else {
            $this->pendingPayments = 0;
        }

        // Get recent orders
        if ($canViewOrders) {
            $this->recentOrders = $user->allOrders()
                ->with('client')
                ->latest()
                ->take(5)
                ->get();
        } else {
            $this->recentOrders = collect();
        }

        // Get recent invoices
        if ($canViewInvoices) {
            $this->recentInvoices = $user->allInvoices()
                ->with('client')
                ->latest()
                ->take(5)
                ->get();
        } else {
            $this->recentInvoices = collect();
        }

        // Get order statuses for chart
        if ($canViewOrders) {
            $this->ordersByStatus = $user->allOrders()
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
        } else {
            $this->ordersByStatus = [];
        }

        // Get monthly revenue data for chart
        if ($canViewPayments) {
            $this->monthlyRevenue = $this->getMonthlyData(Payment::class, 'payment_date', 'amount', $user);
        } else {
            $this->monthlyRevenue = [];
        }

        // Get monthly expenses data for chart
        if ($canViewExpenses) {
            $this->monthlyExpenses = $this->getMonthlyData(Expense::class, 'date', 'amount', $user);
        } else {
            $this->monthlyExpenses = [];
        }
    }

    private function getMonthlyData($model, $dateField, $amountField, $user)
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        // Use the appropriate "all*" method based on the model
        $query = null;
        if ($model === Payment::class) {
            $query = $user->allPayments();
        } elseif ($model === Expense::class) {
            $query = $user->allExpenses();
        } else {
            // Fallback to the old method if model is not recognized
            $query = $model::where('user_id', $user->id);
        }

        $data = $query->whereBetween($dateField, [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT($dateField, '%Y-%m') as month"),
                DB::raw("SUM($amountField) as total")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Fill in missing months with zero
        $result = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $result[$monthKey] = $data[$monthKey] ?? 0;
            $currentDate->addMonth();
        }

        return $result;
    }

    public function updatedTimeframe()
    {
        // Check if user has permission to view dashboard data
        $user = auth()->user();
        $canViewOrders = $user->hasPermission('view_orders');
        $canViewInvoices = $user->hasPermission('view_invoices');
        $canViewPayments = $user->hasPermission('view_payments');
        $canViewExpenses = $user->hasPermission('view_expenses');

        // If user doesn't have any of these permissions, show error
        if (!$canViewOrders && !$canViewInvoices && !$canViewPayments && !$canViewExpenses) {
            session()->flash('error', 'You do not have permission to view dashboard data.');
            return;
        }

        $this->loadData();
    }
}; ?>

<div class="w-full">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Dashboard</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Financial and order insights for your business</p>
        </div>
        <div>
            <select wire:model.live="timeframe" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                <option value="week">Last 7 days</option>
                <option value="month">Last 30 days</option>
                <option value="quarter">Last 3 months</option>
                <option value="year">Last 12 months</option>
            </select>
        </div>
    </div>
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if(auth()->user()->hasPermission('view_payments'))
        <!-- Total Revenue Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Revenue</h3>
                <div class="flex-shrink-0 h-10 w-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($totalRevenue, 2) }}</span>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('view_expenses'))
        <!-- Total Expenses Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Expenses</h3>
                <div class="flex-shrink-0 h-10 w-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($totalExpenses, 2) }}</span>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('view_payments') && auth()->user()->hasPermission('view_expenses'))
        <!-- Net Profit Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Net Profit</h3>
                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500' }}">
                    {{ Auth::user()->getCurrencySymbol() }}{{ number_format($netProfit, 2) }}
                </span>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('view_invoices'))
        <!-- Pending Payments Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending Payments</h3>
                <div class="flex-shrink-0 h-10 w-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 dark:text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ Auth::user()->getCurrencySymbol() }}{{ number_format($pendingPayments, 2) }}</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        @if(auth()->user()->hasPermission('view_payments') && auth()->user()->hasPermission('view_expenses'))
        <!-- Revenue vs Expenses Chart -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Revenue vs Expenses</h3>
            <div id="revenue-expenses-chart" class="h-80"></div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('view_orders'))
        <!-- Orders by Status Chart -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Orders by Status</h3>
            <div id="orders-status-chart" class="h-80"></div>
        </div>
        @endif
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if(auth()->user()->hasPermission('view_orders'))
        <!-- Recent Orders -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Recent Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Order</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Client</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                <td class="px-6 py-4 whitespace-nowrap" data-label="Order">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $order->due_date ? $order->due_date->format('M d, Y') : 'No date' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="Client">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->client->name ?? 'No client' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="Status">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($order->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                        @elseif($order->status === 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                        @elseif($order->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                        @else bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100" data-label="Amount">
                                    {{ Auth::user()->getCurrencySymbol() }}{{ number_format($order->total_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No recent orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('view_invoices'))
        <!-- Recent Invoices -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Recent Invoices</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Invoice</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Client</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($recentInvoices as $invoice)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                <td class="px-6 py-4 whitespace-nowrap" data-label="Invoice">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'No date' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="Client">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->client->name ?? 'No client' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="Status">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($invoice->status === 'paid') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                        @elseif($invoice->status === 'partial') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                        @elseif($invoice->status === 'unpaid') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400
                                        @elseif($invoice->status === 'overdue') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                        @else bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400
                                        @endif">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100" data-label="Amount">
                                    {{ Auth::user()->getCurrencySymbol() }}{{ number_format($invoice->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No recent invoices found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ApexCharts JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // Define charts as global variables
    let revenueExpensesChart = null;
    let ordersStatusChart = null;

    // Initialize charts immediately and on Livewire updates
    document.addEventListener('DOMContentLoaded', initCharts);
    document.addEventListener('livewire:initialized', initCharts);
    document.addEventListener('livewire:navigated', initCharts);

    // Add a fallback initialization after a short delay
    window.addEventListener('load', function() {
        setTimeout(initCharts, 500);
    });

    // Main initialization function
    function initCharts() {
        console.log('Initializing charts...');

        // Check if chart containers exist
        const revenueChartContainer = document.getElementById('revenue-expenses-chart');
        const ordersChartContainer = document.getElementById('orders-status-chart');

        if (!revenueChartContainer || !ordersChartContainer) {
            console.log('Chart containers not found, will retry...');
            setTimeout(initCharts, 200);
            return;
        }

        // Clean up existing charts if they exist
        if (revenueExpensesChart) {
            try {
                revenueExpensesChart.destroy();
            } catch (e) {
                console.error('Error destroying revenue chart:', e);
            }
            revenueExpensesChart = null;
        }

        if (ordersStatusChart) {
            try {
                ordersStatusChart.destroy();
            } catch (e) {
                console.error('Error destroying orders chart:', e);
            }
            ordersStatusChart = null;
        }

        // Initialize revenue chart
        try {
            const monthlyRevenue = @json($monthlyRevenue);
            const monthlyExpenses = @json($monthlyExpenses);

            if (!monthlyRevenue || !monthlyExpenses) {
                console.log('Chart data not available yet');
                return;
            }

            const months = Object.keys(monthlyRevenue);
            const revenueData = months.map(month => monthlyRevenue[month]);
            const expensesData = months.map(month => monthlyExpenses[month]);

            const revenueOptions = {
                series: [{
                    name: 'Revenue',
                    data: revenueData
                }, {
                    name: 'Expenses',
                    data: expensesData
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    stacked: false,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: months.map(month => {
                        const date = new Date(month + '-01');
                        return date.toLocaleString('default', { month: 'short' }) + ' ' + date.getFullYear();
                    }),
                },
                yaxis: {
                    title: {
                        text: 'Amount'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return '{{ Auth::user()->getCurrencySymbol() }}' + val.toFixed(2)
                        }
                    }
                },
                colors: ['#10b981', '#ef4444']
            };

            revenueExpensesChart = new ApexCharts(revenueChartContainer, revenueOptions);
            revenueExpensesChart.render();
            console.log('Revenue chart initialized');
        } catch (e) {
            console.error('Error initializing revenue chart:', e);
        }

        // Initialize orders chart
        try {
            const ordersByStatus = @json($ordersByStatus);

            if (!ordersByStatus) {
                console.log('Orders data not available yet');
                return;
            }

            const statuses = Object.keys(ordersByStatus);
            const counts = Object.values(ordersByStatus);

            const ordersOptions = {
                series: counts,
                chart: {
                    type: 'donut',
                    height: 320
                },
                labels: statuses.map(status => ucfirst(status.replace('_', ' '))),
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6']
            };

            ordersStatusChart = new ApexCharts(ordersChartContainer, ordersOptions);
            ordersStatusChart.render();
            console.log('Orders chart initialized');
        } catch (e) {
            console.error('Error initializing orders chart:', e);
        }
    }

    // Listen for Livewire updates to refresh charts
    document.addEventListener('livewire:update', function() {
        console.log('Livewire updated, refreshing charts...');
        setTimeout(initCharts, 100);
    });

    // Helper function
    function ucfirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>
