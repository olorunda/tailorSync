<?php

namespace App\Livewire\Settings;

use App\Models\BusinessDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BusinessProfile extends Component
{
    public $businessName;
    public $businessAddress;
    public $businessPhone;
    public $businessEmail;
    public $logo;
    public $businessDetail;
    public $bookingUrl;
    public $businessProfileUrl;
    public $facebookHandle;
    public $instagramHandle;
    public $tiktokHandle;
    public $whatsappHandle;

    public function mount()
    {
        $user = Auth::user();

        // Only parent users (with parent_id = null) can access this page
        if ($user->parent_id !== null) {
            return redirect()->route('dashboard');
        }

        // Get business details
        $this->businessDetail = $user->businessDetail;

        if ($this->businessDetail) {
            $this->businessName = $this->businessDetail->business_name;
            $this->businessAddress = $this->businessDetail->business_address;
            $this->businessPhone = $this->businessDetail->business_phone;
            $this->businessEmail = $this->businessDetail->business_email;
            $this->logo = $this->businessDetail->logo_path;
            $this->facebookHandle = $this->businessDetail->facebook_handle;
            $this->instagramHandle = $this->businessDetail->instagram_handle;
            $this->tiktokHandle = $this->businessDetail->tiktok_handle;
            $this->whatsappHandle = $this->businessDetail->whatsapp_handle;
        }

        // Get booking URL
        $this->bookingUrl = $user->getBookingUrl() ?? '';

        // Get business profile URL
        $this->businessProfileUrl = $user->getBusinessProfileUrl() ?? '';
    }

    public function render()
    {
        return view('livewire.settings.business-profile');
    }
}
