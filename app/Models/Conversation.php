<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    // Important pour permettre la création via le Seeder ou Controller
    protected $fillable = ['club_id', 'title', 'slug', 'is_private'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function canBeSeenBy(User $user)
    {
        // Le responsable voit tout
        if ($user->role === 'admin_club') return true;

        // Sinon, on vérifie si l'utilisateur est dans la conversation
        return $this->users()->where('user_id', $user->id)->exists();
    }
}
