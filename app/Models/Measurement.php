<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'user_id',
        'name',
        'measurements',
        'additional_measurements',
        'photos',
        'notes',
        'measurement_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'measurement_date' => 'date',
        'additional_measurements' => 'array',
        'measurements' => 'array',
        'photos' => 'array',
    ];

    /**
     * Get the client that owns the measurement.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Set the measurement_date attribute to current date if not provided.
     */
    protected static function booted()
    {
        static::creating(function ($measurement) {
            if (empty($measurement->measurement_date)) {
                $measurement->measurement_date = now()->toDateString();
            }
        });

        static::updating(function ($measurement) {
            if (empty($measurement->measurement_date)) {
                $measurement->measurement_date = now()->toDateString();
            }
        });
    }
}
