<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['status', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function elements()
    {
        return $this->belongsToMany(Element::class, 'element_carts', 'cart_id', 'element_id')
            ->withPivot('price_at_add', 'quantity');
    }
}
