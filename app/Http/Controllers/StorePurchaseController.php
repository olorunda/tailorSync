<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class StorePurchaseController extends Controller
{
    /**
     * Display a listing of the store purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::where('user_id', auth()->id());

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
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

        // Apply payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
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
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $purchases = $query->paginate(10)->withQueryString();

        return view('store.purchases.index', compact('purchases'));
    }

    /**
     * Display the specified store purchase.
     */
    public function show(Purchase $purchase)
    {
        if ($purchase->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified store purchase.
     */
    public function edit(Purchase $purchase)
    {
        if ($purchase->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.purchases.edit', compact('purchase'));
    }

    /**
     * Update the specified store purchase in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|string|in:pending,paid,refunded,partially_paid',
            'shipping_method' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $purchase->update($validated);

        return redirect()->route('store.purchases.show', $purchase)
            ->with('success', 'Store purchase updated successfully.');
    }
}
