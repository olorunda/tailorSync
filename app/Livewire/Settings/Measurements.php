<?php

namespace App\Livewire\Settings;

use App\Models\MeasurementType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Measurements extends Component
{
    public $measurementTypes = [];
    public $newMeasurementType = [
        'name' => '',
        'description' => '',
        'unit' => 'cm',
        'is_active' => true,
    ];
    public $editingMeasurementType = null;
    public $isEditing = false;

    protected $rules = [
        'newMeasurementType.name' => 'required|string|max:255',
        'newMeasurementType.description' => 'nullable|string',
        'newMeasurementType.unit' => 'required|string|max:10',
        'newMeasurementType.is_active' => 'boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->hasPermission('manage_measurements')) {
            session()->flash('error', 'You do not have permission to manage measurement settings.');
            return redirect()->route('settings.profile');
        }

        $this->loadMeasurementTypes();
    }

    public function loadMeasurementTypes()
    {
        $this->measurementTypes = MeasurementType::where('user_id', Auth::id())->orderBy('name')->get();
    }

    public function createMeasurementType()
    {
        $this->validate();

        MeasurementType::create([
            'user_id' => Auth::id(),
            'name' => $this->newMeasurementType['name'],
            'description' => $this->newMeasurementType['description'],
            'unit' => $this->newMeasurementType['unit'],
            'is_active' => $this->newMeasurementType['is_active'],
        ]);

        $this->resetForm();
        $this->loadMeasurementTypes();
        $this->dispatch('alert', ['status' => 'success', 'message' => 'Measurement type created successfully.']);
    }

    public function editMeasurementType($id)
    {
        $measurementType = MeasurementType::findOrFail($id);
        $this->editingMeasurementType = $measurementType;
        $this->newMeasurementType = [
            'name' => $measurementType->name,
            'description' => $measurementType->description,
            'unit' => $measurementType->unit,
            'is_active' => $measurementType->is_active,
        ];
        $this->isEditing = true;
    }

    public function updateMeasurementType()
    {
        $this->validate();

        $this->editingMeasurementType->update([
            'name' => $this->newMeasurementType['name'],
            'description' => $this->newMeasurementType['description'],
            'unit' => $this->newMeasurementType['unit'],
            'is_active' => $this->newMeasurementType['is_active'],
        ]);

        $this->resetForm();
        $this->loadMeasurementTypes();
        $this->dispatch('alert', ['status' => 'success', 'message' => 'Measurement type updated successfully.']);
    }

    public function deleteMeasurementType($id)
    {
        $measurementType = MeasurementType::findOrFail($id);
        $measurementType->delete();

        $this->loadMeasurementTypes();
        $this->dispatch('alert', ['status' => 'success', 'message' => 'Measurement type deleted successfully.']);
    }

    public function toggleActive($id)
    {
        $measurementType = MeasurementType::findOrFail($id);
        $measurementType->update([
            'is_active' => !$measurementType->is_active,
        ]);

        $this->loadMeasurementTypes();
        $this->dispatch('alert', ['status' => 'success', 'message' => 'Measurement type status updated.']);
    }

    public function resetForm()
    {
        $this->newMeasurementType = [
            'name' => '',
            'description' => '',
            'unit' => 'cm',
            'is_active' => true,
        ];
        $this->editingMeasurementType = null;
        $this->isEditing = false;
    }

    public function render()
    {
        return view('livewire.settings.measurements');
    }
}
