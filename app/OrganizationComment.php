<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganizationComment extends Model
{
    protected $fillable = [
        'user_id', 'organisation_id', 'comment', 'created_at', 'updated_at'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function organisation() {
        return $this->belongsTo('App\Organisation');
    }
}
