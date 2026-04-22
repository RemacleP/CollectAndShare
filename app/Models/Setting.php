<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Indispensable pour autoriser l'écriture en base de données
    protected $fillable = ['key', 'value'];
}
