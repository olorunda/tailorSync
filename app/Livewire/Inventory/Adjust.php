<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Adjust extends Component
{
    use WithFileUploads;

    public $csvFile = null;
    public $adjustedCount = 0;
    public $errorCount = 0;
    public $adjustmentErrors = [];
    public $processing = false;

    public function mount()
    {
        if (!auth()->user()->hasPermission('edit_inventory')) {
            session()->flash('error', 'You do not have permission to adjust inventory quantities.');
            return $this->redirect(route('inventory.index'), navigate: true);
        }
    }

    public function adjust(InventoryService $inventoryService)
    {
        if (!auth()->user()->hasPermission('edit_inventory')) {
            session()->flash('error', 'You do not have permission to adjust inventory quantities.');
            return $this->redirect(route('inventory.index'), navigate: true);
        }

        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
        ]);

        $this->processing = true;
        $this->adjustedCount = 0;
        $this->errorCount = 0;
        $this->adjustmentErrors = [];

        $result = $inventoryService->adjustFromCsv($this->csvFile);

        $this->processing = false;

        if (isset($result['error'])) {
            $this->addError('csvFile', $result['error']);
            return;
        }

        $this->adjustedCount = $result['adjustedCount'];
        $this->errorCount = $result['errorCount'];
        $this->adjustmentErrors = $result['adjustmentErrors'];

        if ($this->adjustedCount > 0) {
            session()->flash('status', "{$this->adjustedCount} inventory items adjusted successfully.");
        }
    }

    public function downloadTemplate()
    {
        $user = Auth::user();
        $items = $user->allInventoryItems()->get();

        return response()->streamDownload(function () use ($items) {
            $headers = ['sku', 'name', 'current_quantity', 'new_quantity', 'adjustment'];
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);

            foreach ($items as $item) {
                fputcsv($output, [
                    $item->sku,
                    $item->name,
                    $item->quantity,
                    '', 
                    '', 
                ]);
            }
            fclose($output);
        }, 'inventory_adjustment_template.csv');
    }

    public function render()
    {
        return view('livewire.inventory.adjust')
            ->layout('components.layouts.app', ['title' => 'Adjust Inventory']);
    }
}
