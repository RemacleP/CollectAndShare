<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{

    protected $table = 'registration_requests';

    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'type',
        'club_id',
        'new_club_name',
        'message',
        'status',
    ];
}
