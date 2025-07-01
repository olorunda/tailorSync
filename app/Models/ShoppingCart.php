<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingCart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'total',
        'subtotal',
        'tax_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the shopping cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the shopping cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Add a product to the cart.
     */
    public function addItem(Product $product, int $quantity = 1, array $options = [], array $customDesignData = null): CartItem
    {
        // Check if the product is already in the cart
        $existingItem = $this->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            // Update the quantity
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity,
                'options' => $options ?: $existingItem->options,
                'custom_design_data' => $customDesignData ?: $existingItem->custom_design_data,
            ]);

            $this->updateTotal();

            return $existingItem;
        }

        // Add a new item
        $item = $this->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->getCurrentPrice(),
            'options' => $options,
            'custom_design_data' => $customDesignData,
        ]);

        $this->updateTotal();

        return $item;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(CartItem $item): void
    {
        $item->delete();
        $this->updateTotal();
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function updateItemQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($item);
            return;
        }

        $item->update(['quantity' => $quantity]);
        $this->updateTotal();
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        $this->items()->delete();
        $this->updateTotal();
    }

    /**
     * Calculate tax for the cart based on business settings.
     */
    public function calculateTax(): float
    {
        // Get the business details for the user
        $businessDetail = $this->user->businessDetail;

        // If no business details or tax not enabled, return 0
        if (!$businessDetail || !$businessDetail->tax_enabled) {
            return 0;
        }

        // Calculate subtotal
        $subtotal = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Use TaxService to calculate tax
        $taxService = new \App\Services\TaxService($businessDetail);

        // Create a temporary invoice object to calculate tax
        $tempInvoice = new \App\Models\Invoice();
        $tempInvoice->subtotal = $subtotal;

        $taxResult = $taxService->calculateInvoiceTax($tempInvoice);

        return $taxResult['tax_amount'];
    }

    /**
     * Update the total price of the cart.
     */
    public function updateTotal(): void
    {
        // Calculate subtotal
        $subtotal = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Calculate tax
        $taxAmount = $this->calculateTax();

        // Calculate total (subtotal + tax)
        $total = $subtotal + $taxAmount;

        // Update the cart
        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total
        ]);
    }

    /**
     * Get the number of items in the cart.
     */
    public function getItemCount(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Convert the cart to an order.
     */
    public function convertToOrder(array $orderData): Order
    {
        // Make sure totals are up to date
        $this->updateTotal();

        // Get tax rate if available
        $taxRate = 0;
        $businessDetail = $this->user->businessDetail;
        if ($businessDetail && $businessDetail->tax_enabled) {
            $taxService = new \App\Services\TaxService($businessDetail);
            $tempInvoice = new \App\Models\Invoice();
            $tempInvoice->subtotal = $this->subtotal;
            $taxResult = $taxService->calculateInvoiceTax($tempInvoice);
            $taxRate = $taxResult['tax_rate'];
        }

        // Calculate tax amount directly
        $taxAmount = 0;
        if ($businessDetail && $businessDetail->tax_enabled) {
            $taxService = new \App\Services\TaxService($businessDetail);
            $tempInvoice = new \App\Models\Invoice();
            $tempInvoice->subtotal = $this->subtotal;
            $taxResult = $taxService->calculateInvoiceTax($tempInvoice);
            $taxAmount = $taxResult['tax_amount'];
        }

        // Create the order
        $order = Order::create(array_merge([
            'user_id' => $this->user_id,
            'client_id' => $orderData['client_id'] ?? null,
            'order_number' => 'ORD-' . time(),
            'status' => 'pending',
            'subtotal' => $this->subtotal,
            'tax_rate' => $taxRate,
            'tax' => $taxAmount, // Use the directly calculated tax amount
            'total_amount' => $this->subtotal + $taxAmount, // Recalculate total with the directly calculated tax
            'is_store_order' => true,
            'payment_status' => 'pending',
            'cost' => $this->subtotal, // Cost should be the subtotal without tax
            'due_date' => $orderData['due_date'] ?? now()->addDays(7), // Default due date is 7 days from now
        ], $orderData));

        // Create order items
        foreach ($this->items as $item) {
            $order->orderItems()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->price * $item->quantity,
                'options' => $item->options,
                'custom_design_data' => $item->custom_design_data,
            ]);

            // Reduce the product quantity
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock_quantity = max(0, $product->stock_quantity - $item->quantity);
                $product->save();
            }

            // If it's a custom order, create a design record
            if ($item->product->is_custom_order && $item->custom_design_data) {
                // Create or update the design
                // This would depend on your specific requirements
            }
        }

        return $order;
    }
}
