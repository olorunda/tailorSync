<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
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
        'order_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount',
        'total',
        'total_amount',
        'status',
        'notes',
        'client_data',
        'items',
        'terms',
        'description',
    ];

    protected $dates = ['due_date','issue_date'];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'client_data' => 'array',
        'items' => 'array',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client that owns the invoice.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the order associated with the invoice.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the payments for the invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate the total amount paid for this invoice.
     */
    public function totalPaid()
    {
        return $this->payments->sum('amount');
    }

    /**
     * Calculate the balance due for this invoice.
     */
    public function balanceDue()
    {
        return $this->total - $this->totalPaid();
    }

    /**
     * Check if the invoice is fully paid.
     */
    public function isPaid(): bool
    {
        return $this->balanceDue() <= 0;
    }

    /**
     * Check if the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isPaid();
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Set the total amount and keep it in sync with the total column.
     */
    public function setTotalAmountAttribute($value)
    {
        $this->attributes['total_amount'] = $value;
        $this->attributes['total'] = $value;
    }

    /**
     * Set the total and keep it in sync with the total_amount column.
     */
    public function setTotalAttribute($value)
    {
        $this->attributes['total'] = $value;
        $this->attributes['total_amount'] = $value;
    }

    /**
     * Get the client name from the client_data array.
     */
    public function getClientNameAttribute()
    {
        return $this->client_data['name'] ?? null;
    }

    /**
     * Get the client email from the client_data array.
     */
    public function getClientEmailAttribute()
    {
        return $this->client_data['email'] ?? null;
    }

    /**
     * Get the client address from the client_data array.
     */
    public function getClientAddressAttribute()
    {
        return $this->client_data['address'] ?? null;
    }

    /**
     * Set the client name in the client_data array.
     */
    public function setClientNameAttribute($value)
    {
        $clientData = $this->client_data ?? [];
        $clientData['name'] = $value;
        $this->attributes['client_data'] = json_encode($clientData);
    }

    /**
     * Set the client email in the client_data array.
     */
    public function setClientEmailAttribute($value)
    {
        $clientData = $this->client_data ?? [];
        $clientData['email'] = $value;
        $this->attributes['client_data'] = json_encode($clientData);
    }

    /**
     * Set the client address in the client_data array.
     */
    public function setClientAddressAttribute($value)
    {
        $clientData = $this->client_data ?? [];
        $clientData['address'] = $value;
        $this->attributes['client_data'] = json_encode($clientData);
    }

    /**
     * Get invoice_date attribute (alias for issue_date).
     */
    public function getInvoiceDateAttribute()
    {
        return $this->issue_date;
    }

    /**
     * Set invoice_date attribute (alias for issue_date).
     */
    public function setInvoiceDateAttribute($value)
    {
        $this->attributes['issue_date'] = $value;
    }

    /**
     * Get discount_amount attribute (alias for discount).
     */
    public function getDiscountAmountAttribute()
    {
        return $this->discount;
    }

    /**
     * Set discount_amount attribute (alias for discount).
     */
    public function setDiscountAmountAttribute($value)
    {
        $this->attributes['discount'] = $value;
    }
}
