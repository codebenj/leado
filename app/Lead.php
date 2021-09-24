<?php

namespace App;

use App\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\App;

class Lead extends Model
{
    //use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

    use SoftDeletes;

    protected $table_name = 'leads';

    protected $fillable = [
        'id', 'customer_type', 'cef_id', 'house_type', 'roof_preference', 'commercial', 'commercial_other',
        'situation', 'gutter_edge_meters', 'valley_meters', 'comments',
        'staff_comments', 'source', 'sale_string', 'sale', 'customer_id',
        'enquirer_message', 'received_via', 'notify_enquirer', 'use_for', 'source_comments',
        'metadata', 'created_at', 'updated_at', 'deleted_at'
    ];

    public $timestamps = true;

    protected $casts = [
        'metadata' => 'array',
        'user_ids' => 'array'
    ];

    protected static function booted()
    {
        parent::boot();

        static::created(function ($lead) {
            if(empty($lead->lead_id)){
                $today = date('ymd');

                //include the deleted leads for the counting... since the lead id is unique
                $count = static::where('created_at', '>=', Carbon::today())->withTrashed()->count();
                if($count == 0){$count += 1;}
                do{
                    $format_counter = str_pad($count, 3, '0', STR_PAD_LEFT);
                    $lead_id = $today . $format_counter;
                    $leads = Lead::where('lead_id', $lead_id)->get();
                    $count++;
                }while(count($leads) > 0);

                $lead->lead_id = $lead_id;
                $lead->created_at = $lead->freshTimestamp();
                $lead->save();
            }
        });
    }

    public function getCustomerTypeAttribute($value)
    {
        return ucwords(strtolower($value));
    }

    public function hasActivities()
    {
        $activity = Activity::whereJsonContains('properties->metadata->lead_id', $this->id)->first();

        return isset($activity);
    }

    public function lead_jobs(){
        return $this->hasMany('App\LeadJob');
    }

    public function customer() {
        return $this->belongsTo('App\Customer');
    }

    public function lead_escalations(){
        return $this->hasMany('App\LeadEscalation');
    }

    public function active_escalation(){
        return $this->hasMany('App\LeadEscalation')->where('is_active', 1);
    }

    public function scopeFilterAsRole($query) {
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            return $query;
        } else if($user->hasRole('organisation')) {
            if($user->organisation_user) {
                return $query->where('organisation_id', $user->organisation_user->organisation_id);
            }
        }

