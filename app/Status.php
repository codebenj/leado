<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    protected $table_name = 'status';

    protected $fillable = [
        'name',
        'created_at'
    ];


    public function tasks() {
        return $this->hasMany(Tasks::class, 'status_id', 'id');
    }

}
