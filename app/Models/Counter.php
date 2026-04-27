<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Counter extends Model
{
    protected $collection = 'counters';
    protected $connection = 'mongodb';

    protected $fillable = ['_id', 'seq'];
}
