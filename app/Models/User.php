<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['username', 'firstname', 'lastname', 'email', 'password', 'phone','is_banned', 'eid_number'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Adresse principale
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * Adresse de livraison (Lot Panier)
     */
    public function addresses(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function shippingAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'shipping');
    }

    public function billingAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'billing');
    }

    /**
     * Relation avec les rôles, en incluant le club_id stocké dans la table pivot
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'club_user_role')
            ->withPivot('club_id')
            ->withTimestamps();
    }

    /**
     * Récupérer tous les clubs auxquels l'utilisateur appartient
     */
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_user_role')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Vérifie si l'utilisateur est banni ou bloqué (Brute Force)
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Vérifie si l'utilisateur a un rôle précis dans UN club précis
     */
    public function hasRoleInClub(string $roleName, int $clubId): bool
    {
        // Le Superadmin a tous les droits partout
        if ($this->is_superadmin) return true;

        return $this->roles()
            ->where('name', $roleName)
            ->wherePivot('club_id', $clubId)
            ->exists();
    }

    /**
     * Vérifie si l'utilisateur a lié son e-ID
     */
    public function hasVerifiedIdentity(): bool
    {
        return !empty($this->eid_number);
    }

    public function hasRole(string $roleName, $clubId = null): bool
    {
        if ($clubId) {
            // Vérifie si l'utilisateur est responsable de CE club précis
            return $this->roles()
                ->where('roles.name', $roleName)
                ->where('club_user_role.club_id', $clubId)
                ->exists();
        }

        // Vérifie si l'utilisateur a le rôle admin (globalement)
        // Dans ton SQL, Pascal (ID 1) est admin.
        return $this->roles()->where('roles.name', $roleName)->exists();
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('role')
            ->withTimestamps();
    }

}
