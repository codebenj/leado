<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    //use SoftDeletes;

    protected $fillable = [
        'name', 'street_address', 'suburb', 'state', 'postcode', 'phone_number', 'contact', 'keep_stock', 'code', 'last_year_sales', 'year_to_date_sales',
        'pricing_book', 'priority', 'stock_kits'
    ];

    public function enquirer() {
        return $this->hasOne('App\Enquiries');
    }

    public function scopeFilter($query, $request){
        $max_distance = $request['distance'] ?? '500';
        $postcode = $request['postcode'] ?? 2766;

        $query->join('distances', function($join) use($postcode, $max_distance){
            $join->on('frompc', '=', DB::raw("'".$postcode."'"));
            $join->on('distance', '<=', DB::raw("'".$max_distance."'"));
            $join->on('topc', '=', 'stores.postcode');
        });

        if(isset($request['code']) && !empty($request['code'])){
            $query->where('code', $request['code']);
        }

        if(isset($request['name']) && !empty($request['name'])){
            $query->where('name', 'like', "{$request['name']}");
        }

        if(isset($request['last_year_sales']) && !empty($request['last_year_sales'])){
            $query->where('last_year_sales', '>=', "{$request['last_year_sales']}");
        }

        if(isset($request['year_to_date_sales']) && !empty($request['year_to_date_sales'])){
            $query->where('year_to_date_sales', '>=', "{$request['year_to_date_sales']}");
        }

        if(isset($request['priority']) && !empty($request['priority'])){
            $query->where('priority', 'like', "%{$request['priority']}%");
        }

        if(isset($request['stock_kits']) && !empty($request['stock_kits'])){
            $query->where('stock_kits', 'like', "%{$request['stock_kits']}%");
        }

        if(isset($request['state']) && !empty($request['state'])){
            $query->where('state', 'like', "%{$request['state']}%");
        }

        if(isset($request['suburb']) && !empty($request['suburb'])){
            $query->where('suburb', 'like', "%{$request['suburb']}%");
        }

        if(isset($request['enquirer_email']) && !empty($request['enquirer_email'])){
            $query->whereHas('enquirer', function($q) use($request){
                $q->where('email_enquirers', 'like', "%{$request['enquirer_email']}%");
            });
        }

        return $query;
    }
}
