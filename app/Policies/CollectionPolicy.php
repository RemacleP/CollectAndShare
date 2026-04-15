<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CollectionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Collection $collection): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Collection $collection): bool
    {
        // 1. Le Super-Admin peut tout faire
        if ($user->is_admin) return true;

        // 2. Le créateur de la collection est propriétaire
        // On vérifie via la relation club_user qui lie l'user à la collection
        if ($collection->club_user && $collection->club_user->user_id === $user->id) {
            return true;
        }

        // 3. Le responsable/président du club auquel appartient la collection
        return $user->hasRole('responsable', $collection->club_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Collection $collection): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Collection $collection): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Collection $collection): bool
    {
        return false;
    }
}
