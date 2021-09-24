<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignUserToLead extends Model
{
    protected $fillable = [
        'user_id', 'created_at', 'updated_at'
    ];

    // public function user() {
    //     return $this->belongsTo('App\User');
    // }

    // public function lead() {
    //     return $this->belongsTo('App\Lead');
    // }
}
