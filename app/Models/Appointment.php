<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
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
        'title',
        'description',
        'start_time',
        'end_time',
        'type',
        'status',
        'reminder_sent',
        'date',
        'location',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'reminder_sent' => 'boolean',
        'date' => 'date',
        'location' => 'string',
    ];

    /**
     * Get the user that owns the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client that owns the appointment.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the order associated with the appointment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to only include upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope a query to only include appointments of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if the appointment needs a reminder.
     */
    public function needsReminder(): bool
    {
        return !$this->reminder_sent &&
               $this->start_time->subHours(24)->isFuture() &&
               $this->status !== 'cancelled';
    }

    /**
     * Set the date attribute from start_time if not provided.
     */
    protected static function booted()
    {
        static::creating(function ($appointment) {
            if (empty($appointment->date) && !empty($appointment->start_time)) {
                $appointment->date = $appointment->start_time->toDateString();
            }
        });

        static::updating(function ($appointment) {
            if (empty($appointment->date) && !empty($appointment->start_time)) {
                $appointment->date = $appointment->start_time->toDateString();
            }
        });
    }

    /**
     * Mark the appointment as completed.
     */
    public function complete()
    {
        $this->status = 'completed';
        $this->save();

        return $this;
    }
}
