<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'business_name',
        'business_address',
        'business_phone',
        'business_email',
        'logo_path',
        'facebook_handle',
        'instagram_handle',
        'tiktok_handle',
        'whatsapp_handle',
    ];

    /**
     * Get the user that owns the business details.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
