<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStoreSettingsRequest;
use App\Services\StoreSettingsService;
use Illuminate\Http\Request;

class StoreSettingsController extends Controller
{
    /**
     * The store settings service instance.
     *
     * @var \App\Services\StoreSettingsService
     */
    protected $storeSettingsService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\StoreSettingsService $storeSettingsService
     * @return void
     */
    public function __construct(StoreSettingsService $storeSettingsService)
    {
        $this->storeSettingsService = $storeSettingsService;
    }

    /**
     * Display the store settings form.
     */
    public function index()
    {
        $businessDetail = $this->storeSettingsService->getBusinessDetail();

        if (!$businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        return view('store.settings', compact('businessDetail'));
    }

    /**
     * Update the store settings.
     */
    public function update(UpdateStoreSettingsRequest $request)
    {
        $businessDetail = $this->storeSettingsService->getBusinessDetail();

        if (!$businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        $this->storeSettingsService->updateStoreSettings($businessDetail, $request->validated(), $request);

        return redirect()->route('store.settings')
            ->with('success', 'Store settings updated successfully.');
    }

    /**
     * Preview the store with current settings.
     */
    public function preview()
    {
        $businessDetail = $this->storeSettingsService->getBusinessDetail();

        if (!$businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        // Get featured products
        $featuredProducts = [];
        if ($businessDetail->store_show_featured_products) {
            $featuredProducts = $this->storeSettingsService->getFeaturedProducts();
        }

        // Get new arrivals
        $newArrivals = [];
        if ($businessDetail->store_show_new_arrivals) {
            $newArrivals = $this->storeSettingsService->getNewArrivals();
        }

        // Get custom designs
        $customDesigns = [];
        if ($businessDetail->store_show_custom_designs) {
            $customDesigns = $this->storeSettingsService->getCustomDesigns();
        }

        return view('store.preview', compact(
            'businessDetail',
            'featuredProducts',
            'newArrivals',
            'customDesigns'
        ));
    }
}
