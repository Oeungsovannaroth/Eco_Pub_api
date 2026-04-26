<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class LedMessage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'led_messages';

    protected $fillable = [
        'title',
        'message',
        'start_date',
        'end_date',
        'status',
    ];
}