<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElementImage extends Model
{
    protected $fillable = ['path', 'element_id'];

    public function element()
    {
        return $this->belongsTo(Element::class);
    }
}
