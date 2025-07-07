<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\StoreOrderService;
use Illuminate\Http\Request;

class StoreOrderController extends Controller
{
    /**
     * The store order service instance.
     *
     * @var \App\Services\StoreOrderService
     */
    protected $storeOrderService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\StoreOrderService $storeOrderService
     * @return void
     */
    public function __construct(StoreOrderService $storeOrderService)
    {
        $this->storeOrderService = $storeOrderService;
    }

    /**
     * Display a listing of the store orders.
     */
    public function index(Request $request)
    {
        $orders = $this->storeOrderService->getFilteredOrders($request);
        $currencySymbol = $this->storeOrderService->getCurrencySymbol(auth()->id());

        return view('store.orders.index', compact('orders', 'currencySymbol'));
    }

    /**
     * Display the specified store order.
     */
    public function show(Order $order)
    {
        if (!$this->storeOrderService->isAuthorized($order)) {
            abort(403, 'Unauthorized action.');
        }

        $currencySymbol = $this->storeOrderService->getCurrencySymbol($order->user_id);

        return view('store.orders.show', compact('order', 'currencySymbol'));
    }

    /**
     * Show the form for editing the specified store order.
     */
    public function edit(Order $order)
    {
        if (!$this->storeOrderService->isAuthorized($order)) {
            abort(403, 'Unauthorized action.');
        }

        $currencySymbol = $this->storeOrderService->getCurrencySymbol($order->user_id);

        return view('store.orders.edit', compact('order', 'currencySymbol'));
    }

    /**
     * Update the specified store order in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        if (!$this->storeOrderService->isAuthorized($order)) {
            abort(403, 'Unauthorized action.');
        }

        $this->storeOrderService->updateOrder($order, $request->validated());

        return redirect()->route('store.orders.show', $order)
            ->with('success', 'Store order updated successfully.');
    }

    /**
     * Mark the specified store order as paid.
     */
    public function markAsPaid(Order $order)
    {
        if (!$this->storeOrderService->isAuthorized($order)) {
            abort(403, 'Unauthorized action.');
        }

        $this->storeOrderService->markOrderAsPaid($order);

        return redirect()->route('store.orders.show', $order)
            ->with('success', 'Order has been marked as paid.');
    }

    /**
     * Cancel the specified store order.
     */
    public function cancelOrder(Order $order)
    {
        if (!$this->storeOrderService->isAuthorized($order)) {
            abort(403, 'Unauthorized action.');
        }

        $result = $this->storeOrderService->cancelOrder($order);

        if (!$result['success']) {
            return redirect()->route('store.orders.show', $order)
                ->with('error', $result['message']);
        }

        return redirect()->route('store.orders.show', $order)
            ->with('success', $result['message']);
    }
}
