<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'description', 'is_active', 'club_user_id',];

    public function elements()
    {
        return $this->belongsToMany(Element::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'category_collection');
    }

    public function owners()
    {
        return $this->belongsToMany(ClubUserRole::class, 'category_club_user', 'category_id', 'club_user_id');
    }

}
