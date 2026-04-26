<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class StaffShift extends Model
{
    protected $collection = 'staff_shifts';
    protected $connection = 'mongodb';
    protected $fillable = [
        'user_id',
        'shift_date',
        'start_time',
        'end_time',
        'shift_role',
        'status'
    ];

    public function user()
    {
        // MongoDB stores _id as ObjectId; user_id is stored as string
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

}
