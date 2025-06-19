<?php

namespace App\Livewire\Settings;

use App\Models\BusinessDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Business extends Component
{
    use WithFileUploads;

    public $businessName;
    public $businessAddress;
    public $businessPhone;
    public $businessEmail;
    public $logo;
    public $newLogo;
    public $businessDetail;
    public $successMessage = '';

    protected $rules = [
        'businessName' => 'required|min:2',
        'businessAddress' => 'required',
        'businessPhone' => 'required',
        'businessEmail' => 'required|email',
        'newLogo' => 'nullable|image|max:1024', // 1MB max
    ];

    public function mount()
    {
        $user = Auth::user();

        // Only parent users (with parent_id = null) can access this page
        if ($user->parent_id !== null) {
            return redirect()->route('dashboard');
        }

        // Get or create business details
        $this->businessDetail = $user->businessDetail;

        if ($this->businessDetail) {
            $this->businessName = $this->businessDetail->business_name;
            $this->businessAddress = $this->businessDetail->business_address;
            $this->businessPhone = $this->businessDetail->business_phone;
            $this->businessEmail = $this->businessDetail->business_email;
            $this->logo = $this->businessDetail->logo_path;
        }
    }

    public function updateBusinessDetails()
    {
        $this->validate();

        $user = Auth::user();

        $data = [
            'business_name' => $this->businessName,
            'business_address' => $this->businessAddress,
            'business_phone' => $this->businessPhone,
            'business_email' => $this->businessEmail,
        ];

        // Handle logo upload if a new logo is provided
        if ($this->newLogo) {
            $data['logo_path'] = $this->newLogo->store('logos', 'public');
            $this->logo = $data['logo_path'];
            $this->newLogo = null;
        }

        // Update or create business details
        if ($this->businessDetail) {
            $this->businessDetail->update($data);
        } else {
            $this->businessDetail = $user->businessDetail()->create($data);
        }

        $this->successMessage = 'Business details updated successfully!';
    }

    public function render()
    {
        return view('livewire.settings.business');
    }
}
