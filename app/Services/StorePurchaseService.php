<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Http\Request;

class StorePurchaseService
{
    /**
     * Get filtered and paginated store purchases for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredPurchases(Request $request)
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

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Check if the user is authorized to manage the purchase.
     *
     * @param Purchase $purchase
     * @return bool
     */
    public function isAuthorized(Purchase $purchase): bool
    {
        return $purchase->user_id === auth()->id();
    }

    /**
     * Update a purchase with the given data.
     *
     * @param Purchase $purchase
     * @param array $data
     * @return bool
     */
    public function updatePurchase(Purchase $purchase, array $data): bool
    {
        return $purchase->update($data);
    }
}
