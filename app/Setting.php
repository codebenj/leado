<?php

namespace App;

use App\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'name',
        'value',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    public function scopeGetSetting($query, $request){
        return $query->where(function ($q) use($request){
            if(isset($request['search']) && !empty($request['search'])){
                $q->orWhere('name', 'like' , '%'.$request['search'].'%');
                $q->orWhere('key', 'like' , '%'.$request['search'].'%');
                $q->orWhere('metadata', 'like' , '%'.$request['search'].'%');
            }
        });
    }


}
