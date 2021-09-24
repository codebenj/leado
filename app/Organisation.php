<?php

namespace App;

use App\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'address_id',
        'contact_number',
        'landline_contact',
        'additional_details',
        'state',
        'org_code',
        'is_suspended',
        'org_status',
        'metadata',
        'notifications',
        'is_available',
        'available_when',
        'reason',
        'on_hold',
        'priority',
        'price'
    ];

    protected $appends = [
        'has_active_lead',
        'pending_leads',
        'active_lead_id',
        'admin_notified',
        'account_status_type',
        'account_status_type_selection',
        'has_postcodes',
        'has_critical',
        'system_status'
    ];

    protected $casts = [
        'metadata' => 'array',
        'notifications' => 'array',
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function getHasPostcodesAttribute($value){
        if($this->postcodes()->count() == 0){
            return false;
        }
        return true;
    }

    public function getSystemStatusAttribute($value){
        if($this->has_critical){
            return 'On Hold';
        }
        return 'On';
    }

    public function getAccountStatusTypeSelectionAttribute($value){
        $status = '';
        $is_hold = 0;
        $today = Date('Y-m-d');
        if($this->is_available == 0 && ($this->available_when > $today) ){
            $is_hold = 1;
            $status .= ' Org | ';
        }

        if($this->on_hold == 1){
            $is_hold = 1;
            $status .= ' Admin | ';
        }

        if($this->getAdminNotifiedAttribute() > 0){
            $is_hold = 1;
            $status .= ' Sys | ';
        }

        if($is_hold == 1){
            $status = substr($status, 0, strlen($status) - 2);
            $status = 'On Hold - '.$status;
        }

        $status = preg_replace('/\s+/', ' ', $status);
        return $status;
    }

    public function getAccountStatusTypeAttribute($value){
        $status_type = ((int)$this->is_suspended) ? 'Suspended' : 'Unsuspended';

        if((int)$this->is_suspended){
            $type = $this->metadata['suspension_type'] ?? 'System';
        }else{
            $low_priority = $this->metadata['low_priority'] ?? false;
            $type = $low_priority ? 'Low Priority' : '';
        }

        if(isset($type) && !empty($type)){
            $status_type .= ' | '.$type;
        }

        return $status_type;
    }

    public function getHasCriticalAttribute(){
        $leads = $this->hasMany('App\LeadEscalation')->where(['is_active' => 1])->whereIn('escalation_status', ['Declined-Lapsed', 'Awaiting Response - Admin Notified'])->get();
        if(count($leads) > 0){
            return true;
        }
        return false;
    }

    public function getAdminNotifiedAttribute(){
        return $this->hasMany('App\LeadEscalation')->where(['is_active' => 1])->whereIn('escalation_status', ['Awaiting Response - Admin Notified'])->count();
    }

    public function getHasActiveLeadAttribute($value)
    {
        return $this->hasMany('App\LeadEscalation')->where(['is_active' => 1])->whereNotIn('escalation_status', ['Won', 'Lost'])->first() !== null;
    }

    public function getActiveLeadIdAttribute( $value ) {
        $lead = $this->hasMany( 'App\LeadEscalation' )->where( 'is_active', 1 )->first();

        return ( $lead ) ? $lead->lead_id : 0;
    }

    public function postcodes(){
        return $this->hasMany('App\OrganizationPostcode');
    }

    public function address() {
        return $this->belongsTo('App\Address');
    }

    public function organisation_users() {
        return $this->hasMany('App\OrganizationUser');
    }

    public function lead_escalations(){
        return $this->hasMany('App\LeadEscalation');
    }

    public function active_escalation(){
        return $this->hasMany('App\LeadEscalation')
            ->where('is_active', 1);
    }

    public function getPendingLeadsAttribute(){
        //return $this->active_escalation()->whereNotIn('escalation_status', ['Discontinued', 'Lost', 'Won', 'Abandoned', 'Declined', 'Inconclusive'])->count();
        $count = 0;
        $lead_escalations = $this->active_escalation()->whereNotIn('escalation_status', ['Discontinued', 'Lost', 'Won', 'Abandoned', 'Declined', 'Inconclusive'])->get();
        foreach($lead_escalations as $lead_escalation){
            if($lead_escalation->lead->customer_type == 'Supply & Install'){
                $count++;
            }
        }
        return $count;
    }

    public function scopeGetOrganisation( $query, $request ) {
        if ( isset( $request['org_status'] ) && ! empty( $request['org_status'] ) ) {
            $org_status = $request['org_status'] == 'active' ? true : false;
            $query->where( 'org_status', $org_status );
        }

        if ( isset( $request['org_suspended'] ) && ! empty( $request['org_suspended'] ) ) {
            $org_suspended = $request['org_suspended'] == 'suspended' ? 1 : 0;
            $query->where( 'is_suspended', $org_suspended );
        }

        if ( isset( $request['org_type'] ) && ! empty( $request['org_type'] ) ) {
            $org_type = $request['org_type'] == 'auto' ? false : true;
            $query->where( 'metadata->manual_update', $org_type );
        }

        if ( isset( $request['org_postcode'] ) && ! empty( $request['org_postcode'] ) ) {
            $query->whereHas( 'address', function( $q ) use ( $request ) {
                $q->where( 'postcode', 'LIKE', '%' . $request['org_postcode'] . '%' );
                $q->orWhere( 'suburb', 'LIKE', '%' . $request['org_postcode'] . '%' );
            } );
        }

        if ( isset( $request['org_state'] ) && ! empty( $request['org_state'] ) ) {
            $query->whereHas( 'address', function( $q ) use ( $request ) {
                $q->where( 'state', 'LIKE', '%' . $request['org_state'] . '%' );
            } );
        }

        if ( isset( $request['search'] ) && ! empty( $request['search'] ) ) {
            $query->where( function( $q ) use( $request ) {
                $q->orWhere( 'org_code', 'like' , '%' . $request['search'] . '%' );
                $q->orWhere( 'name', 'like' , '%' . $request['search'] . '%' );
                $q->orWhere( 'contact_number', 'like' , '%' . $request['search'] . '%' );
            });

            $query->orWhereHas( "address.country", function( $q ) use( $request ) {
                $q->where( 'state', 'like' , '%' . $request['search'] . '%' );
            });

            $query->orWhereHas( "address", function( $q ) use( $request ) {
                $q->where( 'postcode', 'like', '%' . $request['search'] . '%' );
            });

            $query->orWhereHas( "user", function( $q ) use( $request ){
                $q->where( 'email', 'like', '%' . $request['search'] . '%' );
            });
        }

        return $query;
    }

    public function comments() {
        return $this->hasMany('App\OrganizationComment');
    }

}
