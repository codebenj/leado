<?php

namespace App;

use App\Model;

class LeadWebForm extends Model
{
    protected $guarded = [];

    protected $table = 'lead_webforms';

    protected $appends = [
        'is_special_opportunity'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function getIsSpecialOpportunityAttribute(){
        if(!empty($this->commercial) || $this->house_type == 'Commercial' || $this->house_type == 'Townhouses/Villas'){
            return true;
        }
        return false;
    }
}
