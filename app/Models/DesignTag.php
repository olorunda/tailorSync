<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignTag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'design_id',
        'tag_type',
        'tag_value',
    ];

    /**
     * Get the design that owns the tag.
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }
}
