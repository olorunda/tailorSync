<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'sku',
        'type',
        'description',
        'image_path',
        'image',
        'quantity',
        'unit',
        'unit_price',
        'total_cost',
        'reorder_level',
        'supplier',
        'supplier_contact',
        'location',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'reorder_level' => 'decimal:2',
    ];

    /**
     * Get the user that owns the inventory item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the orders that use this inventory item.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'inventory_order')
            ->withPivot('quantity_used')
            ->withTimestamps();
    }

    /**
     * Check if the inventory item is low in stock.
     */
    public function isLowStock(): bool
    {
        if ($this->reorder_level === null) {
            return false;
        }

        return $this->quantity <= $this->reorder_level;
    }
}
