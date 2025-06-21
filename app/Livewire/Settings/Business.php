<?php

namespace App\Livewire\Settings;

use App\Models\BusinessDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
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
    public $facebookHandle;
    public $instagramHandle;
    public $tiktokHandle;
    public $whatsappHandle;

    protected $rules = [
        'businessName' => 'required|min:2',
        'businessAddress' => 'required',
        'businessPhone' => 'required',
        'businessEmail' => 'required|email',
        'newLogo' => 'nullable|image|max:1024', // 1MB max
        'facebookHandle' => 'nullable|string|max:255',
        'instagramHandle' => 'nullable|string|max:255',
        'tiktokHandle' => 'nullable|string|max:255',
        'whatsappHandle' => 'nullable|string|max:255',
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
            $this->facebookHandle = $this->businessDetail->facebook_handle;
            $this->instagramHandle = $this->businessDetail->instagram_handle;
            $this->tiktokHandle = $this->businessDetail->tiktok_handle;
            $this->whatsappHandle = $this->businessDetail->whatsapp_handle;
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
            'facebook_handle' => $this->facebookHandle,
            'instagram_handle' => $this->instagramHandle,
            'tiktok_handle' => $this->tiktokHandle,
            'whatsapp_handle' => $this->whatsappHandle,
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
        $this->dispatch('alert', ['status'=>'success','message'=>$this->successMessage]);
    }

    public function render()
    {
        return view('livewire.settings.business');
    }
}
