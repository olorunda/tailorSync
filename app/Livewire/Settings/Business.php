<?php

namespace App\Livewire\Settings;

use App\Models\BusinessDetail;
use Illuminate\Support\Carbon;
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

    // Appointment settings
    public $businessHoursStart;
    public $businessHoursEnd;
    public $availableDays = [];

    // Permission check
    public $canManageTaxSettings = false;
    public $canManagePaymentSettings = false;

    // Tax settings
    public $taxCountry = 'none';
    public $taxEnabled = false;
    public $taxNumber;
    public $taxSettings = [];

    // Payment settings
    public $paymentEnabled = false;
    public $defaultPaymentGateway = 'none';
    public $paymentSettings = [];

    // Paystack settings
    public $paystackPublicKey;
    public $paystackSecretKey;

    // Flutterwave settings
    public $flutterwavePublicKey;
    public $flutterwaveSecretKey;

    // Stripe settings
    public $stripePublicKey;
    public $stripeSecretKey;

    // Canadian tax settings
    public $canadaProvince;
    public $canadaGstRate = 5;
    public $canadaPstRate = 0;
    public $canadaHstRate = 0;

    // US tax settings
    public $usState;
    public $usStateRate = 0;
    public $usLocalRate = 0;

    // UK tax settings
    public $ukVatRate = 20;

    // Nigeria tax settings
    public $nigeriaVatRate = 7.5;

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
        'businessHoursStart' => 'nullable|date_format:H:i',
        'businessHoursEnd' => 'nullable|date_format:H:i|after:businessHoursStart',
        'availableDays' => 'nullable|array',
        'availableDays.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',

        // Tax settings
        'taxCountry' => 'required|string|in:none,canada,us,uk,nigeria',
        'taxEnabled' => 'boolean',
        'taxNumber' => 'nullable|string|max:255',

        // Canadian tax settings
        'canadaProvince' => 'nullable|string|required_if:taxCountry,canada',
        'canadaGstRate' => 'nullable|numeric|min:0|max:100|required_if:taxCountry,canada',
        'canadaPstRate' => 'nullable|numeric|min:0|max:100',
        'canadaHstRate' => 'nullable|numeric|min:0|max:100',

        // US tax settings
        'usState' => 'nullable|string|required_if:taxCountry,us',
        'usStateRate' => 'nullable|numeric|min:0|max:100|required_if:taxCountry,us',
        'usLocalRate' => 'nullable|numeric|min:0|max:100',

        // UK tax settings
        'ukVatRate' => 'nullable|numeric|min:0|max:100|required_if:taxCountry,uk',

        // Nigeria tax settings
        'nigeriaVatRate' => 'nullable|numeric|min:0|max:100|required_if:taxCountry,nigeria',

        // Payment settings
        'paymentEnabled' => 'boolean',
        'defaultPaymentGateway' => 'required|string|in:none,paystack,flutterwave,stripe',

        // Paystack settings
        'paystackPublicKey' => 'nullable|string|required_if:defaultPaymentGateway,paystack',
        'paystackSecretKey' => 'nullable|string|required_if:defaultPaymentGateway,paystack',

        // Flutterwave settings
        'flutterwavePublicKey' => 'nullable|string|required_if:defaultPaymentGateway,flutterwave',
        'flutterwaveSecretKey' => 'nullable|string|required_if:defaultPaymentGateway,flutterwave',

        // Stripe settings
        'stripePublicKey' => 'nullable|string|required_if:defaultPaymentGateway,stripe',
        'stripeSecretKey' => 'nullable|string|required_if:defaultPaymentGateway,stripe',
    ];

    public function mount()
    {
        $user = Auth::user();

        // Only parent users (with parent_id = null) can access this page
        if ($user->parent_id !== null) {
            return redirect()->route('dashboard');
        }

        // Check if user has permission to manage tax settings
        $this->canManageTaxSettings = $user->hasPermission('manage_tax_settings');

        // Check if user has permission to manage payment settings
        $this->canManagePaymentSettings = $user->hasPermission('manage_payment_settings');

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

            // Load appointment settings
            if ($this->businessDetail->business_hours_start) {
                $this->businessHoursStart = Carbon::parse($this->businessDetail->business_hours_start)->format('H:i');
            }
            if ($this->businessDetail->business_hours_end) {
                $this->businessHoursEnd = Carbon::parse($this->businessDetail->business_hours_end)->format('H:i');
            }
            $this->availableDays = $this->businessDetail->available_days ?? [];

            // Load tax settings
            $this->taxCountry = $this->businessDetail->tax_country ?? 'none';
            $this->taxEnabled = $this->businessDetail->tax_enabled ?? false;
            $this->taxNumber = $this->businessDetail->tax_number;
            $this->taxSettings = $this->businessDetail->tax_settings ?? [];

            // Load country-specific tax settings
            if ($this->taxCountry === 'canada' && isset($this->taxSettings['province'])) {
                $this->canadaProvince = $this->taxSettings['province'];
                $this->canadaGstRate = $this->taxSettings['gst_rate'] ?? 5;
                $this->canadaPstRate = $this->taxSettings['pst_rate'] ?? 0;
                $this->canadaHstRate = $this->taxSettings['hst_rate'] ?? 0;
            } elseif ($this->taxCountry === 'us' && isset($this->taxSettings['state'])) {
                $this->usState = $this->taxSettings['state'];
                $this->usStateRate = $this->taxSettings['state_rate'] ?? 0;
                $this->usLocalRate = $this->taxSettings['local_rate'] ?? 0;
            } elseif ($this->taxCountry === 'uk') {
                $this->ukVatRate = $this->taxSettings['vat_rate'] ?? 20;
            } elseif ($this->taxCountry === 'nigeria') {
                $this->nigeriaVatRate = $this->taxSettings['vat_rate'] ?? 7.5;
            }

            // Load payment settings
            $this->paymentEnabled = $this->businessDetail->payment_enabled ?? false;
            $this->defaultPaymentGateway = $this->businessDetail->default_payment_gateway ?? 'none';
            $this->paymentSettings = $this->businessDetail->payment_settings ?? [];

            // Load gateway-specific settings
            if ($this->defaultPaymentGateway === 'paystack' && isset($this->paymentSettings['paystack'])) {
                $this->paystackPublicKey = $this->paymentSettings['paystack']['public_key'] ?? '';
                $this->paystackSecretKey = $this->paymentSettings['paystack']['secret_key'] ?? '';
            } elseif ($this->defaultPaymentGateway === 'flutterwave' && isset($this->paymentSettings['flutterwave'])) {
                $this->flutterwavePublicKey = $this->paymentSettings['flutterwave']['public_key'] ?? '';
                $this->flutterwaveSecretKey = $this->paymentSettings['flutterwave']['secret_key'] ?? '';
            } elseif ($this->defaultPaymentGateway === 'stripe' && isset($this->paymentSettings['stripe'])) {
                $this->stripePublicKey = $this->paymentSettings['stripe']['public_key'] ?? '';
                $this->stripeSecretKey = $this->paymentSettings['stripe']['secret_key'] ?? '';
            }
        }
    }

    public function updateBusinessDetails()
    {
        $this->validate();

        $user = Auth::user();

        // Prepare tax settings based on selected country
        $taxSettings = [];
        if ($this->taxCountry === 'canada') {
            $taxSettings = [
                'province' => $this->canadaProvince,
                'gst_rate' => $this->canadaGstRate,
                'pst_rate' => $this->canadaPstRate,
                'hst_rate' => $this->canadaHstRate,
            ];
        } elseif ($this->taxCountry === 'us') {
            $taxSettings = [
                'state' => $this->usState,
                'state_rate' => $this->usStateRate,
                'local_rate' => $this->usLocalRate,
            ];
        } elseif ($this->taxCountry === 'uk') {
            $taxSettings = [
                'vat_rate' => $this->ukVatRate,
            ];
        } elseif ($this->taxCountry === 'nigeria') {
            $taxSettings = [
                'vat_rate' => $this->nigeriaVatRate,
            ];
        }

        // Prepare payment settings based on selected gateway
        $paymentSettings = [];
        if ($this->defaultPaymentGateway === 'paystack') {
            $paymentSettings['paystack'] = [
                'public_key' => $this->paystackPublicKey,
                'secret_key' => $this->paystackSecretKey,
            ];
        } elseif ($this->defaultPaymentGateway === 'flutterwave') {
            $paymentSettings['flutterwave'] = [
                'public_key' => $this->flutterwavePublicKey,
                'secret_key' => $this->flutterwaveSecretKey,
            ];
        } elseif ($this->defaultPaymentGateway === 'stripe') {
            $paymentSettings['stripe'] = [
                'public_key' => $this->stripePublicKey,
                'secret_key' => $this->stripeSecretKey,
            ];
        }

        $data = [
            'business_name' => $this->businessName,
            'business_address' => $this->businessAddress,
            'business_phone' => $this->businessPhone,
            'business_email' => $this->businessEmail,
            'facebook_handle' => $this->facebookHandle,
            'instagram_handle' => $this->instagramHandle,
            'tiktok_handle' => $this->tiktokHandle,
            'whatsapp_handle' => $this->whatsappHandle,
            'business_hours_start' => $this->businessHoursStart,
            'business_hours_end' => $this->businessHoursEnd,
            'available_days' => $this->availableDays,
            'tax_country' => $this->taxCountry,
            'tax_enabled' => $this->taxEnabled,
            'tax_number' => $this->taxNumber,
            'tax_settings' => $taxSettings,
            'payment_enabled' => $this->paymentEnabled,
            'default_payment_gateway' => $this->defaultPaymentGateway,
            'payment_settings' => $paymentSettings,
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

    public function setTaxCountry($country)
    {
        $this->taxCountry=$country;

    }

    public function setProvince($province)
    {
        $this->canadaProvince=$province;
    }

    public function setPaymentGateway($gateway)
    {
        $this->defaultPaymentGateway = $gateway;
    }

    public function render()
    {
        return view('livewire.settings.business');
    }
}
