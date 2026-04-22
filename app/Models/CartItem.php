<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'element_id', 'quantity'];

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
