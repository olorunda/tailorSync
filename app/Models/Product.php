<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
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
        'sku',
        'category',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'is_featured',
        'is_active',
        'is_custom_order',
        'images',
        'primary_image',
        'sizes',
        'colors',
        'materials',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_custom_order' => 'boolean',
        'images' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        'materials' => 'array',
        'tags' => 'array',
    ];

    /**
     * Get the user that owns the product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the design associated with the product (for custom orders).
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the cart items for the product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the current price of the product (sale price if available, otherwise regular price).
     */
    public function getCurrentPrice(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if the product is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Check if the product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Get the discount percentage if the product is on sale.
     */
    public function getDiscountPercentage(): ?int
    {
        if (!$this->isOnSale()) {
            return null;
        }

        return (int) (100 - ($this->sale_price / $this->price * 100));
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include custom order products.
     */
    public function scopeCustomOrder($query)
    {
        return $query->where('is_custom_order', true);
    }

    /**
     * Scope a query to only include regular products (not custom orders).
     */
    public function scopeRegular($query)
    {
        return $query->where('is_custom_order', false);
    }

    /**
     * Scope a query to filter products based on given criteria.
     */
    public function scopeFilter($query)
    {
        $request=request();
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $statusMap = [
                'active' => ['is_active', true],
                'inactive' => ['is_active', false],
                'featured' => ['is_featured', true],
                'custom' => ['is_custom_order', true]
            ];

            if (isset($statusMap[$request->status])) {
                $query->where(...$statusMap[$request->status]);
            }
        }

        // Apply sorting
        $sortMap = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'stock_asc' => ['stock_quantity', 'asc'],
            'stock_desc' => ['stock_quantity', 'desc']
        ];

        $sort = $request->sort;
        $query->orderBy(...($sortMap[$sort] ?? ['created_at', 'desc']));
        return $query;
    }
}
