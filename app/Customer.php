<?php

namespace App;

use App\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $table_name = 'customers';

    protected $fillable = [
        'email', 'first_name', 'last_name', 'contact_number', 'address_id'
    ];

    public $timestamps = true;

    public function address() {
        return $this->belongsTo('App\Address');
    }

    public function lead(){
        return $this->hasOne('App\Lead');
    }

    public function scopeFilter($query, $request){
        if(isset($request['lead_type']) && !empty($request['lead_type'])){
            $query->whereHas('lead', function($q) use($request){
                $q->where('customer_type', $request['lead_type']);
            });
        }

        if(isset($request['state']) && !empty($request['state'])){
            $query->whereHas('address', function($q) use($request){
                $q->where('state', $request['state']);
            });
        }

        if(isset($request['suburb']) && !empty($request['suburb'])){
            $query->whereHas('address', function($q) use($request){
                $q->Where('suburb', $request['suburb']);
                $q->orWhere('postcode', $request['suburb']);
            });
        }

        if(isset($request['search']) && !empty($request['search'])){
            $query->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', "%{$request['search']}%");
            $query->orWhere('email', 'LIKE', "%{$request['search']}%");
        }

        return $query;
    }
}
