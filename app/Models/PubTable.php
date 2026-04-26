<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PubTable extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pub_tables';

    protected $fillable = [
        'table_number',
        'capacity',
        'location',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];
}