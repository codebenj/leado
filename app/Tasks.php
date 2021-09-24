<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{


    protected $table_name = 'tasks';

    protected $fillable = [
        'name',
        'user_ids',
        'status_id',
        'created_at'
    ];

    protected $casts = [
        'user_ids' => 'array'
    ];

    public function status() {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }
}
