<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Banner extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'banners';

    protected $fillable = [
        'title',
        'image',
        'link',
        'status',
    ];
}