<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Un utilisateur peut créer s'il est admin global
     * OU s'il a un rôle de gestionnaire dans n'importe quel club.
     */
    public function create(User $user): bool
    {
        return $user->is_admin || $user->roles()->whereIn('roles.name', ['president', 'responsable'])->exists();
    }

    /**
     * Un utilisateur peut modifier s'il est admin global
     * OU s'il possède le bon rôle spécifiquement pour le club de l'événement.
     */
    public function update(User $user, Event $event): bool
    {
        if ($user->is_admin) {
            return true;
        }

        // On utilise ta méthode hasRole en passant l'ID du club de l'event
        return $user->hasRole('responsable', $event->club_id);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
