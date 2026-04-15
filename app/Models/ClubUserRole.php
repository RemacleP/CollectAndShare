<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClubUserRole extends Pivot
{
    protected $table = 'club_user_role';
    public $incrementing = true; // Crucial pour que l'ID soit reconnu

    public function user() { return $this->belongsTo(User::class); }
    public function club() { return $this->belongsTo(Club::class); }
}
