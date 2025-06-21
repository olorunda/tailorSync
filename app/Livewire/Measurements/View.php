<?php

namespace App\Livewire\Measurements;

use App\Models\Client;
use App\Models\Measurement;
use App\Models\MeasurementType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class View extends Component
{
    public Client $client;
    public Measurement $measurement;
    public $measurementTypes = [];
    public $standardMeasurements = [];
    public $customMeasurements = [];

    public function mount(Client $client, Measurement $measurement)
    {
        if (!auth()->user()->hasPermission('view_measurements')) {
            session()->flash('error', 'You do not have permission to view measurements.');
            return redirect()->route('clients.show', $client);
        }

        $this->client = $client;
        $this->measurement = $measurement;
        $this->loadMeasurementTypes();
        $this->loadMeasurements();
    }

    public function loadMeasurementTypes()
    {
        $this->measurementTypes = MeasurementType::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function loadMeasurements()
    {
        // Standard measurements from the measurements JSON field
        $this->standardMeasurements = $this->measurement->measurements ?? [];

        // Custom measurements from the additional_measurements JSON field
        $this->customMeasurements = $this->measurement->additional_measurements ?? [];
    }

    public function render()
    {
        return view('livewire.measurements.view');
    }
}
