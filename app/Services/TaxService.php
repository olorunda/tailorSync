<?php

namespace App\Services;

use App\Models\BusinessDetail;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payment;

class TaxService
{
    protected $businessDetail;

    public function __construct(BusinessDetail $businessDetail = null)
    {
        $this->businessDetail = $businessDetail;
    }

    /**
     * Calculate tax for an invoice based on the business's tax settings
     *
     * @param Invoice $invoice
     * @return array
     */
    public function calculateInvoiceTax(Invoice $invoice)
    {
        if (!$this->businessDetail || !$this->businessDetail->tax_enabled) {
            return [
                'tax_rate' => 0,
                'tax_amount' => 0
            ];
        }

        $taxCountry = $this->businessDetail->tax_country;
        $taxSettings = $this->businessDetail->tax_settings;
        $subtotal = $invoice->subtotal;

        switch ($taxCountry) {
            case 'canada':
                return $this->calculateCanadianTax($subtotal, $taxSettings);
            case 'us':
                return $this->calculateUSTax($subtotal, $taxSettings);
            case 'uk':
                return $this->calculateUKTax($subtotal, $taxSettings);
            case 'nigeria':
                return $this->calculateNigeriaTax($subtotal, $taxSettings);
            default:
                return [
                    'tax_rate' => 0,
                    'tax_amount' => 0
                ];
        }
    }

