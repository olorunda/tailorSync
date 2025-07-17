<?php

namespace App\Livewire\Settings;

use App\Models\BusinessDetail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Store extends Component
{
    use WithFileUploads;

    public $businessDetail;
    public $storeEnabled = false;
    public $storeSlug;
    public $storeDescription;
    public $storeAnnouncement;
    public $newBannerImage;
    public $storeThemeColor;
    public $storeSecondaryColor;
    public $storeAccentColor;
    public $storeShowFeaturedProducts = false;
    public $storeShowNewArrivals = false;
    public $storeShowCustomDesigns = false;
    public $successMessage = '';

    public function mount()
    {
        $this->businessDetail = BusinessDetail::where('user_id', auth()->id())->first();

        if (!$this->businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        $this->storeEnabled = $this->businessDetail->store_enabled;
        $this->storeSlug = $this->businessDetail->store_slug;
        $this->storeDescription = $this->businessDetail->store_description;
        $this->storeAnnouncement = $this->businessDetail->store_announcement;
        $this->storeThemeColor = $this->businessDetail->store_theme_color ?? '#3b82f6';
        $this->storeSecondaryColor = $this->businessDetail->store_secondary_color ?? '#1e40af';
        $this->storeAccentColor = $this->businessDetail->store_accent_color ?? '#f97316';
        $this->storeShowFeaturedProducts = $this->businessDetail->store_show_featured_products;
        $this->storeShowNewArrivals = $this->businessDetail->store_show_new_arrivals;
        $this->storeShowCustomDesigns = $this->businessDetail->store_show_custom_designs;
    }

    public function updateStoreSettings()
    {
        $this->validate([
            'storeEnabled' => 'boolean',
            'storeSlug' => 'nullable|string|max:100|unique:business_details,store_slug,' . $this->businessDetail->id,
            'storeThemeColor' => 'nullable|string|max:20',
            'storeSecondaryColor' => 'nullable|string|max:20',
            'storeAccentColor' => 'nullable|string|max:20',
            'storeDescription' => 'nullable|string|max:1000',
            'newBannerImage' => 'nullable|image|max:10000',
            'storeAnnouncement' => 'nullable|string|max:500',
            'storeShowFeaturedProducts' => 'boolean',
            'storeShowNewArrivals' => 'boolean',
            'storeShowCustomDesigns' => 'boolean',
        ]);

        // Process store banner image
        if ($this->newBannerImage) {
            // Delete old banner if exists
            if ($this->businessDetail->store_banner_image) {
                Storage::disk('public')->delete($this->businessDetail->store_banner_image);
            }

            $bannerPath = $this->newBannerImage->store('store-images', 'public');
        }

        // Generate slug if not provided
        if (empty($this->storeSlug)) {
            $this->storeSlug = Str::slug($this->businessDetail->business_name) . '-' . Str::random(5);
        }

        // Update business details with store settings
        $this->businessDetail->update([
            'store_enabled' => $this->storeEnabled,
            'store_slug' => $this->storeSlug,
            'store_theme_color' => $this->storeThemeColor,
            'store_secondary_color' => $this->storeSecondaryColor,
            'store_accent_color' => $this->storeAccentColor,
            'store_description' => $this->storeDescription,
            'store_banner_image' => $this->newBannerImage ? $bannerPath : $this->businessDetail->store_banner_image,
            'store_announcement' => $this->storeAnnouncement,
            'store_show_featured_products' => $this->storeShowFeaturedProducts,
            'store_show_new_arrivals' => $this->storeShowNewArrivals,
            'store_show_custom_designs' => $this->storeShowCustomDesigns,
        ]);

        $this->successMessage = 'Store settings updated successfully.';
        $this->reset('newBannerImage');
    }

    public function render()
    {
        return view('livewire.settings.store');
    }
}
