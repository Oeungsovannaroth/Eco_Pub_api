<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Reservation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'reservations';

    protected $fillable = [
        'customer_name',
        'table_id',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'status',
        'special_request',
    ];

    protected $casts = [
        'guest_count' => 'integer',
    ];

    public function table()
    {
        return $this->belongsTo(PubTable::class, 'table_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'reservation_id');
    }
}