    /**
     * Calculate Canadian tax (GST/HST/PST)
     *
     * @param float $amount
     * @param array $taxSettings
     * @return array
     */
    protected function calculateCanadianTax($amount, $taxSettings)
    {
        $province = $taxSettings['province'] ?? 'default';
        $gstRate = $taxSettings['gst_rate'] ?? 5;
        $pstRate = $taxSettings['pst_rate'] ?? 0;
        $hstRate = $taxSettings['hst_rate'] ?? 0;

        // Different provinces have different tax structures
        switch ($province) {
            case 'alberta':
            case 'northwest_territories':
            case 'nunavut':
            case 'yukon':
                // GST only (5%)
                $taxRate = $gstRate;
                break;
            case 'british_columbia':
                // GST (5%) + PST (7%)
                $taxRate = $gstRate + $pstRate;
                break;
            case 'manitoba':
                // GST (5%) + PST (7%)
                $taxRate = $gstRate + $pstRate;
                break;
            case 'quebec':
                // GST (5%) + QST (9.975%)
                $taxRate = $gstRate + $pstRate;
                break;
            case 'saskatchewan':
                // GST (5%) + PST (6%)
                $taxRate = $gstRate + $pstRate;
                break;
            case 'ontario':
            case 'new_brunswick':
            case 'newfoundland_and_labrador':
            case 'nova_scotia':
            case 'prince_edward_island':
                // HST (13-15% depending on province)
                $taxRate = $hstRate;
                break;
            default:
                // Default to GST only
                $taxRate = $gstRate;
                break;
        }

        $taxAmount = $amount * ($taxRate / 100);

        return [
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2)
        ];
    }

    /**
     * Calculate US tax (State + Local)
     *
     * @param float $amount
     * @param array $taxSettings
     * @return array
     */
    protected function calculateUSTax($amount, $taxSettings)
    {
        $state = $taxSettings['state'] ?? 'default';
        $stateRate = $taxSettings['state_rate'] ?? 0;
        $localRate = $taxSettings['local_rate'] ?? 0;

        $taxRate = $stateRate + $localRate;
        $taxAmount = $amount * ($taxRate / 100);

        return [
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2)
        ];
    }

    /**
     * Calculate UK tax (VAT)
     *
     * @param float $amount
     * @param array $taxSettings
     * @return array
     */
    protected function calculateUKTax($amount, $taxSettings)
    {
        $vatRate = $taxSettings['vat_rate'] ?? 20; // Standard UK VAT rate is 20%
        $taxAmount = $amount * ($vatRate / 100);

        return [
            'tax_rate' => $vatRate,
            'tax_amount' => round($taxAmount, 2)
        ];
    }

    /**
     * Calculate Nigeria tax (VAT)
     *
     * @param float $amount
     * @param array $taxSettings
     * @return array
     */
    protected function calculateNigeriaTax($amount, $taxSettings)
    {
        $vatRate = $taxSettings['vat_rate'] ?? 7.5; // Standard Nigeria VAT rate is 7.5%
        $taxAmount = $amount * ($vatRate / 100);

        return [
            'tax_rate' => $vatRate,
            'tax_amount' => round($taxAmount, 2)
        ];
    }

    /**
     * Calculate tax summary for all invoices, expenses, and payments
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $userId
     * @return array
     */
    public function calculateTaxSummary($startDate, $endDate, $userId)
    {
        // Get all invoices, expenses, and payments within the date range
        $invoices = Invoice::where('user_id', $userId)
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->get();

        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $payments = Payment::where('user_id', $userId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        // Calculate totals
        $totalRevenue = $invoices->sum('total');
        $totalTaxCollected = $invoices->sum('tax_amount');
        $totalExpenses = $expenses->sum('amount');
        $totalPayments = $payments->sum('amount');

        // Calculate tax liability based on country
        $taxLiability = $this->calculateTaxLiability(
            $totalRevenue,
            $totalTaxCollected,
            $totalExpenses,
            $this->businessDetail->tax_country,
            $this->businessDetail->tax_settings
        );

        return [
            'total_revenue' => $totalRevenue,
            'total_tax_collected' => $totalTaxCollected,
            'total_expenses' => $totalExpenses,
            'total_payments' => $totalPayments,
            'tax_liability' => $taxLiability,
            'tax_country' => $this->businessDetail->tax_country,
            'tax_settings' => $this->businessDetail->tax_settings,
        ];
    }

    /**
     * Calculate tax liability based on country-specific rules
     *
     * @param float $revenue
     * @param float $taxCollected
     * @param float $expenses
     * @param string $taxCountry
     * @param array $taxSettings
     * @return array
     */
    protected function calculateTaxLiability($revenue, $taxCollected, $expenses, $taxCountry, $taxSettings)
    {
        switch ($taxCountry) {
            case 'canada':
                return $this->calculateCanadianTaxLiability($revenue, $taxCollected, $expenses, $taxSettings);
            case 'us':
                return $this->calculateUSTaxLiability($revenue, $taxCollected, $expenses, $taxSettings);
            case 'uk':
                return $this->calculateUKTaxLiability($revenue, $taxCollected, $expenses, $taxSettings);
            case 'nigeria':
                return $this->calculateNigeriaTaxLiability($revenue, $taxCollected, $expenses, $taxSettings);
            default:
                return [
                    'tax_payable' => 0,
                    'details' => []
                ];
        }
    }

    /**
     * Calculate Canadian tax liability
     *
     * @param float $revenue
     * @param float $taxCollected
     * @param float $expenses
     * @param array $taxSettings
     * @return array
     */
    protected function calculateCanadianTaxLiability($revenue, $taxCollected, $expenses, $taxSettings)
    {
        $province = $taxSettings['province'] ?? 'default';
        $gstCollected = 0;
        $pstCollected = 0;
        $hstCollected = 0;

        // Calculate input tax credits (ITCs) - tax paid on business expenses
        $expenseTaxRate = $this->getCanadianExpenseTaxRate($province, $taxSettings);
        $inputTaxCredits = $expenses * ($expenseTaxRate / 100);

        // Different calculations based on province
        if (in_array($province, ['ontario', 'new_brunswick', 'newfoundland_and_labrador', 'nova_scotia', 'prince_edward_island'])) {
            // HST provinces
            $hstCollected = $taxCollected;
            $taxPayable = $hstCollected - $inputTaxCredits;
        } else {
            // GST/PST provinces
            $gstRate = $taxSettings['gst_rate'] ?? 5;
            $pstRate = $taxSettings['pst_rate'] ?? 0;
            $totalRate = $gstRate + $pstRate;

            if ($totalRate > 0) {
                $gstCollected = $taxCollected * ($gstRate / $totalRate);
                $pstCollected = $taxCollected * ($pstRate / $totalRate);
            }

            // Only GST has input tax credits, PST generally doesn't
            $taxPayable = $gstCollected - $inputTaxCredits;
        }

        return [
            'tax_payable' => max(0, round($taxPayable, 2)),
            'details' => [
                'gst_collected' => round($gstCollected, 2),
                'pst_collected' => round($pstCollected, 2),
                'hst_collected' => round($hstCollected, 2),
                'input_tax_credits' => round($inputTaxCredits, 2)
            ]
        ];
    }

    /**
     * Get Canadian expense tax rate based on province
     *
     * @param string $province
     * @param array $taxSettings
     * @return float
     */
    protected function getCanadianExpenseTaxRate($province, $taxSettings)
    {
        $gstRate = $taxSettings['gst_rate'] ?? 5;
        $pstRate = $taxSettings['pst_rate'] ?? 0;
        $hstRate = $taxSettings['hst_rate'] ?? 0;

        switch ($province) {
            case 'ontario':
            case 'new_brunswick':
            case 'newfoundland_and_labrador':
            case 'nova_scotia':
            case 'prince_edward_island':
                return $hstRate;
            default:
                // For GST/PST provinces, only GST is eligible for input tax credits
                return $gstRate;
        }
    }

    /**
     * Calculate US tax liability
     *
     * @param float $revenue
     * @param float $taxCollected
     * @param float $expenses
     * @param array $taxSettings
     * @return array
     */
    protected function calculateUSTaxLiability($revenue, $taxCollected, $expenses, $taxSettings)
    {
        // In the US, sales tax is collected on behalf of the state/local government
        // Businesses generally don't get credits for sales tax paid on expenses
        // The liability is simply the amount collected
        return [
            'tax_payable' => round($taxCollected, 2),
            'details' => [
                'state_tax' => round($taxCollected * 0.7, 2), // Approximate split between state and local
                'local_tax' => round($taxCollected * 0.3, 2)
            ]
        ];
    }

    /**
     * Calculate UK tax liability (VAT)
     *
     * @param float $revenue
     * @param float $taxCollected
     * @param float $expenses
     * @param array $taxSettings
     * @return array
     */
    protected function calculateUKTaxLiability($revenue, $taxCollected, $expenses, $taxSettings)
    {
        $vatRate = $taxSettings['vat_rate'] ?? 20;

        // Calculate input VAT (VAT paid on business expenses)
        $inputVAT = $expenses * ($vatRate / 100);

        // VAT payable is output VAT (collected) minus input VAT (paid on expenses)
        $vatPayable = $taxCollected - $inputVAT;

        return [
            'tax_payable' => max(0, round($vatPayable, 2)),
            'details' => [
                'vat_collected' => round($taxCollected, 2),
                'input_vat' => round($inputVAT, 2)
            ]
        ];
    }

    /**
     * Calculate Nigeria tax liability (VAT)
     *
     * @param float $revenue
     * @param float $taxCollected
     * @param float $expenses
     * @param array $taxSettings
     * @return array
     */
    protected function calculateNigeriaTaxLiability($revenue, $taxCollected, $expenses, $taxSettings)
    {
        $vatRate = $taxSettings['vat_rate'] ?? 7.5;

        // Calculate input VAT (VAT paid on business expenses)
        $inputVAT = $expenses * ($vatRate / 100);

        // VAT payable is output VAT (collected) minus input VAT (paid on expenses)
        $vatPayable = $taxCollected - $inputVAT;

        return [
            'tax_payable' => max(0, round($vatPayable, 2)),
            'details' => [
                'vat_collected' => round($taxCollected, 2),
                'input_vat' => round($inputVAT, 2)
            ]
        ];
    }
}
