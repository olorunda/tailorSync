<?php

namespace App\Services;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    /**
     * Adjust inventory quantities from a CSV file handle.
     * 
     * @param \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file
     * @return array
     */
    public function adjustFromCsv($file)
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        $headers = fgetcsv($handle);
        if (!$headers) {
            return ['error' => 'CSV file is empty or invalid.'];
        }

        $headers = array_map('strtolower', array_map('trim', $headers));
        
        $identifierHeaders = ['id', 'sku', 'name'];
        $foundIdentifiers = array_intersect($identifierHeaders, $headers);

        if (empty($foundIdentifiers)) {
            return ['error' => 'CSV file must contain at least one identifier column: id, sku, or name.'];
        }

        $quantityHeaders = ['quantity', 'adjustment', 'new_quantity'];
        $foundQuantity = array_intersect($quantityHeaders, $headers);

        if (empty($foundQuantity)) {
            return ['error' => 'CSV file must contain a quantity, new_quantity or adjustment column.'];
        }

        $user = Auth::user();
        $allInventoryItems = $user->allInventoryItems();

        $adjustedCount = 0;
        $errorCount = 0;
        $adjustmentErrors = [];

        $row = 2;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($headers) !== count($data)) {
                $data = array_pad($data, count($headers), null);
            }
            $rowData = array_combine($headers, $data);

            $item = null;

            if (!empty($rowData['id'])) {
                $item = (clone $allInventoryItems)->find($rowData['id']);
            }
            if (!$item && !empty($rowData['sku'])) {
                $item = (clone $allInventoryItems)->where('sku', $rowData['sku'])->first();
            }
            if (!$item && !empty($rowData['name'])) {
                $item = (clone $allInventoryItems)->where('name', $rowData['name'])->first();
            }

            if (!$item) {
                $identifierValue = $rowData['sku'] ?? $rowData['name'] ?? $rowData['id'] ?? 'unknown';
                $adjustmentErrors[] = "Row {$row}: Could not find inventory item with identifier '{$identifierValue}'.";
                $errorCount++;
                $row++;
                continue;
            }

            try {
                if (isset($rowData['new_quantity']) && $rowData['new_quantity'] !== '') {
                    $item->update(['quantity' => (float) $rowData['new_quantity']]);
                    $adjustedCount++;
                } elseif (isset($rowData['quantity']) && $rowData['quantity'] !== '') {
                    $item->update(['quantity' => (float) $rowData['quantity']]);
                    $adjustedCount++;
                } elseif (isset($rowData['adjustment']) && $rowData['adjustment'] !== '') {
                    $item->increment('quantity', (float) $rowData['adjustment']);
                    $adjustedCount++;
                } else {
                    $adjustmentErrors[] = "Row {$row}: No quantity or adjustment value provided for '{$item->name}'.";
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $adjustmentErrors[] = "Row {$row}: " . $e->getMessage();
                $errorCount++;
            }

            $row++;
        }

        fclose($handle);

        return [
            'adjustedCount' => $adjustedCount,
            'errorCount' => $errorCount,
            'adjustmentErrors' => $adjustmentErrors,
        ];
    }
}
