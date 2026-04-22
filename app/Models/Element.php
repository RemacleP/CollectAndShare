<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Element extends Model

{
    public $timestamps = true;
    protected $fillable = [
        'label',
        'description',
        'year_production',
        'history',
        'image',
        'condition',
        'is_for_trade',
        'is_for_sale',
        'price',
        'quantity',
        'slug',
        'collection_id'
    ];

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

// Optionnel mais pratique : une relation directe vers l'utilisateur
    public function user()
    {
        return $this->collection->user(); // Si Collection a une relation user()
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'element_carts', 'element_id', 'cart_id')
            ->withPivot('price_at_add');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class) // To check
            ->withPivot('quantity', 'price_at_order');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($element) {
            $element->slug = Str::slug($element->slug);
        });

        // Add id to the slug
        static::created(function ($element) {
            if(!$element->slug) {
                $element->slug = Str::slug($element->label) . '-' . $element->id;
                $element->saveQuietly();
            }
        });

        // If title is updated, update the slug
        static::updating(function ($element) {
            if ($element->isDirty('label')) {
                $element->slug = Str::slug($element->label) . '-' . $element->id;
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function images()
    {
        return $this->hasMany(ElementImage::class);
    }
}
