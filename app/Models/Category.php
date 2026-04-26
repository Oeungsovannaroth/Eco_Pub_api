<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\MenuItem;
use App\Models\User;

class Category extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';

    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'created_by',
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}