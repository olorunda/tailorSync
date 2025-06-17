<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category',
        'vendor',
        'amount',
        'payment_method',
        'date',
        'description',
        'receipt_path',
        'is_recurring',
        'reference_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user that owns the expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include expenses within a date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include expenses in a specific category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include recurring expenses.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Get the categories of expenses.
     */
    public static function categories()
    {
        return [
            'rent',
            'utilities',
            'supplies',
            'equipment',
            'salaries',
            'marketing',
            'transportation',
            'maintenance',
            'other',
        ];
    }

    /**
     * Set the date attribute to current date if not provided.
     */
    protected static function booted()
    {
        static::creating(function ($expense) {
            if (empty($expense->date)) {
                $expense->date = now()->toDateString();
            }
        });

        static::updating(function ($expense) {
            if (empty($expense->date)) {
                $expense->date = now()->toDateString();
            }
        });
    }
}
