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
        'store_enabled',
        'store_slug',
        'store_theme_color',
        'store_secondary_color',
        'store_accent_color',
        'store_description',
        'store_banner_image',
        'store_featured_categories',
        'store_social_links',
        'store_announcement',
        'store_show_featured_products',
        'store_show_new_arrivals',
        'store_show_custom_designs',
        'tax_country',
        'tax_settings',
        'tax_enabled',
        'tax_number',
        'payment_enabled',
        'default_payment_gateway',
        'payment_settings',
    ];

    /**
     * Get the user that owns the business details.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'store_enabled' => 'boolean',
        'store_featured_categories' => 'array',
        'store_social_links' => 'array',
        'store_show_featured_products' => 'boolean',
        'store_show_new_arrivals' => 'boolean',
        'store_show_custom_designs' => 'boolean',
        'tax_settings' => 'array',
        'tax_enabled' => 'boolean',
        'payment_settings' => 'array',
        'payment_enabled' => 'boolean',
    ];
}
