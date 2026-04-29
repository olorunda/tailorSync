<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::addGlobalScope('scoped_to_business', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                $businessId = $user->parent_id ?? $user->id;
                
                $builder->where(function ($query) use ($businessId) {
                    $query->where('user_id', $businessId)
                          ->orWhere('is_system', true);
                });
            }
        });

        // Set user_id on creation if not provided
        static::creating(function ($role) {
            if (auth()->check() && empty($role->user_id) && !$role->is_system) {
                $user = auth()->user();
                $role->user_id = $user->parent_id ?? $user->id;
            }
        });
    }

    /**
     * Check if the role is a system role.
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * The permissions that belong to the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
}
