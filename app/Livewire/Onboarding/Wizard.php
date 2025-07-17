<?php

namespace App\Livewire\Onboarding;

use App\Models\User;
use App\Models\Client;
use App\Notifications\TeamMemberInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Wizard extends Component
{
    use WithFileUploads;

    // Current step of the wizard
    public $currentStep = 1;

    // Total number of steps
    public $totalSteps = 7;

    // Form data
    public $businessName = '';
    public $businessAddress = '';
    public $businessPhone = '';
    public $businessEmail = '';
    public $logo = null;
    public $teamMemberName = '';
    public $teamMemberEmail = '';
    public $teamMembers = [];

    // Appointment settings
    public $businessHoursStart = '09:00';
    public $businessHoursEnd = '17:00';
    public $availableDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    // Client import data
    public $csvFile = null;
    public $importedCount = 0;
    public $errorCount = 0;
    public $importErrors = [];
    public $processing = false;

    // Validation rules
    protected $rules = [
        'businessName' => 'required|min:2',
        'businessAddress' => 'required',
        'businessPhone' => 'required',
        'businessEmail' => 'required|email',
        'logo' => 'nullable|image|max:10000', // 1MB max
        'businessHoursStart' => 'nullable|date_format:H:i',
        'businessHoursEnd' => 'nullable|date_format:H:i|after:businessHoursStart',
        'availableDays' => 'nullable|array',
        'availableDays.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
    ];

    /**
     * Go to the next step
     */
    public function nextStep()
    {
        if ($this->currentStep === 2) {
            $this->validate([
                'businessName' => 'required|min:2',
                'businessAddress' => 'required',
                'businessPhone' => 'required',
                'businessEmail' => 'required|email',
            ]);
        }

        if ($this->currentStep === 3 && $this->logo) {
            $this->validate([
                'logo' => 'image|max:10000',
            ]);
        }

        if ($this->currentStep === 4) {
            $this->validate([
                'businessHoursStart' => 'nullable|date_format:H:i',
                'businessHoursEnd' => 'nullable|date_format:H:i|after:businessHoursStart',
                'availableDays' => 'nullable|array',
                'availableDays.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            ]);
        }

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    /**
     * Import clients from CSV file
     */
    public function importClients()
    {
        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
        ]);

        $this->processing = true;
        $this->importedCount = 0;
        $this->errorCount = 0;
        $this->importErrors = [];

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');

        // Get headers
        $headers = fgetcsv($file);
        $headers = array_map('strtolower', $headers);
        $requiredHeaders = ['name'];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (!empty($missingHeaders)) {
            $this->processing = false;
            $this->addError('csvFile', 'CSV file is missing required headers: ' . implode(', ', $missingHeaders));
            return;
        }

        // Process rows
        $row = 2; // Start from row 2 (after header)
        while (($data = fgetcsv($file)) !== false) {
            $rowData = array_combine($headers, count($headers) === count($data) ? $data : array_pad($data, count($headers), null));

            // Validate required fields
            if (empty($rowData['name'])) {
                $this->importErrors[] = "Row {$row}: Name is required";
                $this->errorCount++;
                $row++;
                continue;
            }

            try {
                Client::create([
                    'user_id' => Auth::id(),
                    'name' => $rowData['name'],
                    'email' => $rowData['email'] ?? null,
                    'phone' => $rowData['phone'] ?? null,
                    'address' => $rowData['address'] ?? null,
                    'notes' => $rowData['notes'] ?? null,
                    'photo_path' => null, // Photos can't be imported via CSV
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->importErrors[] = "Row {$row}: " . $e->getMessage();
                $this->errorCount++;
            }

            $row++;
        }

        fclose($file);
        $this->processing = false;
    }

    /**
     * Download sample CSV file for client import
     */
    public function downloadSampleCsv()
    {
        return response()->streamDownload(function () {
            $headers = ['name', 'email', 'phone', 'address', 'notes'];
            $sample = [
                ['John Doe', 'john@example.com', '1234567890', '123 Main St', 'Regular customer'],
                ['Jane Smith', 'jane@example.com', '0987654321', '456 Oak Ave', 'Prefers appointments on weekends'],
            ];

            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            foreach ($sample as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, 'client_import_sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Go to the previous step
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Add a team member
     */
    public function addTeamMember()
    {
        $this->validate([
            'teamMemberName' => 'required|min:2',
            'teamMemberEmail' => 'required|email|unique:users,email',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Generate a random password
        $password = Str::random(10);

        // Create the user with tailor role
        $teamMember = User::create([
            'name' => $this->teamMemberName,
            'email' => $this->teamMemberEmail,
            'password' => bcrypt($password),
            'parent_id' => $user->id,
            'role' => 'tailor', // Set string role for backward compatibility
            'onboarding_completed' => true, // Team members don't need onboarding
        ]);

        // Find the tailor role ID and assign it
        $tailorRole = \App\Models\Role::where('name', 'tailor')->first();
        if ($tailorRole) {
            $teamMember->role_id = $tailorRole->id;
            $teamMember->save();
        }

        // Send invitation email
        $teamMember->notify(new TeamMemberInvitation($password, $this->businessName ?: 'Our Business'));

        // Add to the team members array for display in the UI
        $this->teamMembers[] = [
            'name' => $this->teamMemberName,
            'email' => $this->teamMemberEmail,
            'id' => $teamMember->id,
        ];

        // Reset the form fields
        $this->teamMemberName = '';
        $this->teamMemberEmail = '';
    }

    /**
     * Remove a team member
     */
    public function removeTeamMember($index)
    {
        // If the team member has an ID, delete them from the database
        if (isset($this->teamMembers[$index]['id'])) {
            $teamMemberId = $this->teamMembers[$index]['id'];
            $teamMember = User::find($teamMemberId);
            if ($teamMember) {
                $teamMember->delete();
            }
        }

        // Remove from the array
        unset($this->teamMembers[$index]);
        $this->teamMembers = array_values($this->teamMembers);
    }

    /**
     * Complete the onboarding process
     */
    public function completeOnboarding()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Save business information
        $businessDetail = $user->businessDetail()->create([
            'business_name' => $this->businessName,
            'business_address' => $this->businessAddress,
            'business_phone' => $this->businessPhone,
            'business_email' => $this->businessEmail,
            'logo_path' => $this->logo ? $this->logo->store('logos', 'public') : null,
            'business_hours_start' => $this->businessHoursStart,
            'business_hours_end' => $this->businessHoursEnd,
            'available_days' => $this->availableDays,
        ]);

        // Team members are already created when added via the addTeamMember method

        // Mark onboarding as completed
        $user->onboarding_completed = true;
        $user->save();

        // Redirect to dashboard
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.onboarding.wizard');
    }
}
