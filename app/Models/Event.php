<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'start_datetime', 'end_datetime','location_name', 'address', 'city',
        'country', 'price', 'status', 'registration_required', 'registration_deadline', 'image',
        'club_id', 'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->slug = Str::slug($event->title);
        });

        // Add id to the slug
        static::created(function ($event) {
            $event->slug = Str::slug($event->title) . '-' . $event->id;
            $event->saveQuietly();
        });

        // If title is updated, update the slug
        static::updating(function ($event) {
            if ($event->isDirty('title')) {
                $event->slug = Str::slug($event->title) . '-' . $event->id;
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('registration_date', 'status', 'role', 'notes');
    }

}
