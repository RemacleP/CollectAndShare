<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LienUtile extends Model
{
    protected $table = 'liens_utiles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nom',
        'url',
    ];
}
