<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;


class Collection extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'description', 'club_id', 'club_user_id', 'image', 'slug'];
    public $timestamps = true;

// La collection appartient à un membre d'un club (pivot club_user_role)
    public function clubUser()
    {
        return $this->belongsTo(ClubUserRole::class, 'club_user_id');
    }

    // Relation indirecte pour obtenir l'utilisateur directement
    public function user()
    {
        // On passe par club_user_role pour atteindre l'utilisateur
        return $this->clubUser->user();
    }
    public function elements()
    {
        return $this->hasMany(Element::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_collection');
    }

    public function club_user_role()
    {
        return $this->belongsTo(ClubUserRole::class, 'club_user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($collect) {
            $collect->slug = Str::slug($collect->name);
        });

        // Add id to the slug
        static::created(function ($collect) {
                $collect->slug = Str::slug($collect->name) . '-' . $collect->id;
                $collect->saveQuietly();
        });

        // If title is updated, update the slug
        static::updating(function ($collect) {
            if ($collect->isDirty('name')) {
                $collect->slug = Str::slug($collect->name) . '-' . $collect->id;
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }




}
