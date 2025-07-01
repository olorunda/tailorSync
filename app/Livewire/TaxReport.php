<?php

namespace App\Livewire;

use App\Models\BusinessDetail;
use App\Services\TaxService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TaxReport extends Component
{
    public $startDate;
    public $endDate;
    public $taxSummary = null;
    public $businessDetail;
    public $loading = false;

    public function mount()
    {
        $user = Auth::user();

        // Check if user has permission to view tax reports
        if (!$user->hasPermission('view_tax_reports')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view tax reports.');
        }

        // Default to current quarter
        $now = now();
        $currentMonth = $now->month;
        $currentQuarter = ceil($currentMonth / 3);
        $startMonth = ($currentQuarter - 1) * 3 + 1;

        $this->startDate = $now->setMonth($startMonth)->startOfMonth()->format('Y-m-d');
        $this->endDate = $now->setMonth($startMonth + 2)->endOfMonth()->format('Y-m-d');

        // Get business details
        $this->businessDetail = $user->businessDetail;
    }

    public function generateReport()
    {
        $this->loading = true;

        // Validate dates
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $user = Auth::user();

        // Check if tax is enabled
        if (!$this->businessDetail || !$this->businessDetail->tax_enabled) {
            $this->taxSummary = [
                'error' => 'Tax calculation is not enabled in your business settings.',
            ];
            $this->loading = false;
            return;
        }

        // Generate tax summary using TaxService
        $taxService = new TaxService($this->businessDetail);
        $this->taxSummary = $taxService->calculateTaxSummary(
            $this->startDate,
            $this->endDate,
            $user->id
        );

        $this->loading = false;
    }

    public function setDateRange($range)
    {
        $now = now();

        switch ($range) {
            case 'current_month':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;

            case 'previous_month':
                $this->startDate = $now->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;

            case 'current_quarter':
                $currentMonth = $now->month;
                $currentQuarter = ceil($currentMonth / 3);
                $startMonth = ($currentQuarter - 1) * 3 + 1;

                $this->startDate = $now->setMonth($startMonth)->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->setMonth($startMonth + 2)->endOfMonth()->format('Y-m-d');
                break;

            case 'year_to_date':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;

            case 'previous_year':
                $this->startDate = $now->subYear()->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;
        }

        // Generate report with new date range
        $this->generateReport();
    }

    public function render()
    {
        return view('livewire.tax-report');
    }
}