        return $query;
    }

    public static function getCustomerStates(){
        $query = parent::rightJoin('customers', function($join){
            $join->on('leads.customer_id', '=', 'customers.id');
        })
        ->join('addresses', function($join){
            $join->on('customers.address_id', '=', 'addresses.id');
        })
        ->select(['addresses.state'])
        ->distinct()
        ->whereIn('addresses.state', ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA'])
        ->get();

        $data = array();
        $data[] = "All States";
        $data[] = "None";
        foreach($query as $row){
            $data[] = $row->state;
        }
        return $data;
    }

    public function scopeGetLeads($query, $request){
        $query->with(['lead_escalations.organisation', 'customer']);

        if(isset($request['key']) && !empty($request['key'])){
            $query->where(function($q)use($request){
                $q->orWhere('customer_type', 'like', '%'.$request['key'].'%');
            });

            $query->orWhereHas("lead_escalations.organisation", function($q) use($request){
                $q->where('name', 'like' , '%'.$request['key'].'%');
            });

            $query->orWhereHas("lead_escalations", function($q) use($request){
                $q->where('escalation_level', 'like' , '%'.$request['key'].'%');
                $q->orWhere('escalation_status', 'like' , '%'.$request['key'].'%');
            });

            $query->orWhereHas("customer", function($q) use($request){
                $q->where('first_name', 'like' , '%'.$request['key'].'%');
                $q->orWhere('last_name', 'like' , '%'.$request['key'].'%');
                $q->orWhere('email', 'like' , '%'.$request['key'].'%');
            });
        }else{
            $query->orWhereHas("lead_escalations", function($q) use($request){
                $q->where('escalation_level', 'like' , '%'.$request['key'].'%');
                $q->orWhere('escalation_status', 'like' , '%'.$request['key'].'%');
            });
        }

        return $query;
    }

    public function getSaleAttribute($sale) {
        $user = auth()->user();

        if(!isset($this->attributes['id'])){
            return $this->attributes['sale'] = "";
        }

        if($user && $user->hasRole('organisation')) {
            $lead_job = LeadJob::select('sale')
                ->where('lead_id', $this->attributes['id'])
                ->where('organisation_id', $user->organisation_user->organisation_id)
                ->first() ?? '';

            if($lead_job) {
                $sale = $lead_job->sale;
            }
        } else {
            $lead_job = LeadJob::select('sale')
                ->where('lead_id', $this->attributes['id'])
                ->orderBy('id', 'DESC')
                ->first();

            if($lead_job) {
                $sale = $lead_job->sale;
            }
        }

        return $this->attributes['sale'] = $sale;
    }

    public function scopeGetReportOrganisationBreakDown($query, $request){
        $data =
            parent::join('lead_escalations', function($q) use($request){
                $q->on('leads.id', 'lead_escalations.lead_id')->where('lead_escalations.is_active', 1);
                if(isset($request['from']) && isset($request['to']) && ! empty($request['from']) && !empty($request['to'])){
                    $from = date('Y-m-d', strtotime($request['from']));
                    $to = date('Y-m-d', strtotime($request['to']));

                    $q->whereBetween(\DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m-%d")'), [$from, $to]);
                }
            })
            ->join('organisations', 'lead_escalations.organisation_id', 'organisations.id')
            ->join('customers', 'leads.customer_id', 'customers.id')
            ->join('addresses', 'addresses.id', 'customers.address_id')
            ->select('organisations.name', 'lead_escalations.organisation_id', 'organisations.name')
            ->selectRaw('count(lead_escalations.id) as lead_count')
            ->selectRaw("
                CASE
                    WHEN lead_escalations.escalation_level = 'Won' THEN 'Won'
                    WHEN lead_escalations.escalation_level = 'Lost' THEN 'Lost'
                    ELSE 'Unresolved'
                END AS status
            ")
            ->selectRaw("
                SUM(IF(lead_escalations.escalation_level = 'Won', 1, 0)) as won_count
            ")
            ->selectRaw("
                SUM(IF(lead_escalations.escalation_level = 'Lost', 1, 0)) as lost_count
            ")
            ->selectRaw("
                SUM(IF(lead_escalations.escalation_level != 'Lost',
                    IF(lead_escalations.escalation_level != 'Won', 1, 0), 0)) as unallocated_count
            ")
            ->whereNotNull('name')
            ->groupBy(['organisations.id', 'status'])
            ->orderBy('organisations.name', 'asc');


        return $data;
    }

    public function scopeGetLeadsWonBreakdown($query, $request){
        if(!empty($request['from']) && !empty($request['to'])){
            $from = Carbon::parse(strtotime($request['from']))->addDay()->startOfDay();
            $to = Carbon::parse(strtotime($request['to']))->addDay()->endOfDay();

            $data =
            parent::join('lead_escalations', function($q){
                $q->on('leads.id', 'lead_escalations.lead_id')->where('lead_escalations.is_active', 1);
            })
            ->join('customers', 'leads.customer_id', 'customers.id')
            ->join('addresses', function($join) use($request){
                $join->on('addresses.id', '=', 'customers.address_id');

                if(isset($request['state']) && $request['state'] == 'All States'){
                    //no filter needed
                }
                else if(isset($request['state']) && ! empty($request['state'])){
                    $join->where('addresses.state', $request['state']);
                }
                else if(!empty($request['state']) && $request['state'] == 'None'){
                    $join->whereNull('addresses.state');
                    $join->orWhere('addresses.state', '');
                }
            })
            ->select('leads.id', 'source', 'lead_escalations.escalation_level', 'leads.created_at')
            ->selectRaw('IF(source IS NULL or source = "" or source = " ", "No Medium Chosen", REPLACE(LCASE(source),"–","-") ) as source')
            ->selectRaw("IF(state IS NULL or state = '', 'None', UPPER(state) ) as state")
            ->selectRaw("IFNULL(leads.gutter_edge_meters, 0) + IFNULL(leads.valley_meters, 0) as total_meters")
            ->selectRaw("(IFNULL(lead_escalations.gutter_edge_meters, 0) + IFNULL(lead_escalations.valley_meters, 0)) as total_meters_actual")
            ->selectRaw("IF(lead_escalations.escalation_status = 'Won', IF(lead_escalations.installed_date IS NULL or lead_escalations.installed_date = '', leads.created_at, lead_escalations.installed_date), leads.created_at) as date_installed")
            ->where('leads.customer_type', 'SUPPLY & INSTALL')
            ->havingRaw('leads.created_at BETWEEN ? AND ?', [$from, $to])
            ->orderBy('source', 'asc')->get();
        }else{
            $data =
            parent::join('lead_escalations', function($q){
                $q->on('leads.id', 'lead_escalations.lead_id')->where('lead_escalations.is_active', 1);
            })
            ->join('customers', 'leads.customer_id', 'customers.id')
            ->join('addresses', function($join) use($request){
                $join->on('addresses.id', '=', 'customers.address_id');

                if(isset($request['state']) && $request['state'] == 'All States'){
                    //no filter needed
                }
                else if(isset($request['state']) && ! empty($request['state'])){
                    $join->where('addresses.state', $request['state']);
                }
                else if(!empty($request['state']) && $request['state'] == 'None'){
                    $join->whereNull('addresses.state');
                    $join->orWhere('addresses.state', '');
                }
            })
            ->select('leads.id', 'source', 'lead_escalations.escalation_level', 'leads.created_at')
            ->selectRaw('IF(source IS NULL or source = "" or source = " ", "No Medium Chosen", REPLACE(LCASE(source),"–","-") ) as source')
            ->selectRaw("IF(state IS NULL or state = '', 'None', UPPER(state) ) as state")
            ->selectRaw("IFNULL(leads.gutter_edge_meters, 0) + IFNULL(leads.valley_meters, 0) as total_meters")
            ->selectRaw("(IFNULL(lead_escalations.gutter_edge_meters, 0) + IFNULL(lead_escalations.valley_meters, 0)) as total_meters_actual")
            ->selectRaw("IF(lead_escalations.escalation_status = 'Won', IF(lead_escalations.installed_date IS NULL or lead_escalations.installed_date = '', leads.created_at, lead_escalations.installed_date), leads.created_at) as date_installed")
            ->where('leads.customer_type', 'SUPPLY & INSTALL')
            ->orderBy('source', 'asc')->get();
        }

        //return $data;

        $report = array();
        $mediums = array();
        $lead_counts = array();
        $lead_won = array();
        $lead_won_percentate = array();
        $lead_states = array();
        $lead_states_count = array();
        $lead_states_won = array();
        $lead_total_meters = array();
        $lead_total_meters_actual = array();
        $lead_won_percentage = array();

        foreach($data as $row){
            $row->source = $row->source ? $row->source : 'No Medium Chosen';
            if(! in_array($row->source, $mediums)){
                $mediums[] = $row->source;
            }
        }

        $total_state = 0;
        $total_won = 0;

        foreach($mediums as $medium){
            $leads = 0;
            $leads_won = 0;
            $leads_not_won = 0;
            $leads_state_won = 0;
            $states = array();
            $temp_states = array();
            $temp_count = array();
            $temp_state_won = '';
            $temp_total_meters = array();
            $temp_total_meters_actual = array();
            $temp_won_states = [];

            foreach($data as $row){
                if($medium == $row->source){
                    //echo $row->source;
                    if(! array_key_exists($row->state, $temp_total_meters)) {
                        $temp_total_meters[$row->state] = 0;
                    }

                    if(! array_key_exists($row->state, $temp_total_meters_actual)) {
                        $temp_total_meters_actual[$row->state] = 0;
                    }

                    $leads++;

                    if($row->escalation_level == 'Won'){
                        $leads_won++;
                        $temp_state_won = $row->state;
                        $leads_state_won++;

                        if(! array_key_exists($row->state, $temp_won_states)) {
                            $temp_won_states[$row->state] = 1;
                        } else {
                            $temp_won_states[$row->state] += 1;
                        }
                    }else{
                        $leads_not_won++;
                    }

                    $temp_size_val = 0;
                    $temp_size_val_actual = 0;

                    if(is_numeric($row->total_meters)) {
                        $temp_size_val = $row->total_meters;
                    } else {
                        if($row->valley_meters) {
                            $temp_size_val = str_replace('metres', '', $row->total_meters);
                            $temp_size_val = (int)$temp_size_val;
                        }
                    }

                    $temp_total_meters[$row->state] += $temp_size_val;

                    if($row->escalation_level == 'Won'){
                        if(is_numeric($row->total_meters_actual)) {
                            $temp_size_val_actual = $row->total_meters_actual;
                        } else {
                            if($row->valley_meters) {
                                $temp_size_val_actual = str_replace('metres', '', $row->total_meters_actual);
                                $temp_size_val_actual = (int)$temp_size_val_actual;
                            }
                        }
                    }

                    $temp_total_meters_actual[$row->state] += $temp_size_val_actual;

                    # Initialize all states
                    $states[$row->state] = 0;
                }

            }

            $percentage = ($leads_won / $leads) * 100;
            $lead_counts[] = $leads;
            $lead_won[] = $leads_won;
            $lead_won_percentage[] = $percentage;

            $total_won += $leads_won;

            foreach($states as $state => $count) {
                if(isset($temp_won_states[$state]) && $temp_won_states[$state] > 0) {
                    $states[$state] = $temp_won_states[$state];
                }
            }

            $lead_total_meters[] = $temp_total_meters;
            $lead_total_meters_actual[] = $temp_total_meters_actual;

            $state_counts = $states;

            if($state_counts > 0){
                foreach($state_counts as $key => $value){
                    $temp_states[] = $key;
                    $temp_count[] = $value;
                    $total_state += $value;
                }
                $lead_states[] = $temp_states;
                $lead_states_count[] = $temp_count;
            }

            $lead_states_won_temp = array();

            foreach($states as $state => $count) {
                try{
                    $lead_states_won_temp[] = number_format(round(($count / $leads_won) * 100, 2)) . '%';
                }
                catch(\Exception $ex){
                    $lead_states_won_temp[] = 0;
                }
            }

            $lead_states_won[] = $lead_states_won_temp;

        }
        $report['mediums'] = $mediums;
        $report['lead_counts'] = $lead_counts;
        $report['lead_won'] = $lead_won;
        $report['lead_won_percentage'] = $lead_won_percentage;
        $report['lead_states'] = $lead_states;
        $report['lead_states_count'] = $lead_states_count;
        $report['lead_states_won'] = $lead_states_won;
        $report['lead_total_meters'] = $lead_total_meters;
        $report['lead_total_meters_actual'] = $lead_total_meters_actual;


        $data = array();
        $count = count($report['mediums']);
        $total_meters = 0;
        $total_meters_actual = 0;

        for($i=0; $i<$count; $i++){
            $lead_total_meters = array();
            $lead_total_meters_actual = array();

            foreach($report['lead_total_meters'][$i] as $meters){
                $lead_total_meters[] = $meters;
                $total_meters += $meters;
            }

            foreach($report['lead_total_meters_actual'][$i] as $meters){
                $lead_total_meters_actual[] = $meters;
                $total_meters_actual += $meters;
            }

            $data[] = array(
                'medium' => $report['mediums'][$i],
                'total_leads' => $report['lead_counts'][$i],
                'lead_won' => $report['lead_won'][$i],
                'percentage_won' => number_format($report['lead_won_percentage'][$i], 2),
                'states' => $report['lead_states'][$i],
                'states_total' => $report['lead_states_count'][$i],
                'lead_states_won' => $report['lead_states_won'][$i],
                'lead_total_meters' => $lead_total_meters,
                'lead_total_meters_actual' => $lead_total_meters_actual,
            );
        }

        return $data;
    }

    /**
    * Get Reports data
    *
    * @param  \Illuminate\Http\Request  $request query string for searching
    * @param  int 1 for organisation dashboard, else administrator or super-admin
    * @return Leads (Reports Medium Breakdown)
    */
    public function scopeGetReportMediumBreakDown($query, $request){
        $total_leads = parent::all()->count();

        \DB::statement('SET SESSION group_concat_max_len = 1000000');

        $query
            ->join('customers', function($join){
                $join->on('leads.customer_id', '=', 'customers.id');
            })
            ->join('addresses', function($join) use($request){
                if(isset($request['state'])){
                    $state = $request['state'];
                    //if all state dont query address state
                    if($request['state'] != 'All States'){
                        $join->on('customers.address_id', '=', 'addresses.id')->where('addresses.state', $state);
                    }else{
                        $join->on('customers.address_id', '=', 'addresses.id');
                    }
                }else{
                    $join->on('addresses.id', '=', 'customers.address_id');
                }
            })
            ->select('leads.id', 'source')
            ->selectRaw('IF(source IS NULL or source = "" or source = " ", "None Chosen", REPLACE(LCASE(source),"–","-") ) as sources')
            ->selectRaw('GROUP_CONCAT(UPPER(addresses.state) SEPARATOR ", ") as states')
            ->selectRaw('count(*) as count_source')
            ->selectRaw('CONCAT((FORMAT((count(*) / ' . $total_leads . ')*100, 2)), \'%\') as percentage')
            ->whereIn('addresses.state', ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA'])
            ->where(function($q) use($request){
                //query date between from and to
                if(isset($request['from']) && isset($request['to'])){
                    $from = date('Y-m-d', strtotime($request['from']));
                    $to = date('Y-m-d', strtotime($request['to']));

                    $q->whereBetween(\DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m-%d")'), [$from, $to]);
                }
                //query date by from to greater
                else if(isset($request['from'])){
                    $from = date('Y-m-d', strtotime($request['from']));

                    $q->where(\DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m-%d")'), '>=', $from);
                }
                //query date by to less than
                else if(isset($request['to'])){
                    $to = date('Y-m-d', strtotime($request['to']));

                    $q->where(\DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m-%d")'), '<=', $to);
                }
            })
            ->groupBy(['sources'])
            ->orderByRaw("FIELD(sources,
                'None Chosen'
        ), count_source DESC");

        return $query;
    }

    /**
    * Get Dashboard data
    *
    * @param  \Illuminate\Http\Request  $request query string for searching
    * @param  int 1 for organisation dashboard, else administrator or super-admin
    * @return Leads (Dashboard)
    */
    public function scopeGetDashboard($query, $request){
        $user = auth()->user();

        $query->leftJoin('lead_escalations', function($join){
            $join->on('leads.id', '=', 'lead_escalations.lead_id');
        })->leftJoin('customers', function($join){
            $join->on('leads.customer_id', '=', 'customers.id');
        })
        ->select(
            \DB::raw('CONCAT(escalation_level, " - ", escalation_status) as lead_escalation_status'),
            \DB::raw('CONCAT(last_name, ", ", first_name) as inquirer_name'),
            'leads.id as lead_id',
            'lead_escalations.id as lead_escalation_id',
            'lead_escalations.escalation_level as escalation_level',
            'lead_escalations.escalation_status as escalation_status',
            'lead_escalations.color as color',
            'lead_escalations.progress_period_date as progress_period_date',
            'lead_escalations.gutter_edge_meters as gutter_edge_meters',
            'lead_escalations.valley_meters as valley_meters',
            'lead_escalations.installed_date as installed_date',
            'lead_escalations.is_notified as is_notified',
            'lead_escalations.is_active as is_active',
            'lead_escalations.reason as reason',
            'lead_escalations.comments as comments',
            'lead_escalations.organisation_id as organisation_id',
            'lead_escalations.metadata as metadata',
            'lead_escalations.created_at as created_at',
            'customers.email as inquirer_email',
            'customers.contact_number as inquirer_contact_number')
        ->where('is_active', 1)
        ->where(function($q) use($request){
            if(isset($request['organisation_id'])){
                $q->where('organisation_id', $request['organisation_id']);
            }

            if(isset($request['escalation_level'])){
                $q->where('escalation_level', $request['escalation_level']);
            }
        });

        $query->orderByRaw(
            "FIELD(lead_escalation_status,
                'Accept Or Decline - Declined-Lapsed',
                'Accept Or Decline - Decline',
                'Confirm Enquirer Contacted - Discontinued',
                'In Progress - Discontinued',
                'Unassigned - Special Opportunity',
                'Unassigned - Unassigned',
                'Confirm Enquirer Contacted - Awaiting Response - Admin Notified',
                'In Progress - Awaiting Response - Admin Notified',
                'Confirm Enquirer Contacted - Awaiting Response - Reminder Sent',
                'In Progress - Awaiting Response - Reminder Sent',
                'Accept Or Decline - Pending',
                'Confirm Enquirer Contacted - Awaiting Response',
                'In Progress - Awaiting Response',
                'In Progress - Extended 3',
                'In Progress - Extended 2',
                'In Progress - Extended 1',
                'Lost - Lost',
                'Won - Won',
                'Accept or Decline - Supply Only',
                'Confirm Enquirer Contacted - Supply Only',
                'In Progress - Supply Only(SP)',
                'In Progress - Supply Only',
                'Finalized - Supply Only(SP)',
                'Finalized - Supply Only',
                'Won - Supply Only',
                'Lost - Supply Only',
                'Supply Only - SP'
            ) ASC"
        );

        if(isset($request['keyword'])){
            $query->orHaving('inquirer_name', 'like', '%'.$request['keyword'].'%');
            $query->orHaving('lead_id', 'like', '%'.$request['keyword'].'%');
            $query->orHaving('inquirer_email', 'like', '%'.$request['keyword'].'%');
        }

        return $query;
    }

    public function scopeFilterWithOrg($query, $org){

        if( isset($org) ){
            $query->whereHas("lead_escalations", function($q) use($org){
                $q->where('organisation_id', $org->id);
                $q->selectRaw(\DB::raw("SUM(CASE
                WHEN is_active = '1' THEN 1 ELSE 0 END)
                as lead_count"));
                $q->having('lead_count', '>', 0);
            });
        }

        return $query;
    }

    public function notifications() {
        return $this->hasMany('App\Notification', 'metadata->lead_id');
    }
}
