<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformFee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'user_id',
        'amount',
        'fee_amount',
        'fee_percentage',
        'currency',
        'payment_reference',
        'status',
        'processed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
    ];

    /**
     * Get the payment that generated this fee.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the user (business owner) who processed the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
