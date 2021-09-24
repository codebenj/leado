<?php

namespace App;

use App\Model;

class OrganizationPostcode extends Model
{
    protected $table = 'organisation_postcodes';

    protected $fillable = [
        'postcode', 'organisation_id'
    ];

    public $timestamps = true;

    public function organisation() {
        return $this->belongsTo('App\Organisation');
    }
}
