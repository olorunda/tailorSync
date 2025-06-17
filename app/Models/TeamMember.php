<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamMember extends Model
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
        'email',
        'phone',
        'role',
        'skills',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'skills' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the team member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tasks for the team member.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the active tasks for the team member.
     */
    public function activeTasks()
    {
        return $this->tasks()->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope a query to only include active team members.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include team members with a specific role.
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Get the roles of team members.
     */
    public static function roles()
    {
        return [
            'cutter',
            'tailor',
            'designer',
            'delivery',
            'admin',
            'other',
        ];
    }
}
