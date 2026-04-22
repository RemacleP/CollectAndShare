<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;

class CollectionPolicy
{
    /**
     * L'admin a tous les droits par défaut (Optionnel si défini dans AuthServiceProvider)
     */
    public function before(User $user, $ability)
    {
        if ($user->is_admin) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return true; // Tout le monde peut voir la liste
    }

    public function view(?User $user, Collection $collection): bool
    {
        return true; // Tout le monde peut voir une collection
    }

    public function update(User $user, Collection $collection): bool
    {
        // 1. On vérifie si l'utilisateur est le propriétaire via club_user_role
        // On utilise la relation chargée dans le contrôleur : club_user_role
        if ($collection->club_user_role && $collection->club_user_role->user_id === $user->id) {
            return true;
        }

        // 2. Le responsable du club a aussi le droit (si ta méthode hasRole existe)
        // Sinon : $user->clubs()->where('club_id', $collection->club_id)->where('role', 'responsable')->exists();
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('responsable', $collection->club_id);
        }

        return false;
    }

    public function delete(User $user, Collection $collection): bool
    {
        // Généralement, mêmes droits que pour l'update
        return $this->update($user, $collection);
    }
}
