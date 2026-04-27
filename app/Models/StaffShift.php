<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class StaffShift extends Model
{
    protected $collection = 'staff_shifts';
    protected $connection = 'mongodb';

    protected $fillable = [
        'user_id',      // This will be auto-generated like "STAFF-0001"
        'name',
        'shift_date',
        'start_time',
        'end_time',
        'shift_role',
        'status'
    ];

    // Auto generate user_id before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shift) {
            if (empty($shift->user_id)) {
                $shift->user_id = self::generateNextUserId();
            }
        });
    }

    private static function generateNextUserId(): string
    {
        $counter = Counter::firstOrCreate(
            ['_id' => 'staff_user_id'],
            ['seq' => 0]
        );

        $counter->increment('seq');
        $next = $counter->seq;

        // Format: STAFF-0001, STAFF-0002, ...
        return 'STAFF-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}
