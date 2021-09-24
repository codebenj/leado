<?php

namespace App;

use App\Model;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Notification;
use App\Organisation;
use App\TimeSetting;
use App\Jobs\NotificationJob;
use App\Mail\AdminNotification;
use App\Mail\OrganizationNotification;
use App\Mail\InquirerNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;
use Illuminate\Support\Carbon;
use App\Events\LeadEscalationCreatedEvent;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use App\Setting;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

use function Ramsey\Uuid\v1;

class LeadEscalation extends Model
{
    use SoftDeletes;

    protected $table_name = 'lead_escalations';

    protected $fillable = [
        'escalation_level', 'escalation_status', 'color',
        'progress_period_date', 'valley_meters', 'gutter_edge_meters', 'installed_date',
        'is_notified', 'is_active', 'reason', 'comments', 'lead_id',
        'organisation_id', 'expiration_date', 'metadata',
        'created_at', 'updated_at', 'deleted_at', 'user_id', 'paused_time',
        'added_time', 'extended_time'
    ];

    public $timestamps = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'expiration_date' => 'datetime',
    ];

    /**
     * Append custom fields
     *
     * @var array
     */
    protected $appends = [
        'time_left',
        'min_extension',
        'max_extension',
        'expiration_text',
        'open_date',
        'is_critical',
        'earliest_history'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        $notification = new Notification();

        static::created(function ($lead_escalation) use($notification) {
            if(! isset($lead_escalation->metadata['v2_data'])){
                $notification_messages = $notification->parseMessages();

                $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
                $organisation_notifications = $lead_escalation->organisation->notifications ?? ['sms', 'email'];

                //update expiration date
                $countdown = Setting::where('name', $lead_escalation_status)->first();

                if($countdown && ! empty($countdown->value)) {
                    $time_type = ucfirst($countdown->metadata['type']);
                    $lead_escalation->expiration_date = Date('Y-m-d H:i:s', strtotime('+'.$countdown->value." $time_type"));
                    $lead_escalation->save();
                }

                # Get general notifications for IP - Extended N
                if(strpos($lead_escalation_status, 'In Progress - Extended') !== false) {
                    $lead_escalation_status = 'In Progress - Extended';
                }

                $user_types = ['inquirer', 'admin', 'organization'];

                foreach($user_types as $user_type) {
                    if((isset($notification_messages[$lead_escalation_status][$user_type]['email']['title']) &&
                        !empty($notification_messages[$lead_escalation_status][$user_type]['email']['title'])) ||
                       (isset($notification_messages[$lead_escalation_status][$user_type]['email']['message']) &&
                        !empty($notification_messages[$lead_escalation_status][$user_type]['email']['message']))) {

                        if($user_type == 'admin') {
                            $setting = Setting::where('key', 'admin-email-notification-receivers')->first();
                            if($setting && !empty($setting->value)) {
                                $emails = explode(',', $setting->value);

                                $recepient = [];
                                foreach($emails as $email) {
                                    $email = trim($email);
                                    if(!empty($email)) {
                                        $recepient[] = [
                                            'name' => 'Admin',
                                            'email' => $email
                                        ];
                                    }
                                }
                            } else {
                                # get admin user names and emails
                                $users_email = User::role('administrator')->get();

                                $recepient = [];
                                foreach($users_email as $user) {
                                    $recepient[] = [
                                        'name' => $user->name,
                                        'email' => $user->email
                                    ];
                                }
                            }

                            # Removed email sending to admin when reassigning
                            if( !isset($lead_escalation->metadata['reassign'])) {
                                # Send email to admin
                                Mail::to($recepient)->queue(new AdminNotification($lead_escalation, $notification_messages));
                            }

                        } else if($user_type == 'inquirer' && !isset($lead_escalation->metadata['prevent_inquirer_notification'])) {
                            $title = $notification_messages[$lead_escalation_status][$user_type]['email']['title'];
                            $description = $notification_messages[$lead_escalation_status][$user_type]['email']['description'];

                            $recepient = [];
                            $recepient[] = [
                                'name' => "{$lead_escalation->lead->customer->first_name} {$lead_escalation->lead->customer->last_name}",
                                'email' => $lead_escalation->lead->customer->email
                            ];

                            $message = $description;

                            if(!empty($lead_escalation->lead->enquirer_message)){
                                $message = $lead_escalation->lead->enquirer_message;
                            }

                            # Send email to inquirer check if the customer has email address
                            if(!empty($lead_escalation->lead->customer->email && isset($lead_escalation->metadata['notif_config']['enquirer_send_email']))
                                && !empty($lead_escalation->metadata['notif_config']['enquirer_send_email'])) {
                                Mail::to($recepient)->queue(new InquirerNotification($title, $message));
                            }

                            # Send sms to inquirer check if the customer has contact number
                            if(!empty($lead_escalation->lead->customer->contact_number) && isset($lead_escalation->metadata['notif_config']['enquirer_send_sms'])
                                 && !empty($lead_escalation->metadata['notif_config']['enquirer_send_sms'])) {
                                //add notification for enquirer, to display in LO - Enquirer Notifications
                                $notif_structure = [
                                    'title' => 'LEAF STOPPER CONFIRMATION',
                                    'description' => $message,
                                    'metadata' => [
                                        'to' => 'inquirer',
                                        'lead_id' => $lead_escalation->lead_id,
                                        'organisation_id' => $lead_escalation->organisation_id,
                                        'notification_type' => 'notification',
                                        'lead_escalation_status' => $lead_escalation_status,
                                        'lead_escalation_color' => $lead_escalation->leadEscalationStatus($lead_escalation_status) ?? 'purple',
                                    ]
                                ];

                                Notification::create($notif_structure);

                                //save all notification types
                                $notification->logActivity(
                                    $lead_escalation,
                                    $notif_structure
                                );

                                Notification::send_sms($lead_escalation->lead->customer->contact_number, $message);
                            }

                        } else {
                            if($lead_escalation->organisation && (!isset($lead_escalation->metadata['send_email']) || $lead_escalation->metadata['send_email'] == true)) {
                                $user_ids = $lead_escalation->organisation->organisation_users()->pluck('user_id');

                                $users_email = User::whereIn('id',
                                    $user_ids
                                )->get();

                                $recepient = [];
                                foreach($users_email as $user) {
                                    $recepient[] = [
                                        'name' => $user->name,
                                        'email' => $user->email
                                    ];
                                }

                                # Send email to org users
                                # for previous value of notification. we have updated values notification to email
                                if( isset($lead_escalation->metadata['notif_config'])) {
                                    if ( $lead_escalation->metadata['notif_config']['send_email'] == true) {
                                        Mail::to($recepient)->queue(new OrganizationNotification($lead_escalation, $notification_messages));
                                    }
                                }

                                if(in_array('notification', $organisation_notifications) && (!isset($lead_escalation->metadata['notif_config'])) ){
                                    Mail::to($recepient)->queue(new OrganizationNotification($lead_escalation, $notification_messages));
                                } else if(in_array('email', $organisation_notifications)) {
                                    Mail::to($recepient)->queue(new OrganizationNotification($lead_escalation, $notification_messages));
                                }
                            }
                        }
                    }
                }
                NotificationJob::dispatch(
                    $lead_escalation
                );
            }

        });
    }

    public function customer() {
        return $this->belongsTo('App\Customer');
    }

    public function lead() {
        return $this->belongsTo('App\Lead');
    }

    public function organisation() {
        return $this->belongsTo('App\Organisation');
    }

    public function getIsCriticalAttribute() {
        //seems its not reading as in test units
        if ( App::runningUnitTests() ) {
            return true;
        }

        //in testing units, factory run first so no user logins
        if(!auth()->user()){
            return true;
        }

        $criticals = ['Accept Or Decline - Declined-Lapsed', 'Confirm Enquirer Contacted - Awaiting Response - Admin Notified', 'In Progress - Awaiting Response - Admin Notified' ];
        $status = $this->escalation_level . ' - ' . $this->escalation_status;

        if ( auth()->user()->roles[0]['name'] == 'organisation' ) {
            return ( in_array( $status, $criticals ) ) ? true : false;
        }

        return false;
    }

    public function getEarliestHistoryAttribute() {
        //seems its not reading as in test units
        if ( App::runningUnitTests() ) {
            return true;
        }

        //in testing units, factory run first so no user logins
        if(!auth()->user()){
            return true;
        }

        $data = $this->where('lead_id', $this->lead_id)->orderBy('created_at', 'ASC')->first();

        if ($data) return $data->created_at;

        return true;
    }

    public function getTimeLeftAttribute() {
        $expiration_date = $this->expiration_date;

        // PAUSE DATES
        $dates = [
            'start' => [],
            'end' => [],
        ];
        $times = [];
        $now = Carbon::now();

        $timeSettings = TimeSetting::all();
        foreach($timeSettings as $timeSetting){
            if($timeSetting->is_active){
                if($timeSetting->type == "recurring"){
                    $start_recurring = new \DateTime( $timeSetting->start_day );
                    $stop_recurring = new \DateTime( $timeSetting->stop_day );

                    $interval_recurring = new \DateInterval('P1D');
                    $daterange_recurring = new \DatePeriod($start_recurring, $interval_recurring, $stop_recurring);

                    if(!empty($daterange_recurring)){

                        $dates['start']= Carbon::parse($timeSetting->start_day );
                        $dates['end'] = Carbon::parse($timeSetting->stop_day );
                    }
                }
                else{
                    $start_onetime = new \DateTime( $timeSetting->start_date );
                    $stop_onetime = new \DateTime( $timeSetting->stop_date );

                    $interval_onetime = new \DateInterval('P1D');
                    $daterange_onetime = new \DatePeriod($start_onetime, $interval_onetime, $stop_onetime);

                    if(!empty($daterange_onetime)){
                        $dates['start'] = Carbon::parse($timeSetting->start_date );
                        $dates['end'] = Carbon::parse($timeSetting->stop_date );
                    }

                    // $start_onetime_dt = new \DateTime( "{$timeSetting->start_date} {$timeSetting->start_time}" );
                    // $stop_onetime_dt = new \DateTime( "{$timeSetting->stop_date} {$timeSetting->stop_time}" );

                    // $interval_onetime_dt = new \DateInterval('P1D');
                    // $daterange_onetime_dt = new \DatePeriod($start_onetime_dt, $interval_onetime_dt, $stop_onetime_dt);

                    // if(!empty($daterange_onetime_dt)){
                    //     foreach($daterange_onetime_dt as $date_onetime_dt){
                    //         $times[] =  Carbon::parse($date_onetime_dt);
                    //     }
                    // }

                    // $times[] =  Carbon::parse($start_onetime_dt);
                    // $times[] =  Carbon::parse($stop_onetime_dt);
                }
            }
        }

        // $is_paused = in_array($now, $dates);

        if($expiration_date == "" || $expiration_date == null || $expiration_date < $now){
            return 0;
        }
        else {
            $extension = (!empty($dates['start'] )) && (!empty($dates['end'] )) ? min([$dates['start']])->diffInMilliseconds(max([$dates['end']])) : 0;
            // $extension_2 = (!empty($times)) ? min($times)->diffInSeconds(max($times)) : 0;
            // dd(min($dates), min($times), max($dates), max($times));
            // dd($extension, $extension_2);

            // $expiration_date_2 = Carbon::parse($expiration_date)->addSeconds($extension_2);
            // dd($expiration_date, $expiration_date_2);

            // SAVE TO paused_time column THE NEW EXPIRATION DATE
            // if($extension != 0){
            //     $this->paused_time = $now->diffInMilliseconds($expiration_date, false);
            //     $this->save();
            // }

            // IF TIMER SHOULD SHOW BUT PAUSED
            // $expiration_date = $extension ? $now->diffInMilliseconds($expiration_date, false) - min($dates)->diffInMilliseconds(max($dates)) : $now->diffInMilliseconds($expiration_date, false);
            $paused = Carbon::parse( $this->paused_time);
            $added = Carbon::parse( $this->added_time);

            $times = TimeSetting::where('is_active', true)->get()->count();

            $new_expiration = $paused->diffInMilliseconds($expiration_date, false);

            $is_paused = $times > 0 && $extension != 0;

            $expiration_date = $expiration_date->addMilliseconds($this->extended_time);
            // $ete = $now->diffInMilliseconds($expiration_date, false) + $test->diffInMilliseconds($now, false);
            return $is_paused ? $new_expiration : $now->diffInMilliseconds($expiration_date, false) ;
        }
    }

    public static function getExtensions() {
        // COMPUTE EXTENSION
        $dates = [];
        $times = [];

        $timeSettings = TimeSetting::where( 'is_active', true )->get();
        $dateInterval = new \DateInterval( 'P1D' );

        foreach ( $timeSettings as $timeSetting ) {
            if ( $timeSetting->type == "recurring" ) {
                $start_recurring = new \DateTime($timeSetting->start_day);
                $stop_recurring = new \DateTime($timeSetting->stop_day);

                $daterange_recurring = new \DatePeriod($start_recurring, $dateInterval, $stop_recurring);

                if ( ! empty( $daterange_recurring ) ) {
                    foreach ( $daterange_recurring as $date_recurring ) {
                        $dates[] =  Carbon::parse($date_recurring);
                    }
                }
            } else {
                $start_onetime = new \DateTime($timeSetting->start_date);
                $stop_onetime = new \DateTime($timeSetting->stop_date);

                $daterange_onetime = new \DatePeriod($start_onetime, $dateInterval, $stop_onetime);

                if ( ! empty( $daterange_onetime ) ) {
                    foreach ( $daterange_onetime as $date_onetime ) {
                        $dates[] =  Carbon::parse($date_onetime);
                    }
                }

                $start_onetime_dt = new \DateTime("{$timeSetting->start_date} {$timeSetting->start_time}");
                $stop_onetime_dt = new \DateTime("{$timeSetting->stop_date} {$timeSetting->stop_time}");

                $daterange_onetime_dt = new \DatePeriod($start_onetime_dt, $dateInterval, $stop_onetime_dt);

                if ( ! empty( $daterange_onetime_dt ) ) {
                    foreach ($daterange_onetime_dt as $date_onetime_dt) {
                        $times[] =  Carbon::parse($date_onetime_dt);
                    }
                }

                $times[] =  Carbon::parse($start_onetime_dt);
                $times[] =  Carbon::parse($stop_onetime_dt);
            }
        }

        // $extension = (!empty($dates)) ? min($dates)->diff(max($dates))->days : 0;
        $extension_in_seconds = (!empty($times)) ? min($times)->diffInSeconds(max($times)) : 0;
        $extension_in_hours = (!empty($dates)) ? min($dates)->diffInHours(max($times)) : 0;
        $extension_in_days = (!empty($dates)) ? min($dates)->diffInDays(max($dates)) : 0;
        $extension_in_months = (!empty($dates)) ? min($dates)->diffInMonths(max($dates)) : 0;

        $data = [
            $extension_in_seconds,
            $extension_in_hours,
            $extension_in_days,
            $extension_in_months,
        ];

        // $expiration_date = Carbon::parse($expiration_date)->addDays($extension);

        return implode( ", ", $data );
    }

    public function addOneTimePauseTimers($expiration_date) {
        // get all sum of one-time pause timers that are within the expiration date

        // use carbon to add sum of additional one-time pause timers

        // return the new expiration date
        return $expiration_date;
    }

    public function addRecurringPauseTimers($expiration_date) {
        // get all of recurring pause timers that are within the expiration date

        // find a way to skipped the recurring pause timers that are within the expiration date

        // use carbon to add sum of additional one-time pause timers

        return $expiration_date;
    }

    public function getMinExtensionAttribute() {
        return (isset($this->progress_period_date) && $this->progress_period_date)
            ? $this->progress_period_date
            : Carbon::now();
    }

    public function getMaxExtensionAttribute() {
        $extension = Setting::where('key', 'inprogress-extended')->get();
        if(!empty($this->progress_period_date) && !empty($this->progress_period_date)) {
            $createdDate = new Carbon($this->progress_period_date);
        } else {
            $createdDate = new Carbon($this->created_at);

            if(Carbon::now()->diffInDays($createdDate, false) < 0) {
                $createdDate = Carbon::now();
            }
        }

        $value = 1;
        $measurement = 'months';
        if($extension->count() > 0) {
            $extension = $extension[0];
            $value = $extension->value;
            $measurement = $extension->metadata['type'];
            $intervalType = ucfirst($measurement);
        }
        $addInterval = "add{$intervalType}";

        return $createdDate->{$addInterval}($value);
    }

    public function getOpenDateAttribute(){
        $date = new Carbon($this->progress_period_date);

        return (isset($this->progress_period_date) && $this->progress_period_date)
            ? $date->addDays(1)->format('Y-m-d')
            : Carbon::now();
    }

    public function getExpirationTextAttribute() {
        $expirationText = '';
        $timerSetting = \App\Setting::whereJsonContains('metadata->level', $this->escalation_level)->whereJsonContains('metadata->status', $this->escalation_status)->first();

        $adminTooltip = isset($timerSetting) && isset($timerSetting->metadata['admin_tooltip']) ? $timerSetting->metadata['admin_tooltip'] : '';
        $orgTooltip = isset($timerSetting) && isset($timerSetting->metadata['org_tooltip'])? $timerSetting->metadata['org_tooltip'] : '';

        return [
            'admin' => str_replace('[reason]', $this->reason, $adminTooltip),
            'org' => str_replace('[reason]', $this->reason, $orgTooltip),
        ];
    }

    public function scopeFilterAsRole($query) {
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            //return $query->where('is_active', true);
        } else if($user->hasRole('organisation')) {
            if($user->organisation_user) {
                return $query->where(function($q) {
                    $q->where('is_active', true);

                    //dont show lead if supply on or unassigned
                    $q->whereNotIn('escalation_status', ['Unassigned', 'Supply Only', 'Supply Only(SP)', 'General Enquiry', 'General Enquiry(SP)']);

                    /* Custom WHERE clause where we get the leads with "Declined", "Discontinued", "Abandoned" and display
                    * it if no updates on top of it. Meaning, if there's a manual update from Discontinued status to
                    * AOD - IP, the discontinued status should not be displayed but instead, the active escalation.
                    */
                    $q->orWhereRaw('lead_escalations.lead_id IN (SELECT le.lead_id FROM lead_escalations le WHERE escalation_status IN("Declined", "Discontinued", "Abandoned") AND (SELECT COUNT(*) FROM lead_escalations inner_le WHERE inner_le.lead_id = le.lead_id AND is_active=false) <= 0)');
                })->where('organisation_id', $user->organisation_user->organisation_id);
            }else{
                //user has no organiation_user
                return $query->where('is_active', true)
                    ->where('organisation_id', 0);
            }
        }

        return $query;
    }

    public static function getOrganisationStates(){
        $query = parent::leftJoin('leads', function($join){
            $join->on('leads.id', '=', 'lead_escalations.lead_id');
        })
        ->leftJoin('organisations', function($join){
            $join->on('organisations.id', '=', 'lead_escalations.organisation_id');
        })
        ->leftJoin('addresses', function($join){
            $join->on('organisations.address_id', '=', 'addresses.id');
        })
        ->selectRaw("IFNULL(addresses.state, 'Undefined') as state")
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

    public function scopeGetOrganizationStats($query, $org_id){
        $acceptance_date = [
            "30_Won" => [],
            "30_Lost"=> [],
            "30_Unallocated"=> [],
            "30_Rejected"=> [],

            "90_Won" => [],
            "90_Lost" => [],
            "90_Unallocated" => [],
            "90_Rejected" => [],

            "180_Won" => [],
            "180_Lost" => [],
            "180_Unallocated" => [],
            "180_Rejected" => [],
        ];

        $acceptance_date_name = [
            "30 Days Won",
            "30 Days Lost",
            "30 Days Unallocated",
            "30 Days Rejected",
            "90 Days Won",
            "90 Days Lost",
            "90 Days Unallocated",
            "90 Days Rejected",
            "180 Days Won",
            "180 Days Lost",
            "180 Days Unallocated",
            "180 Days Rejected",
        ];

        $organisation = Organisation::find($org_id);
        $organisation_ids = array();
        $organisation_names = array();

        //getting the organisation information
        $organisation_ids[] = $organisation->id;
        $organisation_names[] = $organisation->name;

        $stats = array();
        $count = count($organisation_ids);

        $stat = array();
        $index = 0;
        foreach($acceptance_date as $key => $value){

            for($i=0; $i < $count; $i++){
                //$stat['title'] = $key;
                $stat['title'] = $acceptance_date_name[$index];
                if($key == '30_Won'){
                    $total = parent::where('created_at', '>=', Carbon::now()->subDays(30)->toDateString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'escalation_status' => 'Won', 'is_active' => 1])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '30_Lost'){
                    $total = parent::where('created_at', '>=', Carbon::now()->subDays(30)->toDateString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'escalation_status' => 'Lost', 'is_active' => 1])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '30_Unallocated'){
                    $total = parent::where('created_at', '>=', Carbon::now()->subDays(30)->toDateString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'is_active' => 1])
                        ->whereNotIn('escalation_status', ['Won', 'Lost', 'Declined', 'Declined-Lapsed'])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '30_Rejected'){
                    $total = parent::where('created_at', '>=', Carbon::now()->subDays(30)->toDateString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'is_active' => 1])
                        ->whereIn('escalation_status', ['Declined', 'Declined-Lapsed'])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '90_Won'){
                    $total = parent::where('created_at', '>=', Carbon::now()->subDays(90)->toDateString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'escalation_status' => 'Won', 'is_active' => 1])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '90_Lost'){
                    $total = parent::where('created_at','<' , Carbon::now()->subDays(30)->toDateTimeString())
                        ->where('created_at','>=' , Carbon::now()->subDays(90)->toDateTimeString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'escalation_status' => 'Lost', 'is_active' => 1])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '90_Unallocated'){
                    $total = parent::where('created_at','<' , Carbon::now()->subDays(30)->toDateTimeString())
                    ->where('created_at', '>=', Carbon::now()->subDays(90)->toDateString())
                    ->where(['organisation_id' => $organisation_ids[$i], 'is_active' => 1])
                    ->whereNotIn('escalation_status', ['Won', 'Lost', 'Declined', 'Declined-Lapsed'])
                    ->count();

                    $stat['company_'.$i] = $total;
                }
                else if($key == '90_Rejected'){
                    $total = parent::where('created_at','<' , Carbon::now()->subDays(30)->toDateTimeString())
                    ->where('created_at', '>=', Carbon::now()->subDays(90)->toDateString())
                    ->where(['organisation_id' => $organisation_ids[$i], 'is_active' => 1])
                    ->whereIn('escalation_status', ['Declined', 'Declined-Lapsed'])
                    ->count();

                    $stat['company_'.$i] = $total;
                }
                else if($key == '180_Won'){
                    $total = parent::where('created_at','<' , Carbon::now()->subDays(90)->toDateTimeString())
                        ->where('created_at', '>=', Carbon::now()->subDays(180)->toDateString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'escalation_status' => 'Won', 'is_active' => 1])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '180_Lost'){
                    $total = parent::where('created_at','<' , Carbon::now()->subDays(90)->toDateTimeString())
                        ->where('created_at', '>=', Carbon::now()->subDays(180)->toDateString())
                        ->where(['organisation_id' => $organisation_ids[$i], 'escalation_status' => 'Lost', 'is_active' => 1])
                        ->count();

                        $stat['company_'.$i] = $total;
                }
                else if($key == '180_Unallocated'){
                    $total = parent::where('created_at','<' , Carbon::now()->subDays(90)->toDateTimeString())
                    ->where('created_at', '>=', Carbon::now()->subDays(180)->toDateString())
                    ->where(['organisation_id' => $organisation_ids[$i], 'is_active' => 1])
                    ->whereNotIn('escalation_status', ['Won', 'Lost', 'Declined', 'Declined-Lapsed'])
                    ->count();

                    $stat['company_'.$i] = $total;
                }
                else if($key == '180_Rejected'){
                    $total = parent::where('created_at','<' , Carbon::now()->subDays(90)->toDateTimeString())
                    ->where('created_at', '>=', Carbon::now()->subDays(180)->toDateString())
                    ->where(['organisation_id' => $organisation_ids[$i], 'is_active' => 1])
                    ->whereIn('escalation_status', ['Declined', 'Declined-Lapsed'])
                    ->count();

                    $stat['company_'.$i] = $total;
                }

            }
            $stats[] = $stat;
            $index++;
        }

        $columns = array();
        $columns[] = array(
            'prop' => 'title',
            'label' => 'Last Acceptance Date',
            'align' => 'left'
        );

        for($i=0; $i < $count; $i++){
            $columns[] = array(
                'prop' => 'company_'.$i,
                'label' => $organisation_names[$i],
                'align' => 'center'
            );
        }


        $data['company_id'] = $organisation_ids;
        $data['organisation_name'] = $organisation_names;
        $data['stats'] = $stats;
        $data['dates'] = $acceptance_date_name;
        $data['columns'] = $columns;
        return $data;

    }

    public function scopeGetLeadStats($query ,$lead_id){
        $stats = [
            'Won'          => [30, 90, 180],
            'Lost'         => [30, 90, 180],
            'Unassigned'   => [30, 90, 180],
            'Declined'     => [30, 90, 180]
        ];

        $stats_result = [];
        $stats_labels = [];
        $lead_esc = $query->where(['lead_id' => $lead_id, 'is_active' => true])->first();
        $organisation = $lead_esc->organisation ?? false;

        foreach($stats as $key => $stat) {
            foreach($stat as $days) {
                if($organisation) {
                    $stat_temp = [];
                    $stat_temp['title'] = "{$days} Days {$key}";
                    $stat_temp['company_0'] = parent::where('created_at', '>=', Carbon::now()->subDays($days)->toDateString())
                            ->where(['organisation_id' => $organisation->id, 'escalation_status' => $key])
                            ->count();
                } else {
                    $stat_temp = [];
                    $stat_temp['no_organization'] = '-';
                    $stat_temp['title'] = "{$days} Days {$key}";
                }

                $stats_labels[] = "{$days} Days {$key}";
                $stats_result[] = $stat_temp;
            }
        }

        $columns = [];
        $columns[] = [
            'prop' => 'title',
            'label' => 'Last Acceptance Date',
            'align' => 'left'
        ];

        if($organisation){
            $columns[] = [
                'prop' => 'company_0',
                'label' => $organisation->name,
                'align' => 'center'
            ];
        } else {
            $columns[] = [
                'prop' => 'no_organization',
                'label' => 'No Organisation',
                'align' => 'center'
            ];
        }

        return [
            'columns'           => $columns,
            'organisation_name' => [$organisation->name ?? '---'],
            'dates'             => $stats_labels,
            'stats'             => $stats_result,
        ];
    }

    public static function scopeGetLeadsWonBreakdown($query, $request){
        $data = parent::join('leads', 'leads.id', '=', 'lead_escalations.lead_id')
        ->join('customers', 'leads.customer_id', '=', 'customers.id')
        ->join('addresses', 'addresses.id', '=', 'customers.address_id')
        ->select('leads.id', 'source', 'lead_escalations.escalation_level')
        ->selectRaw('IF(source IS NULL or source = "" or source = " ", "No Medium Chosen", REPLACE(LCASE(source),"–","-") ) as source')
        ->selectRaw("IF(state IS NULL or state = '', 'Unresolved', UPPER(state) ) as state")
        ->selectRaw("
            IFNULL(leads.gutter_edge_meters, 0) + IFNULL(leads.valley_meters, 0) as total_meters
        ")
        ->selectRaw("
            (IFNULL(lead_escalations.gutter_edge_meters, 0) + IFNULL(lead_escalations.valley_meters, 0)) as total_meters_actual
        ")
        ->whereIn('addresses.state', ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA'])
        ->where('leads.customer_type', 'SUPPLY & INSTALL')
        ->where(function($q) use($request){
            //query date between from and to
            if(isset($request['from']) && isset($request['to']) && ! empty($request['from']) && !empty($request['to'])){
                $from = date('Y-m-d', strtotime($request['from']));
                $to = date('Y-m-d', strtotime($request['to']));

                $q->whereBetween(\DB::raw('DATE_FORMAT(lead_escalations.created_at, "%Y-%m-%d")'), [$from, $to]);
            }
            //query date by from to greater
            else if(isset($request['from']) && ! empty($request['from'])){
                $from = date('Y-m-d', strtotime($request['from']));
                $q->where(\DB::raw('DATE_FORMAT(lead_escalations.created_at, "%Y-%m-%d")'), '>=', $from);
            }
            //query date by to less than
            else if(isset($request['to']) && ! empty($request['to'])){
                $to = date('Y-m-d', strtotime($request['to']));
                $q->where(\DB::raw('DATE_FORMAT(lead_escalations.created_at, "%Y-%m-%d")'), '<=', $to);
            }

            if(isset($request['state']) && ! empty($request['state'])){
                $state = $request['state'];
                if($request['state'] != 'All States'){
                    if($request['state'] == 'Undefined' || $request['state'] == 'Unresolved'){
                        $q->whereNull('addresses.state');
                    }else{
                        $q->where('addresses.state', $request['state']);
                    }
                }
            }

            if(isset($request['keyword'])){
                $q->orWhere('addresses.state', 'like', '%' . $request['keyword'] . '%');
                $q->orWhere('source', 'like', '%' . $request['keyword'] . '%');
            }
        })
        ->orderBy('source', 'asc')->get();

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

    public function scopeSetPausedTimers($query)
    {
        $now = Carbon::now();

        $query
        ->whereRaw('expiration_date <> ""');

        $old_paused = \DB::table('lead_escalations')
        ->whereRaw('expiration_date <> ""')
        ->get()
        ->first()
        ->paused_time;

        // PAUSE DATES
        $dates = [
            'start' => [],
            'end' => [],
        ];

        $timeSettings = TimeSetting::all();
        foreach($timeSettings as $timeSetting){
            if($timeSetting->is_active){
                if($timeSetting->type == "recurring"){
                    $start_recurring = new \DateTime( $timeSetting->start_day );
                    $stop_recurring = new \DateTime( $timeSetting->stop_day );

                    $interval_recurring = new \DateInterval('P1D');
                    $daterange_recurring = new \DatePeriod($start_recurring, $interval_recurring, $stop_recurring);

                    if(!empty($daterange_recurring)){

                        $dates['start']= Carbon::parse($timeSetting->start_day );
                        $dates['end'] = Carbon::parse($timeSetting->stop_day );
                    }
                }
                else{
                    $start_onetime = new \DateTime( $timeSetting->start_date );
                    $stop_onetime = new \DateTime( $timeSetting->stop_date );

                    $interval_onetime = new \DateInterval('P1D');
                    $daterange_onetime = new \DatePeriod($start_onetime, $interval_onetime, $stop_onetime);

                    if(!empty($daterange_onetime)){
                        $dates['start'] = Carbon::parse($timeSetting->start_date );
                        $dates['end'] = Carbon::parse($timeSetting->stop_date );
                    }
                }
            }
        }


        $extension = $timeSettings ? Carbon::parse($old_paused)->diffInMilliseconds($dates['end']) : 0;
        $difference = Carbon::parse($old_paused)->diffInMilliseconds($now);

        $query->update([
            'paused_time' => $now->subMilliseconds($difference),
            'extended_time' => $extension,
            'added_time' => $old_paused ? $old_paused : $now
        ]);
    }


    public function scopeRemovePausedTimers($query)
    {
        $now = Carbon::now();

        $query
        ->whereRaw('expiration_date <> ""');

        $old_paused = \DB::table('lead_escalations')
        ->whereRaw('expiration_date <> ""')
        ->get()
        ->first()
        ->paused_time;

        $timeSettings = TimeSetting::where('is_active', true)->get();
        if ($timeSettings->count() > 0 ) {
            $extension = $now->diffInMilliseconds(Carbon::parse($old_paused));
            $query->update([
                'paused_time' => null,
                'extended_time' => $extension,
                'added_time' => $old_paused ? $old_paused : $now
            ]);
        }
    }

    // Org Stats Breakdown Data
    public function scopeGetReportOrganisationBreakDown($query, $request){
        $is_org_stat = true;
        $from = "";
        $to = "";
        $now = "";
        $inc_raw = "";
        $stat_array = [
            'is_org_stat' => false
        ];

        $is_org_stat = ( isset( $request['type'] ) && $request['type'] == 'org_stat' ) ? true : false;

        /* if ( isset( $request['type'] ) && $request['type'] == 'org_stat' ) {
            $is_org_stat = true;

            if ( ! empty( $request['from'] ) && ! empty( $request['to'] ) ) {
                $from = Carbon::parse( $request['from'] )->toDateString();
                $to = Carbon::parse( $request['to'] )->toDateString();

                $inc_raw = " AND lead_escalations.created_at BETWEEN '$from 00:00:01' AND '$to 23:59:59'";

                $stat_array = [
                    'from' => $from,
                    'to' => $to,
                    'where_type' => 'between',
                    'is_org_stat' => true
                ];

            } else if ( isset( $request['days'] ) && $request['days'] !== NULL ) {
                $days = $request['days'];
                $now = Carbon::now()->subDays( intval( $days ) )->toDateString();

                $inc_raw = " AND lead_escalations.created_at >= '$now 00:00:01'";

                $stat_array = [
                    'now' => $now,
                    'where_type' => 'days',
                    'is_org_stat' => true
                ];
            }
        } */

        $months = [ Carbon::now(), Carbon::now()->subMonths( 1 ), Carbon::now()->subMonths( 2 ), Carbon::now()->subMonths( 6 ) ];
        $months_format = [
            'month_' . strtolower( Carbon::now()->format( 'M' ) ),
            'month_' . strtolower( Carbon::now()->subMonths( 1 )->format( 'M' ) ),
            'month_' . strtolower( Carbon::now()->subMonths( 2 )->format( 'M' ) ),
            'month_' . strtolower( Carbon::now()->subMonths( 6 )->format( 'M' ) ),
        ];
        $_months = [];

        foreach( $months as $_index => $month ) {
            if ( $_index + 1 == 4 ) {

                $from = $month->firstOfMonth()->toDateString() . ' 00:00:01';
                $to = $months[0]->endOfMonth()->toDateString() . ' 23:59:59';

            } else {

                $from = $month->firstOfMonth()->toDateString() . ' 00:00:01';
                $to = $month->endOfMonth()->toDateString() . ' 23:59:59';

            }

            $new_inc_raw = " AND lead_escalations.created_at BETWEEN '$from' AND '$to'";
            array_push( $_months, $new_inc_raw );
        }

        $query
            ->leftJoin('leads', function($join) use($request){
                $join->on('lead_escalations.lead_id', '=', 'leads.id');
            })
            ->leftJoin('organisations', function($join) use($stat_array){
                $join->on('lead_escalations.organisation_id', '=', 'organisations.id')
                ->whereNull('lead_escalations.deleted_at');
                    // ->where('lead_escalations.is_active', 1);
            })
            ->leftJoin('addresses', function($join){
                $join->on('organisations.address_id', '=', 'addresses.id');
            })
            ->leftJoin('users', function($join){
                $join->on('organisations.user_id', '=', 'users.id');
            })
            ->select('lead_escalations.organisation_id', 'organisations.name', 'organisations.metadata', 'organisations.priority','addresses.state', 'organisations.org_code', 'organisations.contact_number', 'users.email',
                // \DB::raw("
                //     (SELECT COUNT(*) FROM lead_escalations
                //     WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                //     GROUP BY lead_escalations.organisation_id) as lead_count
                // "),
                \DB::raw("
                    (SELECT COUNT(*) FROM lead_escalations inner join
                    leads on leads.id = lead_escalations.lead_id
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and leads.deleted_at IS NULL and leads.customer_type = 'Supply & Install'
                    GROUP BY lead_escalations.organisation_id) as lead_count
                "),
                \DB::raw("
                    (SELECT COUNT(*) FROM lead_escalations
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1
                    AND lead_escalations.deleted_at  IS NULL
                    $_months[0]
                    GROUP BY lead_escalations.organisation_id) as {$months_format[0]}
                "),
                \DB::raw("
                    (SELECT COUNT(*) FROM lead_escalations
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1
                    AND lead_escalations.deleted_at IS NULL
                    $_months[1]
                    GROUP BY lead_escalations.organisation_id) as {$months_format[1]}
                "),
                \DB::raw("
                    (SELECT COUNT(*) FROM lead_escalations
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1
                    AND lead_escalations.deleted_at IS NULL
                    $_months[2]
                    GROUP BY lead_escalations.organisation_id) as {$months_format[2]}
                "),
                \DB::raw("
                    (SELECT COUNT(*) FROM lead_escalations
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1
                    AND lead_escalations.deleted_at IS NULL
                    $_months[3]
                    GROUP BY lead_escalations.organisation_id) as month_six
                "),
            )
            // ->selectRaw("
            //     (SELECT COUNT(*) FROM lead_escalations
            //         WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
            //         AND lead_escalations.escalation_level = 'Won'
            //         $inc_raw
            //         GROUP BY lead_escalations.organisation_id) as won_count
            // ")
            ->selectRaw("
                (SELECT COUNT(*) FROM lead_escalations inner join
                leads on leads.id = lead_escalations.lead_id
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and leads.deleted_at IS NULL and leads.customer_type = 'Supply & Install'
                AND lead_escalations.escalation_level = 'Won'
                $inc_raw
                GROUP BY lead_escalations.organisation_id) as won_count
            ")
            ->selectRaw("
                (SELECT ( SUM(IFNULL(lead_escalations.gutter_edge_meters, 0)) + SUM(IFNULL(lead_escalations.valley_meters, 0)) ) FROM lead_escalations
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1
                    AND lead_escalations.escalation_level = 'Won' AND deleted_at IS NULL
                    GROUP BY lead_escalations.organisation_id) as installed_meters
            ")
            // ->selectRaw("
            //     (SELECT COUNT(*) FROM lead_escalations
            //         WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
            //         AND lead_escalations.escalation_level = 'Lost'
            //         $inc_raw
            //         GROUP BY lead_escalations.organisation_id) as lost_count
            // ")
            ->selectRaw("
                (SELECT COUNT(*) FROM lead_escalations inner join
                    leads on leads.id = lead_escalations.lead_id
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and leads.deleted_at IS NULL and leads.customer_type = 'Supply & Install'
                    AND lead_escalations.escalation_level = 'Lost' AND lead_escalations.escalation_status = 'Lost'
                    $inc_raw
                    GROUP BY lead_escalations.organisation_id) as lost_count
            ")
            // ->selectRaw("
            //     (SELECT COUNT(*) FROM lead_escalations
            //         WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
            //         AND lead_escalations.escalation_level NOT IN ('Lost', 'Won')
            //         $inc_raw
            //         GROUP BY lead_escalations.organisation_id) as unallocated_count
            // ")
            ->selectRaw("
                (SELECT COUNT(*) FROM lead_escalations inner join
                    leads on leads.id = lead_escalations.lead_id
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and leads.deleted_at IS NULL and leads.customer_type = 'Supply & Install'
                    AND lead_escalations.escalation_level NOT IN ('Lost', 'Won')
                    $inc_raw
                    GROUP BY lead_escalations.organisation_id) as unallocated_count
            ")
            ->selectRaw("
                CASE
                    WHEN lead_escalations.escalation_level = 'Won' THEN 'Won'
                    WHEN lead_escalations.escalation_level = 'Lost' THEN 'Lost'
                    ELSE 'Unresolved'
                END AS status
            ")
            ->selectRaw("
                CONCAT(FORMAT(((count(organisations.id)
                /
                (SELECT COUNT(*) FROM lead_escalations
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                GROUP BY lead_escalations.organisation_id) * 100)),2),'%') as percent
            ")
            ->selectRaw("
                CONCAT(FORMAT(((SELECT COUNT(*) FROM lead_escalations
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                AND lead_escalations.escalation_level = 'Won'
                $inc_raw
                GROUP BY lead_escalations.organisation_id) /
                (SELECT COUNT(*) FROM lead_escalations
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                GROUP BY lead_escalations.organisation_id)*100),2),'%') as percent_won

            ")
            ->selectRaw("
                CONCAT(FORMAT(((SELECT COUNT(*) FROM lead_escalations
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                AND lead_escalations.escalation_level = 'Lost'
                $inc_raw
                GROUP BY lead_escalations.organisation_id) /
                (SELECT COUNT(*) FROM lead_escalations
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                GROUP BY lead_escalations.organisation_id)*100),2),'%') as percent_lost

            ")
            ->selectRaw("
                CONCAT(FORMAT(((SELECT COUNT(*) FROM lead_escalations
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                AND lead_escalations.escalation_level NOT IN ('Lost', 'Won')
                $inc_raw
                GROUP BY lead_escalations.organisation_id) /
                (SELECT COUNT(*) FROM lead_escalations
                WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                GROUP BY lead_escalations.organisation_id)*100),2),'%') as percent_unallocated

            ")
            ->selectRaw("
            (SELECT ( SUM(IFNULL(lead_escalations.gutter_edge_meters, 0)) + SUM(IFNULL(lead_escalations.valley_meters, 0)) ) FROM lead_escalations
                    WHERE lead_escalations.organisation_id = organisations.id AND lead_escalations.is_active = 1 and deleted_at IS NULL
                    AND lead_escalations.escalation_level = 'Won'
                    GROUP BY lead_escalations.organisation_id) as installed_metersff
            ")
            ->whereIn('addresses.state', ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA', ''])
            //->orWhereNull('addresses.state')
            ->where(function($q) use($request, $is_org_stat){

                if ( ! $is_org_stat ) {
                    $q->where('is_active', 1);
                    if (isset($request['state'])) {
                        $state = $request['state'];
                        //if all state dont query address state
                        if ($request['state'] != 'All States') {
                            $q->where('addresses.state', $state);
                        }
                    }

                    if (isset($request['keyword'])) {
                        $q->orWhere('name', 'like', '%' . $request['keyword'] . '%');
                        $q->orWhere('addresses.state', 'like', '%' . $request['keyword'] . '%');
                    }

                    //query date between from and to
                    if (isset($request['from']) && isset($request['to'])) {
                        $from = date('Y-m-d', strtotime($request['from']));
                        $to = date('Y-m-d', strtotime($request['to']));

                        $q->whereBetween(\DB::raw('DATE_FORMAT(lead_escalations.created_at, "%Y-%m-%d")'), [$from, $to]);
                    }
                    //query date by from to greater
                    else if (isset($request['from'])) {
                        $from = date('Y-m-d', strtotime($request['from']));
                        $q->where(\DB::raw('DATE_FORMAT(lead_escalations.created_at, "%Y-%m-%d")'), '>=', $from);
                    }
                    //query date by to less than
                    else if (isset($request['to'])) {
                        $to = date('Y-m-d', strtotime($request['to']));
                        $q->where(\DB::raw('DATE_FORMAT(lead_escalations.created_at, "%Y-%m-%d")'), '<=', $to);
                    }

                } else {
                    $q->whereIn( 'organisations.id', $request['ids'] );
                }
            })
            ->whereNotNull('name')
            // ->where('lead_escalations.is_active', 1)
            ->groupBy('status')
            ->groupBy('organisations.id')
            ->orderBy('organisations.name', 'asc');

            return $query;
    }

    public function scopeActive($query, $is_activie = 0){
        $query->where('is_active', $is_activie);

        return $query;
    }

    public function scopeFilters($query, $filters) {
        $filters['lead_group'] = (isset($filters['lead_group'])) ? $filters['lead_group'] : 0;

        if(isset($filters['lead_group']) && $filters['lead_group'] == 0){
            if ( isset($filters['lead_type'] ) && ! empty( $filters['lead_type'] ) ) {
                $query->whereHas('lead', function ($q) use ($filters) {
                    $q->where('customer_type', $filters['lead_type']);
                });
            }

            if (isset($filters['escalation_level']) && !empty($filters['escalation_level'])) {
                $query->where('escalation_level', $filters['escalation_level']);
            }

            if (isset($filters['lead_escalation_status']) && !empty($filters['lead_escalation_status'])) {
                if($filters['lead_escalation_status'] == 'Extended'){
                    $query->where('escalation_status', 'LIKE', "%{$filters['lead_escalation_status']}%");
                }else{
                    $query->where('escalation_status', "{$filters['lead_escalation_status']}");
                }
            }

            if (isset($filters['organisation_id']) && !empty($filters['organisation_id'])) {
                $query->where('organisation_id', $filters['organisation_id'])->where('leads.customer_type', 'Supply & Install');
            }

            if (isset($filters['postcode']) && !empty($filters['postcode'])) {
                $query->whereHas('lead.customer.address', function ($q) use ($filters) {
                    $q->where( 'postcode', 'LIKE', $filters['postcode'] . '%' );
                    $q->orWhere( 'suburb', 'LIKE', $filters['postcode'] . '%' );
                });
            }

            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = $filters['search'];

                if ( is_numeric( $search ) ) {

                    $query->where( 'lead_escalations.is_active', 1 );

                    $query->whereHas( 'lead', function ( $q ) use ( $search ) {
                        $q->where( 'leads.lead_id', 'LIKE', $search . '%' );
                    });

                    $query->orWhere( 'lead_escalations.lead_id', $search );

                } else {
                    $query->whereHas('lead.customer', function ($q) use ($search) {
                        $q->where(\DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', "%{$search}%");
                        $q->orWhere('email', 'LIKE', "%{$search}%");
                    });
                }
            }
        }else if(isset($filters['lead_group']) && $filters['lead_group'] == 1){

            if ( isset($filters['lead_type'] ) && ! empty( $filters['lead_type'] ) ) {
                $query->whereHas('lead', function ($q) use ($filters) {
                    $q->where('customer_type', $filters['lead_type']);
                });
            }

            // level and status are not empty
            if(isset($filters['escalation_level']) && !empty($filters['escalation_level']) && isset($filters['lead_escalation_status']) && !empty($filters['lead_escalation_status'])){
                $query->where('lead_escalations.escalation_level', $filters['escalation_level'])->where('lead_escalations.is_active', 1);

                if($filters['lead_escalation_status'] == 'Extended'){
                    $query->where('escalation_status', 'LIKE', "%{$filters['lead_escalation_status']}%")->where('lead_escalations.is_active', 1);
                }else{
                    $query->where('escalation_status', "{$filters['lead_escalation_status']}")->where('lead_escalations.is_active', 1);
                }
            }else{
                if(!isset($filters['escalation_level']) && empty($filters['escalation_level']) && !isset($filters['lead_escalation_status']) && empty($filters['lead_escalation_status'])){
                    // if (! isset($filters['lead_type'] ) && empty( $filters['lead_type'] ) ) {
                    //     $query->orWhereIn('lead_escalations.id', $this->getLeadNeedAttentions());
                    // }
                    $query->whereIn('lead_escalations.id', $this->getLeadNeedAttentions());
                }else{
                    if (isset($filters['escalation_level']) && !empty($filters['escalation_level'])) {
                        $query->where('lead_escalations.escalation_level', $filters['escalation_level'])->where('lead_escalations.is_active', 1);
                    }

                    if (isset($filters['lead_escalation_status']) && !empty($filters['lead_escalation_status'])) {
                        if($filters['lead_escalation_status'] == 'Extended'){
                            $query->where('escalation_status', 'LIKE', "%{$filters['lead_escalation_status']}%")->where('lead_escalations.is_active', 1);
                        }else{
                            $query->where('escalation_status', "{$filters['lead_escalation_status']}")->where('lead_escalations.is_active', 1);
                        }
                    }
                }
            }

            if (isset($filters['organisation_id']) && !empty($filters['organisation_id'])) {
                $query->where('organisation_id', $filters['organisation_id']);
            }

            if (isset($filters['postcode']) && !empty($filters['postcode'])) {
                $query->whereHas('lead.customer.address', function ($q) use ($filters) {
                    $q->where( 'postcode', 'LIKE', $filters['postcode'] . '%' );
                    $q->orWhere( 'suburb', 'LIKE', $filters['postcode'] . '%' );
                });
            }

            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = $filters['search'];

                if ( is_numeric( $search ) ) {

                    $query->where( 'lead_escalations.is_active', 1 );

                    $query->whereHas( 'lead', function ( $q ) use ( $search ) {
                        $q->where( 'leads.lead_id', 'LIKE', $search . '%' );
                    });

                    $query->orWhere( 'lead_escalations.lead_id', $search );

                } else {
                    $query->whereHas('lead.customer', function ($q) use ($search) {
                        $q->where(\DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', "%{$search}%");
                        $q->orWhere('email', 'LIKE', "%{$search}%");
                    });
                }
            }
        }
        else if(isset($filters['lead_group']) && $filters['lead_group'] == 2){
            $query->whereJsonContains('leads.user_ids', Auth::user()->id)->where('lead_escalations.is_active', 1);

            if ( isset($filters['lead_type'] ) && ! empty( $filters['lead_type'] ) ) {
                $query->whereHas('lead', function ($q) use ($filters) {
                    $q->where('customer_type', $filters['lead_type']);
                });
            }

            if (isset($filters['escalation_level']) && !empty($filters['escalation_level'])) {
                $query->where('escalation_level', $filters['escalation_level']);
            }

            if (isset($filters['lead_escalation_status']) && !empty($filters['lead_escalation_status'])) {
                if($filters['lead_escalation_status'] == 'Extended'){
                    $query->where('escalation_status', 'LIKE', "%{$filters['lead_escalation_status']}%");
                }else{
                    $query->where('escalation_status', "{$filters['lead_escalation_status']}");
                }
            }

            if (isset($filters['organisation_id']) && !empty($filters['organisation_id'])) {
                $query->where('organisation_id', $filters['organisation_id']);
            }

            if (isset($filters['postcode']) && !empty($filters['postcode'])) {
                $query->whereHas('lead.customer.address', function ($q) use ($filters) {
                    $q->where( 'postcode', 'LIKE', $filters['postcode'] . '%' );
                    $q->orWhere( 'suburb', 'LIKE', $filters['postcode'] . '%' );
                });
            }

            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = $filters['search'];

                if ( is_numeric( $search ) ) {

                    $query->where( 'lead_escalations.is_active', 1 );

                    $query->whereHas( 'lead', function ( $q ) use ( $search ) {
                        $q->where( 'leads.lead_id', 'LIKE', $search . '%' );
                    });

                    $query->orWhere( 'lead_escalations.lead_id', $search );

                } else {
                    $query->whereHas('lead.customer', function ($q) use ($search) {
                        $q->where(\DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', "%{$search}%");
                        $q->orWhere('email', 'LIKE', "%{$search}%");
                    });
                }
            }
        }

        // if(!empty($filters['organisation_id']) && empty($filters['lead_type']) && empty($filters['escalation_level']) && empty($filters['lead_escalation_status']) && empty($filters['postcode'])){
        if(!empty($filters['organisation_id']) && !empty($filters['reassinged'])){
            $query->select('*')
                ->whereIn('lead_escalations.id', function($q) use($filters){
                    $q->selectRaw('max(lead_escalations.id)')->from('lead_escalations')
                    ->where('lead_escalations.organisation_id', $filters['organisation_id'])
                    ->groupBy('lead_id');
                });
        }else{
            $query->where('is_active', 1);
        }

        return $query;
    }

    private function getLeadNeedAttentions(){
        $need_attention_status = ['Declined-Lapsed', 'Declined', 'Discontinued', 'Special Opportunity', 'Unassigned', 'Awaiting Response - Admin Notified',
                'Parked', 'Supply', 'Supply Only', 'General Enquiry'];

        $need_attention_level = ['New', 'In Progress'];

        $lead_escalation_status = [
            'Unassigned - Unassigned',
            'Unassigned - Special Opportunity',
            'Accept Or Decline - Declined-Lapsed',
            'Accept or Decline - Declined',
            'Confirm Enquirer Contacted - Discontinued',
            'Confirm Enquirer Contacted - Awaiting Response - Admin Notified',
            'In Progress - Discontinued',
            'In Progress - Awaiting Response - Admin Notified',
            'In Progress - Parked',
            'In Progress - General Enquiry',
            'In Progress - Supply Only',
            'New - Supply Only',
            'New - General Enquiry',
        ];

        $query = parent::select('id', \DB::raw('CONCAT(escalation_level, " - ", escalation_status) as lead_escalation_status'));
        $query->whereIn('lead_escalations.escalation_level', $need_attention_level)->where('lead_escalations.is_active', 1);
        $query->orWhereIn('lead_escalations.escalation_status', $need_attention_status)->where('lead_escalations.is_active', 1);
        $leads = $query->get();

        $lead_ids = array();

        foreach($leads as $row){
            if(in_array($row->lead_escalation_status, $lead_escalation_status)){
                $lead_ids[] = $row->id;
            }
        }
        return $lead_ids;
    }

    public function scopeReassignedLeads($query, $filters){
        $query->where('organisation_id', $filters['organisation_id']);
        $query->select('*')
            ->whereIn('lead_escalations.id', function($q) use($filters){
                $q->selectRaw('max(lead_escalations.id)')->from('lead_escalations')
                    ->where('lead_escalations.organisation_id', $filters['organisation_id'])
                    ->groupBy('lead_id');
            });

        return $query;
    }

    public function scopeGetReportMediumBreakdown($query, $request){
        $total_leads = parent::all()->count();

        \DB::statement('SET SESSION group_concat_max_len = 1000000');

        $query->join('leads', function($join) use($request){
            $join->on('lead_escalations.lead_id', '=', 'leads.id')
                ->where('is_active', 1);
                if(isset($request['from']) && isset($request['to'])){
                    $from = date('Y-m-d', strtotime($request['from']));
                    $to = date('Y-m-d', strtotime($request['to']));

                    $join->whereBetween(\DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m-%d")'), [$from, $to]);
                }
        })->join('customers', function($join){
            $join->on('leads.customer_id', '=', 'customers.id');
        })->join('addresses', function($join) use($request){
            if(isset($request['state'])){
                $state = $request['state'];
                //if all state dont query address state
                if($request['state'] == 'None'){
                    $join->on('customers.address_id', '=', 'addresses.id')->where('addresses.state', '');
                }
                else if($request['state'] != 'All States'){
                    $join->on('customers.address_id', '=', 'addresses.id')->where('addresses.state', $state);
                }else{
                    $join->on('customers.address_id', '=', 'addresses.id');
                }
            }else{
                $join->on('addresses.id', '=', 'customers.address_id');
            }
        })->select('leads.id', 'source', 'leads.created_at')
        ->selectRaw('IF(source IS NULL or source = "" or source = " ", "None Chosen", REPLACE(LCASE(source),"–","-") ) as sources')
        ->selectRaw('GROUP_CONCAT(UPPER(IF(CHAR_LENGTH(addresses.state)>0, addresses.state, "none")) SEPARATOR ", ") as states')
        ->selectRaw('count(*) as count_source')
        ->selectRaw('CONCAT((FORMAT((count(*) / ' . $total_leads . ')*100, 2)), \'%\') as percentage')
        ->whereIn('addresses.state', ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA', ''])
        ->orWhereNull('addresses.state')
        ->groupBy(['sources'])
        ->orderByRaw("FIELD(sources, 'None Chosen'), count_source DESC");

        return $query;
    }

    public function scopeGetSortedLeads($query, $filters=null) {
        $user = auth()->user();

        $ip_extended = '';

        if(!\Cache::get('extended_string')) {
            for($x = 200; $x > 0; $x--) {
                $ip_extended .= "'In Progress - Extended {$x}',";
            }

            \Cache::put('extended_string', $ip_extended);
        } else {
            $ip_extended = \Cache::get('extended_string');
        }

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            $query->with(['lead'])
            ->join('leads', 'lead_escalations.lead_id', 'leads.id')
            ->join('customers', 'leads.customer_id', 'customers.id')
            ->join('addresses', 'customers.address_id', 'addresses.id')
            ->select(
                \DB::raw('CONCAT(escalation_level, " - ", escalation_status) as lead_escalation_status'),
                'lead_escalations.*',
                'leads.created_at'
            );

            if ( isset($filters['lead_type'] ) && !empty( $filters['lead_type'] ) && in_array( $filters['lead_type'], ['Supply Only', 'General Enquiry'] ) ) {
                $query->whereIn('state', ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA', ''])
                    ->orWhereNull('state')
                    ->orderByRaw(
                    "FIELD(lead_escalation_status,
                        'Accept Or Decline - Declined-Lapsed',
                        'Accept Or Decline - Declined',
                        'Declined - Declined',
                        'Confirm Enquirer Contacted - Discontinued',
                        'In Progress - Discontinued',
                        'Unassigned - Special Opportunity',
                        'Unassigned - Unassigned',
                        'Lost - Inconclusive',
                        'Confirm Enquirer Contacted - Awaiting Response - Admin Notified',
                        'In Progress - Awaiting Response - Admin Notified',
                        'New - Supply Only',
                        'New - General Enquiry',
                        'Confirm Enquirer Contacted - Awaiting Response - Reminder Sent',
                        'In Progress - Awaiting Response - Reminder Sent',
                        'Accept Or Decline - Pending',
                        'Confirm Enquirer Contacted - Awaiting Response',
                        'In Progress - Awaiting Response',
                        " . $ip_extended . "
                        'In Progress - Parked',
                        'Lost - Lost',
                        'Won - Won',
                        'In Progress - Supply Only',
                        'In Progress - General Enquiry',
                        'Finalized - Supply Only',
                        'Finalized - General Enquiry',
                        'Supply Only - SP',
                        'Unassigned - Lost'
                    )"
                )
                ->orderBy('leads.created_at', 'ASC');
            } else {
                $query->whereIn('state', ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA', ''])
                    ->orWhereNull('state')
                    ->orderByRaw(
                    "FIELD(lead_escalation_status,
                        'Accept Or Decline - Declined-Lapsed',
                        'Accept Or Decline - Declined',
                        'Declined - Declined',
                        'Discontinued - Discontinued',
                        'Confirm Enquirer Contacted - Discontinued',
                        'In Progress - Discontinued',
                        'Abandoned - Abandoned',
                        'Unassigned - Special Opportunity',
                        'Unassigned - Unassigned',

                        'Confirm Enquirer Contacted - Awaiting Response - Admin Notified',
                        'In Progress - Awaiting Response - Admin Notified',
                        'New - Supply Only',
                        'New - General Enquiry',
                        'Confirm Enquirer Contacted - Awaiting Response - Reminder Sent',
                        'In Progress - Awaiting Response - Reminder Sent',
                        'Accept Or Decline - Pending',
                        'Confirm Enquirer Contacted - Awaiting Response',
                        'In Progress - Awaiting Response',
                        " . $ip_extended . "
                        'In Progress - Parked',
                        'Lost - Inconclusive',
                        'Lost - Lost',
                        'Won - Won',
                        'In Progress - Supply Only',
                        'In Progress - General Enquiry',
                        'Finalized - Supply Only',
                        'Finalized - General Enquiry',
                        'Supply Only - SP',
                        'Unassigned - Lost'
                    ), lead_escalations.expiration_date ASC"
                )
                ->orderBy('leads.created_at', 'asc');
            }
        }else{
            $query->with('lead')
                ->join('leads', 'lead_escalations.lead_id', 'leads.id')
                ->select(
                    \DB::raw('CONCAT(escalation_level, " - ", escalation_status) as lead_escalation_status'),
                    'lead_escalations.*'
                );
            if(empty($filters['sorted_by']) || !isset($filters['sorted_by']) || $filters['sorted_by'] == 'Default'){
                $query->orderByRaw(
                    "FIELD(lead_escalation_status,
                        'Accept Or Decline - Pending',
                        'Accept Or Decline - Declined-Lapsed',
                        'In Progress - Parked',
                        'Confirm Enquirer Contacted - Awaiting Response - Admin Notified',
                        'In Progress - Awaiting Response - Admin Notified',
                        'Confirm Enquirer Contacted - Awaiting Response - Reminder Sent',
                        'In Progress - Awaiting Response - Reminder Sent',
                        'Confirm Enquirer Contacted - Awaiting Response',
                        'In Progress - Awaiting Response',
                        " . $ip_extended . "
                        'Lost - Lost',
                        'Won - Won',
                        'Lost - Inconclusive',
                        'Discontinued - Discontinued',
                        'Confirm Enquirer Contacted - Discontinued',
                        'Discontinued - Discontinued',
                        'In Progress - Discontinued',
                        'Abandoned - Abandoned',
                        'Accept Or Decline - Declined',
                        'Declined - Declined'
                    ), lead_escalations.expiration_date ASC"
                )->orderBy('leads.created_at', 'asc');
            }else if($filters['sorted_by'] == 'Timer'){
                $extended = ['Pending', 'Awaiting Response - Email Sent', 'Awaiting Response - Reminder Sent', 'Awaiting Response'];
                for($x = 200; $x > 0; $x--) {
                    $extended[] = "Extended {$x}";
                }
                $query->whereIn('lead_escalations.escalation_status', $extended);
                $query->orderByRaw('CONVERT(lead_escalations.expiration_date, DATETIME)', 'ASC');
            }else if($filters['sorted_by'] == 'Date'){
                $query->orderBy('leads.created_at', 'DESC');
            }
        }

        return $query;
    }

    public static function leadEscalationStatus($lead_escalation_status) {
        $color = [
            'Unassigned - Unassigned'                                           => 'purple',
            'Unassigned - Special Opportunity'                                  => 'purple',
            'Unassigned - Lost'                                                 => 'red',
            'Accept Or Decline - Pending'                                       => 'blue',
            'Supply Only - New'                                                 => 'blue',
            'New - Supply Only'                                                 => 'blue',
            'New - Supply Only(SP)'                                             => 'blue',
            'New - General Enquiry'                                             => 'blue',
            'In Progress - General Enquiry'                                     => 'orange',
            'Finalized - General Enquiry'                                       => 'green',
            'Accept Or Decline - Declined'                                      => 'red',
            'Accept Or Decline - Declined-Lapsed'                               => 'red',
            'Confirm Enquirer Contacted - Awaiting Response'                    => 'yellow',
            'Confirm Enquirer Contacted - Awaiting Response - Reminder Sent'    => 'yellow',
            'Confirm Enquirer Contacted - Awaiting Response - Admin Notified'   => 'yellow',
            'Confirm Enquirer Contacted - Discontinued'                         => 'red',
            'Confirm Enquirer Contacted - Declined'                             => 'red',
            'Declined - Declined'                                               => 'red',
            'In Progress - Awaiting Response'                                   => 'orange',
            'In Progress - Extended 1'                                          => 'orange',
            'In Progress - Extended 2'                                          => 'orange',
            'In Progress - Extended 3'                                          => 'orange',
            'In Progress - Extended'                                            => 'orange',
            'In Progress - Awaiting Response - Reminder Sent'                   => 'orange',
            'In Progress - Awaiting Response - Admin Notified'                  => 'orange',
            'In Progress - Supply Only(SP)'                                     => 'orange',
            'In Progress - Supply Only'                                         => 'orange',
            'In Progress - Parked'                                              => 'orange',
            'In Progress - Discontinued'                                        => 'red',
            'Discontinued - Discontinued'                                       => 'red',
            'Abandoned - Abandoned'                                             => 'red',
            'Lost - Lost'                                                       => 'red',
            'Lost - Inconclusive'                                               => 'red',
            'Lost - Supply Only'                                                => 'red',
            'Won - Won'                                                         => 'green',
            'Won - Supply Only'                                                 => 'green',
            'Confirm Enquirer Contacted - Supply Only(SP)'                      => 'yellow',
            'Confirm Enquirer Contacted - Supply Only'                          => 'yellow',
            'In Progress - Supply Only(SP)'                                     => 'orange',
            'In Progress - Supply Only'                                         => 'orange',
            'Finalized - Supply Only(SP)'                                       => 'green',
            'Finalized - Supply Only'                                           => 'green',
            'Supply Only - SP'                                                  => 'green',
            'Supply Only - SP'                                                  => 'green',
        ];

        if(strpos($lead_escalation_status, 'In Progress - Extended') !== false) {
            return 'orange';
        }

        return $color[$lead_escalation_status] ?? false;
    }

    public static function getConfirmationMessage($esc_key, $time_value, $time_type) {
        $esc_key = str_replace('-', '_', $esc_key);

        # localize time format
        $time_format = __("messages.{$time_type}");

        # format confirmation message
        $message = __('messages.' . $esc_key . '_confirmation', ['time' => "{$time_value} {$time_format}"]);

        # return false if localize message doesn't exists
        return strpos($message, 'message.') === false ? $message : false;
    }

    public static function getExemptedEscalationLevelAndStatus($lead_escalation_status) {
        $exempted = [
            'Confirm Enquirer Contacted - Awaiting Response',
        ];

        return in_array($lead_escalation_status, $exempted);
    }

    public static function isSuspendableEscalationStatus($lead_escalation_status) {
        $suspendable_status = [
            'Accept Or Decline - Declined-Lapsed',
            'Confirm Enquirer Contacted - Awaiting Response - Admin Notified',
            'In Progress - Awaiting Response - Admin Notified',
        ];

        return in_array($lead_escalation_status, $suspendable_status);
    }

    public static function getCurrentLeadsByOrganization($org_id){
        $data = array();

        $current = parent::select('organisation_id')
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null) as assigned_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null and escalation_status Not In('Discontinued', 'Lost', 'Won', 'Abandoned', 'Declined', 'Inconclusive')) as unresolved_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null and escalation_status='Lapsed') as lapsed_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null and escalation_status='Declined') as declined_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null and escalation_status='Discontinued') as discontinued_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null and escalation_level='Won') as won_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null and escalation_level='Lost') as lost_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and is_active=1 and deleted_at is null and escalation_status='Inconclusive') as inconclusive_count")
        ->where(['organisation_id' => $org_id, 'is_active' => 1])
        ->groupBy('organisation_id')
        ->get()
        ->makeHidden(['time_left', 'min_extension', 'max_extension', 'expiration_text', 'open_date', 'is_critical', 'earliest_history']);


        $historical[0]['assigned_count'] = parent::select('organisation_id')
        ->selectRaw('COUNT(*) as assigned_count')
        ->whereIn('lead_escalations.id', function($q) use($org_id){
            $q->selectRaw('max(lead_escalations.id)')->from('lead_escalations')
                ->where('lead_escalations.organisation_id', $org_id)
                ->where('lead_escalations.is_active', 0)
                ->groupBy('lead_escalations.lead_id');
        })
        ->count();

        $historical[0]['declined_count'] = parent::select('organisation_id')
        ->selectRaw('COUNT(*) as declined_count')
        ->whereIn('lead_escalations.id', function($q) use($org_id){
            $q->selectRaw('max(lead_escalations.id)')->from('lead_escalations')
                ->where('lead_escalations.organisation_id', $org_id)
                ->where('escalation_status', 'Declined')
                ->where('lead_escalations.is_active', 0)
                ->groupBy('lead_escalations.lead_id');
        })
        ->count();

        $historical[0]['abandoned_count'] = parent::select('organisation_id')
        ->selectRaw('COUNT(*) as declined_count')
        ->whereIn('lead_escalations.id', function($q) use($org_id){
            $q->selectRaw('max(lead_escalations.id)')->from('lead_escalations')
                ->where('lead_escalations.organisation_id', $org_id)
                ->where('escalation_status', 'Abandoned')
                ->where('lead_escalations.is_active', 0)
                ->groupBy('lead_escalations.lead_id');
        })
        ->count();

        $historical[0]['discontinued_count'] = parent::select('organisation_id')
        ->selectRaw('COUNT(*) as declined_count')
        ->whereIn('lead_escalations.id', function($q) use($org_id){
            $q->selectRaw('max(lead_escalations.id)')->from('lead_escalations')
                ->where('lead_escalations.organisation_id', $org_id)
                ->where('escalation_status', 'Discontinued')
                ->where('lead_escalations.is_active', 0)
                ->groupBy('lead_escalations.lead_id');
        })
        ->count();

        $events_how_many_times = parent::select('organisation_id')
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and deleted_at is null and escalation_status='Declined-Lapsed') as lapsed_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and deleted_at is null and escalation_status='Awaiting Response - Reminder Sent') as reminder_sent_count")
        ->selectRaw("(SELECT COUNT(*) from lead_escalations where organisation_id = {$org_id} and deleted_at is null and escalation_status='Awaiting Response - Admin Notified') as admin_notified_count")
        ->where(['organisation_id' => $org_id])
        ->groupBy('organisation_id')
        ->get()
        ->makeHidden(['time_left', 'min_extension', 'max_extension', 'expiration_text', 'open_date', 'is_critical', 'earliest_history']);

        $how_many_times_lapse = parent::where(['organisation_id' => $org_id, 'escalation_status' => 'Declined-Lapsed'])
        ->groupBy('lead_id')
        ->get();

        $how_many_times_lapse = count($how_many_times_lapse);

        $how_many_times_reminder_sent = parent::where(['organisation_id' => $org_id, 'escalation_status' => 'Awaiting Response - Reminder Sent'])
        ->groupBy('lead_id')
        ->get();

        $how_many_times_reminder_sent = count($how_many_times_reminder_sent);

        $how_many_times_admin_notified = parent::where(['organisation_id' => $org_id, 'escalation_status' => 'Awaiting Response - Admin Notified'])
        ->groupBy('lead_id')
        ->get();

        $how_many_times_admin_notified = count($how_many_times_admin_notified);

        $ressigned = parent::select('*')
            ->whereIn('lead_escalations.lead_id', function($q) use($org_id){
                $q->selectRaw('lead_escalations.lead_id')->from('lead_escalations')
                ->where('lead_escalations.organisation_id', $org_id)
                ->where('lead_escalations.is_active', 0)
                ->groupBy('lead_escalations.lead_id');
            })
            ->where('organisation_id', '!=', $org_id)
            ->whereNotIn('escalation_status', ['Unassigned'])
            ->where('is_active', 1)
            ->count();

        $row = array(
            'r0' => '',
            'r1' => '',
            'r2' => '',
            'r3' => '',
            'r4' => '',
            'r5' => 'How many Leads',
            'r6' => 'How many times',
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Assigned',
            'r1' => $current[0]->assigned_count ?? 0,
            'r2' => 'Assigned',
            'r3' => $historical[0]['assigned_count'] ?? 0,
            'r4' => 'Lapse',
            'r5' =>	$how_many_times_lapse ?? 0,
            'r6' => $events_how_many_times[0]->lapsed_count ?? 0,
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Unresolved',
            'r1' => $current[0]->unresolved_count ?? 0,
            'r2' => 'Declined',
            'r3' => $historical[0]['declined_count'] ?? 0,
            'r4' => 'Reminder Sent',
            'r5' =>	$how_many_times_reminder_sent ?? 0,
            'r6' => $events_how_many_times[0]->reminder_sent_count ?? 0,
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Lapse',
            'r1' => $current[0]->lapsed_count ?? 0,
            'r2' => 'Abandoned',
            'r3' => $historical[0]['abandoned_count'] ?? 0,
            'r4' => 'Admin Notified',
            'r5' =>	$how_many_times_admin_notified ?? 0,
            'r6' => $events_how_many_times[0]->admin_notified_count ?? 0,
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Declined',
            'r1' => $current[0]->declined_count ?? 0,
            'r2' => 'Discontinued',
            'r3' => $historical[0]['discontinued_count'] ?? 0,
            'r4' => '',
            'r5' =>	'',
            'r6' => '',
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Discontinued',
            'r1' => $current[0]->discontinued_count ?? 0,
            'r2' => 'Reassigned',
            'r3' => $ressigned ?? 0,
            'r4' => '',
            'r5' =>	'',
            'r6' => '',
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Won',
            'r1' => $current[0]->won_count ?? 0,
            'r2' => '',
            'r3' => '',
            'r4' => '',
            'r5' =>	'',
            'r6' => '',
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Lost',
            'r1' => $current[0]->lost_count ?? 0,
            'r2' => '',
            'r3' => '',
            'r4' => '',
            'r5' =>	'',
            'r6' => '',
        );
        $data[] = $row;

        $row = array(
            'r0' => 'Lost-Inconclusive',
            'r1' => $current[0]->inconclusive_count ?? 0,
            'r2' => '',
            'r3' => '',
            'r4' => '',
            'r5' =>	'',
            'r6' => '',
        );
        $data[] = $row;

        return $data;
    }
}
