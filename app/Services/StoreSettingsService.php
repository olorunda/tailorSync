<?php

namespace App\Services;

use App\Models\BusinessDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreSettingsService
{
    /**
     * Get the business details for the authenticated user.
     *
     * @return BusinessDetail|null
     */
    public function getBusinessDetail(): ?BusinessDetail
    {
        return BusinessDetail::where('user_id', auth()->id())->first();
    }

    /**
     * Update the store settings.
     *
     * @param BusinessDetail $businessDetail
     * @param array $data
     * @param Request $request
     * @return bool
     */
    public function updateStoreSettings(BusinessDetail $businessDetail, array $data, Request $request): bool
    {
        // Process store banner image
        if ($request->hasFile('store_banner_image')) {
            // Delete old banner if exists
            if ($businessDetail->store_banner_image) {
                Storage::disk('public')->delete($businessDetail->store_banner_image);
            }

            $data['store_banner_image'] = $request->file('store_banner_image')
                ->store('store-images', 'public');
        }

        // Generate slug if not provided
        if (empty($data['store_slug'])) {
            $data['store_slug'] = Str::slug($businessDetail->business_name) . '-' . Str::random(5);
        }

        // Update business details with store settings
        return $businessDetail->update($data);
    }

    /**
     * Get featured products for the authenticated user.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedProducts(int $limit = 8)
    {
        return Product::where('user_id', auth()->id())
            ->where('is_featured', true)
            ->where('is_active', true)
            ->take($limit)
            ->get();
    }

    /**
     * Get new arrivals for the authenticated user.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNewArrivals(int $limit = 8)
    {
        return Product::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get custom designs for the authenticated user.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomDesigns(int $limit = 8)
    {
        return Product::where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('is_custom_order', true)
            ->take($limit)
            ->get();
    }
}
