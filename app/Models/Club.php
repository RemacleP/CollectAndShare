<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Club extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'email', 'phone', 'logo'];

    /**
     * Boot du modèle pour gérer le slug automatiquement
     */
    protected static function booted()
    {
        static::creating(function ($club) {
            if (empty($club->slug)) {
                $club->slug = Str::slug($club->name);
            }
        });

        static::updating(function ($club) {
            // Optionnel : mettre à jour le slug si le nom change
            if ($club->isDirty('name')) {
                $club->slug = Str::slug($club->name);
            }
        });
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'club_user_role')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
