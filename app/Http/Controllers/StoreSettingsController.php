<?php

namespace App\Http\Controllers;

use App\Models\BusinessDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreSettingsController extends Controller
{
    /**
     * Display the store settings form.
     */
    public function index()
    {
        $businessDetail = BusinessDetail::where('user_id', auth()->id())->first();

        if (!$businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        return view('store.settings', compact('businessDetail'));
    }

    /**
     * Update the store settings.
     */
    public function update(Request $request)
    {
        $businessDetail = BusinessDetail::where('user_id', auth()->id())->first();

        if (!$businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        $validated = $request->validate([
            'store_enabled' => 'boolean',
            'store_slug' => 'nullable|string|max:100|unique:business_details,store_slug,' . $businessDetail->id,
            'store_theme_color' => 'nullable|string|max:20',
            'store_secondary_color' => 'nullable|string|max:20',
            'store_accent_color' => 'nullable|string|max:20',
            'store_description' => 'nullable|string|max:1000',
            'store_banner_image' => 'nullable|image|max:2048',
            'store_featured_categories' => 'nullable|array',
            'store_featured_categories.*' => 'nullable|string|max:100',
            'store_social_links' => 'nullable|array',
            'store_social_links.*' => 'nullable|url',
            'store_announcement' => 'nullable|string|max:500',
            'store_show_featured_products' => 'boolean',
            'store_show_new_arrivals' => 'boolean',
            'store_show_custom_designs' => 'boolean',
        ]);

        // Process store banner image
        if ($request->hasFile('store_banner_image')) {
            // Delete old banner if exists
            if ($businessDetail->store_banner_image) {
                Storage::disk('public')->delete($businessDetail->store_banner_image);
            }

            $validated['store_banner_image'] = $request->file('store_banner_image')
                ->store('store-images', 'public');
        }

        // Generate slug if not provided
        if (empty($validated['store_slug'])) {
            $validated['store_slug'] = Str::slug($businessDetail->business_name) . '-' . Str::random(5);
        }

        // Update business details with store settings
        $businessDetail->update($validated);

        return redirect()->route('store.settings')
            ->with('success', 'Store settings updated successfully.');
    }

    /**
     * Preview the store with current settings.
     */
    public function preview()
    {
        $businessDetail = BusinessDetail::where('user_id', auth()->id())->first();

        if (!$businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        // Get featured products
        $featuredProducts = [];
        if ($businessDetail->store_show_featured_products) {
            $featuredProducts = \App\Models\Product::where('user_id', auth()->id())
                ->where('is_featured', true)
                ->where('is_active', true)
                ->take(8)
                ->get();
        }

        // Get new arrivals
        $newArrivals = [];
        if ($businessDetail->store_show_new_arrivals) {
            $newArrivals = \App\Models\Product::where('user_id', auth()->id())
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();
        }

        // Get custom designs
        $customDesigns = [];
        if ($businessDetail->store_show_custom_designs) {
            $customDesigns = \App\Models\Product::where('user_id', auth()->id())
                ->where('is_active', true)
                ->where('is_custom_order', true)
                ->take(8)
                ->get();
        }

        return view('store.preview', compact(
            'businessDetail',
            'featuredProducts',
            'newArrivals',
            'customDesigns'
        ));
    }
}
