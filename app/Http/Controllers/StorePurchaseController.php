<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Services\StorePurchaseService;
use Illuminate\Http\Request;

class StorePurchaseController extends Controller
{
    /**
     * The store purchase service instance.
     *
     * @var \App\Services\StorePurchaseService
     */
    protected $storePurchaseService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\StorePurchaseService $storePurchaseService
     * @return void
     */
    public function __construct(StorePurchaseService $storePurchaseService)
    {
        $this->storePurchaseService = $storePurchaseService;
    }

    /**
     * Display a listing of the store purchases.
     */
    public function index(Request $request)
    {
        $purchases = $this->storePurchaseService->getFilteredPurchases($request);

        return view('store.purchases.index', compact('purchases'));
    }

    /**
     * Display the specified store purchase.
     */
    public function show(Purchase $purchase)
    {
        if (!$this->storePurchaseService->isAuthorized($purchase)) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified store purchase.
     */
    public function edit(Purchase $purchase)
    {
        if (!$this->storePurchaseService->isAuthorized($purchase)) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.purchases.edit', compact('purchase'));
    }

    /**
     * Update the specified store purchase in storage.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        if (!$this->storePurchaseService->isAuthorized($purchase)) {
            abort(403, 'Unauthorized action.');
        }

        $this->storePurchaseService->updatePurchase($purchase, $request->validated());

        return redirect()->route('store.purchases.show', $purchase)
            ->with('success', 'Store purchase updated successfully.');
    }
}
