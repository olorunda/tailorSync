<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        // Allow if user is the direct owner
        if ($user->id === $client->user_id) {
            return true;
        }

        // Allow child users to view parent's clients
        if ($user->parent_id && $client->user_id === $user->parent_id) {
            return true;
        }

        // Allow parent users to view children's clients
        if (!$user->parent_id) {
            $childrenIds = $user->children()->pluck('id')->toArray();
            if (in_array($client->user_id, $childrenIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        // Allow if user is the direct owner
        if ($user->id === $client->user_id) {
            return true;
        }

        // Allow child users to update parent's clients
        if ($user->parent_id && $client->user_id === $user->parent_id) {
            return true;
        }

        // Allow parent users to update children's clients
        if (!$user->parent_id) {
            $childrenIds = $user->children()->pluck('id')->toArray();
            if (in_array($client->user_id, $childrenIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        // Allow if user is the direct owner
        if ($user->id === $client->user_id) {
            return true;
        }

        // Allow child users to delete parent's clients
        if ($user->parent_id && $client->user_id === $user->parent_id) {
            return true;
        }

        // Allow parent users to delete children's clients
        if (!$user->parent_id) {
            $childrenIds = $user->children()->pluck('id')->toArray();
            if (in_array($client->user_id, $childrenIds)) {
                return true;
            }
        }

        return false;
    }
}
