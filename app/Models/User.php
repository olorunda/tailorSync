<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'role',
        'role_id',
        'parent_id',
        'onboarding_completed',
        'booking_hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the clients for the user.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get all clients, including those of the parent user or child users.
     */
    public function allClients()
    {
        if ($this->parent_id) {
            // Child account: see own clients and parent's clients
            return Client::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own clients and all children's clients
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Client::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->clients();
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all orders, including those of the parent user or child users.
     */
    public function allOrders()
    {
        if ($this->parent_id) {
            // Child account: see own orders and parent's orders
            return Order::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own orders and all children's orders
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Order::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->orders();
    }

    /**
     * Get the designs for the user.
     */
    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }

    /**
     * Get all designs, including those of the parent user or child users.
     * Limits the number of designs based on subscription plan.
     */
    public function allDesigns()
    {
        $query = null;

        if ($this->parent_id) {
            // Child account: see own designs and parent's designs
            $query = Design::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own designs and all children's designs
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                $query = Design::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            } else {
                $query = $this->designs();
            }
        }

        // Get subscription plan details
        $businessDetail = $this->businessDetail;
        if ($businessDetail) {
            $planKey = $businessDetail->subscription_plan ?? 'free';
            $plan = \App\Services\SubscriptionService::getPlan($planKey);
            $maxDesigns = $plan['features']['max_designs'] ?? 5;

            // If max_designs is not unlimited, limit the number of designs displayed
            if ($maxDesigns !== 'unlimited' && $maxDesigns > 0) {
                $query->limit($maxDesigns);
            }
        }

        return $query;
    }

    /**
     * Get the inventory items for the user.
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Get all inventory items, including those of the parent user or child users.
     */
    public function allInventoryItems()
    {
        if ($this->parent_id) {
            // Child account: see own inventory items and parent's inventory items
            return InventoryItem::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own inventory items and all children's inventory items
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return InventoryItem::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->inventoryItems();
    }

    /**
     * Get the messages for the user.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all messages, including those of the parent user or child users.
     */
    public function allMessages()
    {
        if ($this->parent_id) {
            // Child account: see own messages and parent's messages
            return Message::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own messages and all children's messages
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Message::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->messages();
    }

    /**
     * Get the appointments for the user.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all appointments, including those of the parent user or child users.
     */
    public function allAppointments()
    {
        if ($this->parent_id) {
            // Child account: see own appointments and parent's appointments
            return Appointment::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own appointments and all children's appointments
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Appointment::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->appointments();
    }

    /**
     * Get the invoices for the user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all invoices, including those of the parent user or child users.
     */
    public function allInvoices()
    {
        if ($this->parent_id) {
            // Child account: see own invoices and parent's invoices
            return Invoice::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own invoices and all children's invoices
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Invoice::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->invoices();
    }

    /**
     * Get the payments for the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all payments, including those of the parent user or child users.
     */
    public function allPayments()
    {
        if ($this->parent_id) {
            // Child account: see own payments and parent's payments
            return Payment::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own payments and all children's payments
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Payment::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->payments();
    }

    /**
     * Get the expenses for the user.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get all expenses, including those of the parent user or child users.
     */
    public function allExpenses()
    {
        if ($this->parent_id) {
            // Child account: see own expenses and parent's expenses
            return Expense::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own expenses and all children's expenses
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Expense::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->expenses();
    }

    /**
     * Get the team members for the user.
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get all team members, including those of the parent user or child users.
     * Limits the number of team members based on subscription plan.
     */
    public function allTeamMembers()
    {
        $query = null;

        if ($this->parent_id) {
            // Child account: see own team members and parent's team members
            $query = User::where(function ($query) {
                $query->where('id', $this->id)
                    ->orWhere('parent_id', $this->parent_id);
            });
        } else {
            // Parent account: see own team members and all children's team members
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                $query = User::where(function ($query) use ($childrenIds) {
                    $query
                        //->where('id', $this->id)
                        ->orWhereIn('id', $childrenIds);
                });
            } else {
                $query = $this->teamMembers();
            }
        }

        // Get subscription plan details
        $businessDetail = $this->businessDetail;
        if ($businessDetail) {
            $planKey = $businessDetail->subscription_plan ?? 'free';
            $plan = \App\Services\SubscriptionService::getPlan($planKey);
            $maxTeamMembers = $plan['features']['max_team_members'] ?? 1;

            // If max_team_members is not unlimited, limit the number of team members displayed
            if ($maxTeamMembers !== 'unlimited' && $maxTeamMembers > 0) {
                $query->limit($maxTeamMembers);
            }
        }

        return $query;
    }

    /**
     * Get the tasks for the user.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all tasks, including those of the parent user or child users.
     */
    public function allTasks()
    {
        if ($this->parent_id) {
            // Child account: see own tasks and parent's tasks
            return Task::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own tasks and all children's tasks
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Task::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->tasks();
    }

    /**
     * Get the measurements for the user.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    /**
     * Get all measurements, including those of the parent user or child users.
     */
    public function allMeasurements()
    {
        if ($this->parent_id) {
            // Child account: see own measurements and parent's measurements
            return Measurement::where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('user_id', $this->parent_id);
            });
        } else {
            // Parent account: see own measurements and all children's measurements
            $childrenIds = $this->children()->pluck('id')->toArray();
            if (!empty($childrenIds)) {
                return Measurement::where(function ($query) use ($childrenIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $childrenIds);
                });
            }
        }

        return $this->measurements();
    }

    /**
     * Get the currency symbol for the user's selected currency.
     */
    public function getCurrencySymbol(): string
    {
        return match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'NGN' => '₦',
            default => '$',
        };
    }

    /**
     * Get the role that owns the user.
     */
    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the parent user.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get the child users.
     */
    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Check if the user has the given role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        // Check string role for backward compatibility
        if ($this->role === $roleName) {
            return true;
        }

        // Check role relationship
        return $this->role_id && $this->roleRelation->name === $roleName;
    }

    /**
     * Check if the user has the given permission through their role.
     *
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        // If no role_id, check if user has admin role string for backward compatibility
        if (!$this->role_id && isset($this->attributes['role']) && $this->attributes['role'] === 'admin') {
            return true;
        }

        // For new users with no role_id and no string role, return false
        if (!$this->role_id && (!isset($this->attributes['role']) || empty($this->attributes['role']) || is_null($this->attributes['role']))) {
            return false;
        }

        // If role_id exists but we can't load the role relationship, return false
//        try {
//            if ($this->role_id && (!$this->relationLoaded('roleRelation') || is_null($this->getRelation('roleRelation')))) {
//               dd($this->roleRelation);
//                return false;
//            }
//        } catch (\Throwable $e) {
//            // If any error occurs when checking the relationship, return false
//            return false;
//        }

        // Check if user has permission through their role
        // First ensure that role_id exists and the role relationship is loaded
        try {
            if (str_contains($permissionName, '|')) {
                $permissions = explode('|', $permissionName);
                foreach ($permissions as $permission) {
                    if ($this->roleRelation->permissions->contains('name', $permission)) {
                        return $this->role_id && $this->roleRelation && $this->roleRelation->permissions && $this->roleRelation->permissions->contains('name', $permission);
                    }
                }
            }
            return $this->role_id && $this->roleRelation && $this->roleRelation->permissions && $this->roleRelation->permissions->contains('name', $permissionName);
        } catch (\Throwable $e) {
            // If any error occurs during permission check, return false
            return false;
        }
    }

    /**
     * Check if the user needs to complete the onboarding process.
     * Only parent accounts need to complete onboarding.
     *
     * @return bool
     */
    public function needsOnboarding(): bool
    {
        // Only parent accounts need onboarding
        if ($this->parent_id) {
            return false;
        }

        // Check if onboarding has been completed
        return !$this->onboarding_completed;
    }

    /**
     * Get the business details associated with the user.
     */
    public function businessDetail()
    {
        return $this->hasOne(BusinessDetail::class);
    }

    /**
     * Generate a unique booking hash for the user.
     * This is used for public appointment booking URLs.
     *
     * @return string
     */
    public function generateBookingHash(): string
    {
        $hash = \Illuminate\Support\Str::random(32);
        $this->update(['booking_hash' => $hash]);
        return $hash;
    }

    /**
     * Get the booking hash for the user, generating one if it doesn't exist.
     * Only parent accounts can have booking hashes.
     *
     * @return string|null
     */
    public function getBookingHash(): ?string
    {
        // Only parent accounts can have booking hashes
        if ($this->parent_id) {
            return null;
        }

        // Generate a hash if one doesn't exist
        if (empty($this->booking_hash)) {
            return $this->generateBookingHash();
        }

        return $this->booking_hash;
    }

    /**
     * Get the business name slug for the user.
     * This is used for public URLs.
     *
     * @return string|null
     */
    public function getBusinessSlug(): ?string
    {
        // Only parent accounts can have business slugs
        if ($this->parent_id) {
            return null;
        }

        // Get business name from business details
        $businessDetail = $this->businessDetail;
        $businessName = $businessDetail ? $businessDetail->business_name : $this->name;

        if (empty($businessName)) {
            return null;
        }

        // Create slug from business name and append user ID
        $slug = \Illuminate\Support\Str::slug($businessName) . '_' . $this->id;

        return $slug;
    }

    /**
     * Get the public booking URL for the user.
     *
     * @return string|null
     */
    public function getBookingUrl(): ?string
    {
        $slug = $this->getBusinessSlug();

        if (!$slug) {
            return null;
        }

        return route('appointments.public.booking', ['slug' => $slug]);
    }

    /**
     * Get the public business profile URL for the user.
     *
     * @return string|null
     */
    public function getBusinessProfileUrl(): ?string
    {
        $slug = $this->getBusinessSlug();

        if (!$slug) {
            return null;
        }

        return route('business.public', ['slug' => $slug]);
    }
}
