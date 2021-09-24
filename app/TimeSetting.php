<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeSetting extends Model
{
    protected $fillable = [
        'name', 
        'type', // recurring or one time,
        'start_date',
        'start_time',
        'stop_date',
        'stop_time',
        'start_day',
        'stop_day',
        'is_active',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array'
    ];
}
