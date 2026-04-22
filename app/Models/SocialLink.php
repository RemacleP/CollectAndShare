<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = ['social_platform_id', 'identifier', 'full_url', 'linkable_id', 'linkable_type'];

    public function platform()
    {
        return $this->belongsTo(SocialPlatform::class, 'social_platform_id');
    }

    public function linkable()
    {
        return $this->morphTo();
    }
}
