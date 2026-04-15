<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Collection extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'description', 'club_id', 'club_user_id', 'image', 'slug'];
    public $timestamps = false;

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
        return $this->belongsToMany(Category::class, 'category_collections');
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
