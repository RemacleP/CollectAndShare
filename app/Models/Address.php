<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    protected $fillable = [
        'type',
        'street',
        'number',
        'box',
        'postal_code',
        'city',
        'country',
        'addressable_type',
        'addressable_id',
    ];

    /**
     * Récupère le modèle propriétaire (User ou Club).
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
