<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class StoreOrderController extends Controller
{
    /**
     * Display a listing of the store orders.
     */
    public function index(Request $request)
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

        $orders = $query->paginate(10)->withQueryString();

        return view('store.orders.index', compact('orders'));
    }

    /**
     * Display the specified store order.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id() || !$order->isStoreOrder()) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified store order.
     */
    public function edit(Order $order)
    {
        if ($order->user_id !== auth()->id() || !$order->isStoreOrder()) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.orders.edit', compact('order'));
    }

    /**
     * Update the specified store order in storage.
     */
    public function update(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id() || !$order->isStoreOrder()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|string|in:pending,paid,refunded,partially_paid,cancelled',
            'shipping_method' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // If the status is being set to cancelled, also set the payment status to cancelled
        if ($validated['status'] === 'cancelled') {
            $validated['payment_status'] = 'cancelled';
        }

        $order->update($validated);

        return redirect()->route('store.orders.show', $order)
            ->with('success', 'Store order updated successfully.');
    }

    /**
     * Mark the specified store order as paid.
     */
    public function markAsPaid(Order $order)
    {
        if (!$order->isStoreOrder()) {
            abort(403, 'Unauthorized action.');
        }

        $order->update(['payment_status' => 'paid']);

        return redirect()->route('store.orders.show', $order)
            ->with('success', 'Order has been marked as paid.');
    }

    /**
     * Cancel the specified store order.
     */
    public function cancelOrder(Order $order)
    {
        if ($order->user_id !== auth()->id() || !$order->isStoreOrder()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow cancellation of orders that are not already delivered or cancelled
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return redirect()->route('store.orders.show', $order)
                ->with('error', 'Cannot cancel an order that is already delivered or cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled'
        ]);

        return redirect()->route('store.orders.show', $order)
            ->with('success', 'Order has been cancelled successfully.');
    }
}
