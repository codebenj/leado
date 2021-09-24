<?php

namespace App;

use App\Model;

class Address extends Model
{
    protected $table_name = 'addresses';

    protected $fillable = [
        'address', 'city', 'suburb', 'state', 'postcode', 'country_id', 'metadata'
    ];

    protected $appends = ['full_address', 'timezone'];

    public $timestamps = true;

    protected $casts = [
        'metadata' => 'array'
    ];

    public function country() {
        return $this->belongsTo('App\Country');
    }

    public function getFullAddressAttribute() {
        $parts = [$this->address, $this->city, $this->suburb, $this->state, $this->postcode];
        $parts = array_filter($parts);

        return implode(', ', $parts);
    }

    /**
     * Get the timezone attribute.
     *
     * @return string
     */
    public function getTimezoneAttribute()
    {
        $timezones = [
            'ACT' => 'Australia/ACT',
            'NSW' => 'Australia/NSW',
            'NT' => 'Australia/North',
            'QLD' => 'Australia/Queensland',
            'SA' => 'Australia/South',
            'TAS' => 'Australia/Tasmania',
            'VIC' => 'Australia/Victoria',
            'WA' => 'Australia/West'
        ];

        return $timezones[$this->state] ?? 'Australia/Sydney';
    }
}
