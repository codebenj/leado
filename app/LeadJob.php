<?php

namespace App;

use App\Model;

class LeadJob extends Model
{
    protected $fillable = [
        'lead_id',
        'organisation_id',
        'sale',
        'meters_gutter_edge',
        'meters_valley',
        'comments',
        'created_at',
        'updated_at'
    ];

    public function organisation() {
        return $this->belongsTo('App\Organisation');
    }
}
