<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class PublicBusinessProfile extends Component
{
    public $businessName;
    public $businessAddress;
    public $businessPhone;
    public $businessEmail;
    public $logo;
    public $bookingUrl;
    public $user;

    public function mount($slug)
    {
        // Extract user ID from slug (format: business-name_userId)
        $parts = explode('_', $slug);
        $userId = end($parts);

        // Find the user by ID
        $this->user = User::find($userId);

        if (!$this->user) {
            abort(404, 'Business not found');
        }

        // Get business details
        $businessDetail = $this->user->businessDetail;

        if ($businessDetail) {
            $this->businessName = $businessDetail->business_name;
            $this->businessAddress = $businessDetail->business_address;
            $this->businessPhone = $businessDetail->business_phone;
            $this->businessEmail = $businessDetail->business_email;
            $this->logo = $businessDetail->logo_path;
        }

        // Get booking URL
        $this->bookingUrl = $this->user->getBookingUrl() ?? '';
    }

    public function render()
    {
        return view('livewire.public-business-profile')
            ->layout('components.layouts.guest');
    }
}
