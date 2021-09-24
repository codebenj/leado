<?php

namespace App;

use App\Model;

class OrgLocator extends Model
{
    protected $fillable = [
        'name',
        'org_id',
        'street_address',
        'suburb',
        'postcode',
        'state',
        'phone',
        'last_year_sales',
        'year_to_date_sales',
        'keeps_stock',
        'pricing_book',
        'priority',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $guarded = [];

    public function scopeGetOrgLocator($query, $request){
        // note: i think distance its not applicable since its global not just austrilia

        // $kilometer = (isset($request['kilometer']) && !empty($request['kilometer'])) ? $request['kilometer'] : 500;
        // $postcode = (!empty($request['postcode'])) ? $request['postcode'] : '*';

        // $url = 'https://www.leafstopper.com.au/wpv1/locator/distance.php?postcode='.$postcode.'&distance='.$kilometer;
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_URL,$url);
        // $result=curl_exec($ch);
        // curl_close($ch);

        // $distance = json_decode($result, true);
        // $count = count($distance);
        // $topc = [];

        // for( $i = 0; $i < $count; $i++ ){
        //     $topc[] = $distance[$i]['topc'];
        // }

        // return $query->where(function ($query) use($request, $topc){

            // if(isset($request['org_id']) && !empty($request['org_id'])){
            //     $query->where('org_id', $request['org_id']);
            // }

            // if(isset($request['name']) && !empty($request['name'])){
            //     $query->where('name', 'LIKE', '%'.$request['name'].'%');
            // }

            // if(isset($request['postcode']) && !empty($request['postcode'])){
            //     if(isset($request['kilometer']) && !empty($request['kilometer'])){
            //         $query->whereIn('postcode', $topc);
            //     }else{
            //         $query->where('postcode', $request['postcode']);
            //     }
            // }

            // if(isset($request['ytd_sale']) && !empty($request['ytd_sale'])){
            //     $query->where('year_to_date_sales', '>', $request['ytd_sale']);
            // }

            // if(isset($request['ly_sale']) && !empty($request['ly_sale'])){
            //     $query->where('last_year_sales', '>', $request['ly_sale']);
            // }

            // if(isset($request['priority']) && !empty($request['priority'])){
            //     $query->where('priority', 'like', '%'.$request['priority'].'%');
            // }

            // if(isset($request['stock_kits']) && !empty($request['stock_kits'])){
            //     $query->where('keeps_stock', 'like', '%'.$request['stock_kits'].'%');
            // }

            // if(isset($request['search']) && !empty($request['search'])){
            //     $query->orWhere('org_id', $request['search']);
            //     $query->orWhere('name', 'LIKE', '%'.$request['search'].'%');
            //     $query->orWhere('year_to_date_sales', '>', $request['search']);
            //     $query->orWhere('last_year_sales', '>', $request['search']);
            //     $query->orWhere('priority', 'like', '%'.$request['search'].'%');
            //     $query->orWhere('keeps_stock', 'like', '%'.$request['search'].'%');
            //     $query->orWhere('suburb', 'like', '%'.$request['search'].'%');
            //     $query->orWhere('postcode', $request['search']);
            //     $query->orWhereIn('postcode', $topc);
            // }
        // });

        if(!empty($request['search'])){
            if(is_numeric($request['search'])){
                $query->orWhere('org_id', $request['search']);
                $query->orWhere('year_to_date_sales', '>', $request['search']);
                $query->orWhere('last_year_sales', '>', $request['search']);
                $query->orWhere('postcode', $request['search']);
                $query->orWhere('phone', 'like', '%'.$request['search'].'%');
            }else{
                $query->orWhere('name', 'like', '%'.$request['search'].'%');
                $query->orWhere('suburb', 'like', '%'.$request['search'].'%');
                $query->orWhere('keeps_stock', 'like', '%'.$request['search'].'%');
                $query->orWhere('priority', 'like', '%'.$request['search'].'%');
                $query->orWhere('street_address', 'like', '%'.$request['search'].'%');
                $query->orWhere('suburb', 'like', '%'.$request['search'].'%');
            }

            return $query;
        }else{
            if(isset($request['keyword']) && !empty($request['keyword'])){
                if(is_numeric($request['keyword'])){
                    $query->orWhere('org_id', $request['keyword']);
                    $query->orWhere('phone', 'like', '%'.$request['keyword'].'%');
                }else{
                    $query->orWhere('name', 'like', '%'.$request['keyword'].'%');
                    $query->orWhere('suburb', 'like', '%'.$request['keyword'].'%');
                    $query->orWhere('street_address', 'like', '%'.$request['keyword'].'%');
                }
            }

            if(isset($request['postcode']) && !empty($request['postcode'])){
                //$query->where('postcode', $request['postcode']);
                $query->Where('suburb', 'like', '%'.$request['postcode'].'%');
            }

            if(isset($request['ytd_sale']) && !empty($request['ytd_sale'])){
                $query->where('year_to_date_sales', '>', $request['ytd_sale']);
            }

            if(isset($request['ly_sale']) && !empty($request['ly_sale'])){
                $query->where('last_year_sales', '>', $request['ly_sale']);
            }

            if(isset($request['priority']) && !empty($request['priority'])){
                $query->where('priority', 'like', '%'.$request['priority'].'%');
            }

            if(isset($request['stock_kits']) && !empty($request['stock_kits'])){
                $query->where('keeps_stock', 'like', '%'.$request['stock_kits'].'%');
            }

            if(isset($request['state']) && !empty($request['state'])){
                $query->where('state', $request['state']);
            }

            return $query;
        }
    }
}
