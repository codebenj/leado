<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadComment extends Model
{
    protected $fillable = [
        'user_id', 'lead_id', 'comment', 'created_at', 'updated_at'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function lead() {
        return $this->belongsTo('App\Lead');
    }
}
