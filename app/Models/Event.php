<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Event extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'events';

    protected $fillable = [
        'title',
        'description',
        'image',
        'event_date',
        'start_time',
        'end_time',
        'status',
    ];
}