<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'client_id',
        'order_number',
        'design_name',
        'design_id',
        'fabric_type',
        'due_date',
        'status',
        'cost',
        'deposit',
        'deposit_amount',
        'balance',
        'description',
        'notes',
        'total_amount',
        'photos',
        'is_store_order',
        'payment_status',
        'shipping_address',
        'billing_address',
        'shipping_method',
        'shipping_cost',
        'tax',
        'tracking_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'cost' => 'decimal:2',
        'deposit' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'photos' => 'array',
        'is_store_order' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client that owns the order.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the design associated with the order.
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    /**
     * Get the inventory items for the order.
     */
    public function inventoryItems(): BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class, 'inventory_order')
            ->withPivot('quantity_used')
            ->withTimestamps();
    }

    /**
     * Get the appointments for the order.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the invoices for the order.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payments for the order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the tasks for the order.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if the order is a store order.
     */
    public function isStoreOrder(): bool
    {
        return $this->is_store_order;
    }

    /**
     * Get the products for the order.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'price', 'total', 'options', 'custom_design_data')
            ->withTimestamps();
    }
}
