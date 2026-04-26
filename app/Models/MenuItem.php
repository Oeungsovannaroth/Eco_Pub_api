<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MenuItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'menu_items';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock_qty',
        'image',
        'is_available',
        'status',
    ];

    protected $casts = [
        'price' => 'float',
        'stock_qty' => 'integer',
        'is_available' => 'boolean',
    ];
    protected $appends = ['image_url'];
      public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        // if already full URL
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
     public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'menu_item_id');
    }
}