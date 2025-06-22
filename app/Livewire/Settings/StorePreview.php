<?php

namespace App\Livewire\Settings;

use App\Models\BusinessDetail;
use App\Models\Product;
use Livewire\Component;

class StorePreview extends Component
{
    public $businessDetail;
    public $featuredProducts;
    public $newArrivals;
    public $customDesigns;

    public function mount()
    {
        $this->businessDetail = BusinessDetail::where('user_id', auth()->id())->first();

        if (!$this->businessDetail) {
            return redirect()->route('settings.business')
                ->with('error', 'Please set up your business details first.');
        }

        // Get featured products
        $this->featuredProducts = [];
        if ($this->businessDetail->store_show_featured_products) {
            $this->featuredProducts = Product::where('user_id', auth()->id())
                ->where('is_featured', true)
                ->where('is_active', true)
                ->take(8)
                ->get();
        }

        // Get new arrivals
        $this->newArrivals = [];
        if ($this->businessDetail->store_show_new_arrivals) {
            $this->newArrivals = Product::where('user_id', auth()->id())
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();
        }

        // Get custom designs
        $this->customDesigns = [];
        if ($this->businessDetail->store_show_custom_designs) {
            $this->customDesigns = Product::where('user_id', auth()->id())
                ->where('is_active', true)
                ->where('is_custom_order', true)
                ->take(8)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.settings.store-preview');
    }
}
