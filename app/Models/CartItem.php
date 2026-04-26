<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class CartItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'cart_items';

    protected $fillable = [
        'cart_id',
        'menu_item_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
        'subtotal' => 'float',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
}