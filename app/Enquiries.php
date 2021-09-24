<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enquiries extends Model
{
    protected $fillable = [
        'store_id', 'email_enquirers', 'message'
    ];

    protected $table = 'enquiries';

    public function store() {
        return $this->belongsTo('App\Store');
    }

    public function scopeFilter($query, $request){
        if(isset($request['code']) && !empty($request['code'])){
            $query->whereHas('store', function($q) use($request){
                $q->where('code', 'like', "%{$request['code']}%");
            });
        }

        if(isset($request['name']) && !empty($request['name'])){
            $query->whereHas('store', function($q) use($request){
                $q->where('name', 'like', "%{$request['name']}%");
            });
        }

        if(isset($request['state']) && !empty($request['state'])){
            $query->whereHas('store', function($q) use($request){
                $q->where('state', $request['state']);
            });
        }

        if(isset($request['suburb_postcode']) && !empty($request['suburb_postcode'])){
            $query->whereHas('store', function($q) use($request){
                $q->orWhere('postcode', 'like', "%{$request['suburb_postcode']}%");
                $q->orWhere('suburb', 'like', "%{$request['suburb_postcode']}%");
            });
        }

        if(isset($request['enquirer_email']) && !empty($request['enquirer_email'])){
            $query->where('email_enquirers', 'like', "%{$request['enquirer_email']}%");
        }

        return $query;
    }
}
