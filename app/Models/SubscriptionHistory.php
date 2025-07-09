<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_detail_id',
        'subscription_plan',
        'subscription_start_date',
        'subscription_end_date',
        'subscription_active',
        'subscription_payment_method',
        'subscription_payment_id',
        'subscription_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subscription_start_date' => 'datetime',
        'subscription_end_date' => 'datetime',
        'subscription_active' => 'boolean',
    ];

    /**
     * Get the business detail that owns this subscription history record.
     */
    public function businessDetail()
    {
        return $this->belongsTo(BusinessDetail::class);
    }
}
