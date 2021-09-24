<?php

namespace App;

use App\Model;

class OrganizationUser extends Model
{
    protected $table_name = 'organization_users';
    protected $fillable = [
        'user_id', 'organisation_id'
    ];
    public $timestamps = true;

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function organisation() {
        return $this->belongsTo('App\Organisation');
    }
}
