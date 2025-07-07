<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class StoreOrderService
{
    /**
     * Get filtered and paginated store orders for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredOrders(Request $request)
    {
        $query = Order::where('user_id', auth()->id())
            ->where('is_store_order', true);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Apply payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        // Apply date range filter
        if ($request->filled('date_range')) {
            $dateRange = $request->input('date_range');
            if ($dateRange === 'today') {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($dateRange === 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($dateRange === 'this_month') {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            } elseif ($dateRange === 'last_month') {
                $query->whereMonth('created_at', now()->subMonth()->month)
                      ->whereYear('created_at', now()->subMonth()->year);
            } elseif ($dateRange === 'this_year') {
                $query->whereYear('created_at', now()->year);
            }
        }

        // Apply due date filter
        if ($request->filled('due_date')) {
            $dueDate = $request->input('due_date');
            if ($dueDate === 'overdue') {
                $query->where('due_date', '<', now()->toDateString());
            } elseif ($dueDate === 'today') {
                $query->whereDate('due_date', now()->toDateString());
            } elseif ($dueDate === 'this_week') {
                $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($dueDate === 'next_week') {
                $query->whereBetween('due_date', [now()->addWeek()->startOfWeek(), now()->addWeek()->endOfWeek()]);
            } elseif ($dueDate === 'this_month') {
                $query->whereMonth('due_date', now()->month)
                      ->whereYear('due_date', now()->year);
            }
        }

        // Apply sorting
        if ($request->filled('sort')) {
            $sort = $request->input('sort');
            if ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } elseif ($sort === 'amount_high') {
                $query->orderBy('total_amount', 'desc');
            } elseif ($sort === 'amount_low') {
                $query->orderBy('total_amount', 'asc');
            } elseif ($sort === 'due_date_asc') {
                $query->orderBy('due_date', 'asc');
            } elseif ($sort === 'due_date_desc') {
                $query->orderBy('due_date', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Check if the user is authorized to manage the order.
     *
     * @param Order $order
     * @return bool
     */
    public function isAuthorized(Order $order): bool
    {
        return $order->user_id === auth()->id() && $order->isStoreOrder();
    }

    /**
     * Get the currency symbol for the store owner.
     *
     * @param int $userId
     * @return string
     */
    public function getCurrencySymbol(int $userId): string
    {
        $user = User::find($userId);
        return $user ? $user->getCurrencySymbol() : '$';
    }

    /**
     * Update an order with the given data.
     *
     * @param Order $order
     * @param array $data
     * @return bool
     */
    public function updateOrder(Order $order, array $data): bool
    {
        // If the status is being set to cancelled, also set the payment status to cancelled
        if ($data['status'] === 'cancelled') {
            $data['payment_status'] = 'cancelled';
        }

        return $order->update($data);
    }

    /**
     * Mark an order as paid.
     *
     * @param Order $order
     * @return bool
     */
    public function markOrderAsPaid(Order $order): bool
    {
        return $order->update(['payment_status' => 'paid']);
    }

    /**
     * Cancel an order.
     *
     * @param Order $order
     * @return array
     */
    public function cancelOrder(Order $order): array
    {
        // Only allow cancellation of orders that are not already delivered or cancelled
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return [
                'success' => false,
                'message' => 'Cannot cancel an order that is already delivered or cancelled.'
            ];
        }

        $updated = $order->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled'
        ]);

        return [
            'success' => $updated,
            'message' => $updated ? 'Order has been cancelled successfully.' : 'Failed to cancel the order.'
        ];
    }
}
