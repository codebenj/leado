<?php

namespace App;

use App\Model;

class Country extends Model
{
    protected $table_name = 'countries';
    protected $fillable = ['name', 'code', 'created_at', 'updated_at'];
    public $timestamps = true;
}
