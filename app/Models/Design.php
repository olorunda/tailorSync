<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Design extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'description',
        'materials',
        'tags',
        'images',
        'primary_image',
        'old_image_path',
        'collection',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'materials' => 'array',
        'tags' => 'array',
        'images' => 'array',
    ];

    /**
     * Get the user that owns the design.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the orders for the design.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the tags for the design.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(DesignTag::class);
    }

    /**
     * Get fabric tags for the design.
     */
    public function fabricTags()
    {
        return $this->tags()->where('tag_type', 'fabric');
    }

    /**
     * Get style tags for the design.
     */
    public function styleTags()
    {
        return $this->tags()->where('tag_type', 'style');
    }
}
