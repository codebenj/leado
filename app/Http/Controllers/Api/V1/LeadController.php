<?php

namespace App\Http\Controllers\Api\V1;

use App\Address;
use App\Customer;
use App\Lead;
use App\LeadJob;
use App\LeadEscalation;
use App\Organisation;
use App\OrganizationUser;
use App\Notification;
use App\Setting;
use App\Country;
use App\Events\TestEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Mail\InquirerNotification;
use App\Mail\EnquirerDetailsNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\LeadComment;
use App\TimeSetting;

// ASSIGN USER TO LEAD
use App\User;

use Illuminate\Support\Facades\Auth;
use Sabberworm\CSS\Settings;

use function Deployer\parse;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->getLeads($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request){
        return $this->getLeads($request);
    }

    private function getLeads(Request $request) {
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')) {
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $reqData = $request->all();

            $leads = LeadEscalation::with(['lead.customer.address', 'lead.customer.address.country', 'lead.customer', 'organisation'])
                ->filters($reqData)
                ->filterAsRole()
                ->getSortedLeads($reqData);

            # Get total leads
            $totalLeads = $leads->count();

            # Paginate leads
            $leads = $leads->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            // PAUSE DATES
            $dates = [];
            $times = [];
            $today = Carbon::today();

            $timeSettings = TimeSetting::all();
            foreach($timeSettings as $timeSetting){
                if($timeSetting->is_active){
                    if($timeSetting->type == "recurring"){
                        $start_recurring = new \DateTime( $timeSetting->start_day );
                        $stop_recurring = new \DateTime( $timeSetting->stop_day );

                        $interval_recurring = new \DateInterval('P1D');
                        $daterange_recurring = new \DatePeriod($start_recurring, $interval_recurring, $stop_recurring);

                        if(!empty($daterange_recurring)){
                            foreach($daterange_recurring as $date_recurring){
                                $dates[] =  Carbon::parse($date_recurring);
                            }
                        }
                    }
                    else{
                        $start_onetime = new \DateTime( $timeSetting->start_date );
                        $stop_onetime = new \DateTime( $timeSetting->stop_date );

                        $interval_onetime = new \DateInterval('P1D');
                        $daterange_onetime = new \DatePeriod($start_onetime, $interval_onetime, $stop_onetime);

                        if(!empty($daterange_onetime)){
                            foreach($daterange_onetime as $date_onetime){
                                $dates[] =  Carbon::parse($date_onetime);
                            }
                        }

                        $start_onetime_dt = new \DateTime( "{$timeSetting->start_date} {$timeSetting->start_time}" );
                        $stop_onetime_dt = new \DateTime( "{$timeSetting->stop_date} {$timeSetting->stop_time}" );

                        $interval_onetime_dt = new \DateInterval('P1D');
                        $daterange_onetime_dt = new \DatePeriod($start_onetime_dt, $interval_onetime_dt, $stop_onetime_dt);

                        if(!empty($daterange_onetime_dt)){
                            foreach($daterange_onetime_dt as $date_onetime_dt){
                                $times[] =  Carbon::parse($date_onetime_dt);
                            }
                        }

                        $times[] =  Carbon::parse($start_onetime_dt);
                        $times[] =  Carbon::parse($stop_onetime_dt);
                    }
                }
            }
            // if(!empty($daterange_recurring)){
            //     foreach($daterange_recurring as $date_recurring){
            //         $dates[] =  Carbon::parse($date_recurring);
            //     }
            // }

            // if(!empty($daterange_onetime)){
            //     foreach($daterange_onetime as $date_onetime){
            //         $dates[] =  Carbon::parse($date_onetime);
            //     }
            // }
            // dd($dates);
            // dd($now, $dates);
            // dd(min($dates), max($dates));
            // dd(min($dates)->diff(max($dates))->days);
            // dd(min($dates)->diffInMilliseconds(max($dates)));

            if(!empty($times)){
                $now = Carbon::now("Asia/Manila");

                // dd(min($times), $now, max($times));
                // dd(strtotime(min($times)), strtotime($now), strtotime(max($times)));
                // dd(strtotime(min($times)) <= strtotime($now), strtotime($now) < strtotime(max($times)));
                if(strtotime(min($times)) <= strtotime($now) && strtotime($now) < strtotime(max($times))){
                    $is_paused_dt = true;
                }
                else{
                    $is_paused_dt = false;
                }
            }
            else{
                $is_paused_dt = false;
            }

            // IS_PAUSED DATE ONLY
            $is_paused = in_array($today, $dates);

            // dd($is_paused_dt);

            if($is_paused || $is_paused_dt){
                $should_pause = true;
            }
            else{
                $should_pause = false;
            }

            $has_critical = false;
            if ( count( $leads ) > 0 ) {
                foreach( $leads as $_ ) {
                    if ( $_->is_critical ) {
                        $has_critical = true;
                    }
                }
            }

            if ( auth()->user()->roles[0]['name'] == 'organisation' ) {
                $_org = Organisation::where( 'user_id', auth()->user()->id )->first();
                $data = LeadEscalation::getReportOrganisationBreakDown( $request->all() )->get();

                if ( count( $data ) > 0 ) {
                    foreach( $data as $_data ) {
                        if ( $_data->organisation_id == $_org->id ) {
                            $_org_data = $_data;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'leads' => $leads,
                    'paused_dates' => $dates,
                    'is_paused' => $should_pause,
                    'total' => $totalLeads,
                    'has_critical' => $has_critical,
                    'org_data' => $_org_data ?? [],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorize_response'),
            'data' => [],
        ], 400);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LeadRequest $request)
    {
        try {
            \DB::beginTransaction();

            $data = $request->all();

            $escalation_status = $request->escalation_status;

            // if(!$this->isSpecialOpportunity($request->all()) && empty($request->organisation_id) && $request->customer_type == 'Supply & Install'){
            //     return response()->json([
            //         'success' => false,
            //         'message' => __('messages.organization_required')
            //     ], 400);
            // }

            if($request->customer_type == 'Supply Only'){
                $escalation_status = 'Supply Only';
                if($this->isSpecialOpportunity($request->all())){
                    $escalation_status = "Supply Only(SP)";
                }
                $data['escalation_status'] = $escalation_status;
            }

            if($this->isSpecialOpportunity($request->all()) && $request->escalation_level == 'Unassigned'){
                if($request->customer_type == 'Supply & Install'){
                    $escalation_status = "Special Opportunity";
                    $data['escalation_status'] = $escalation_status;
                }
            }

            if(! empty($request->organisation_id) && $request->customer_type == 'Supply & Install' && $request->escalation_level == 'Unassigned'){
                $data['escalation_level'] = 'Accept Or Decline';
                $data['escalation_status'] = 'Pending';
            }
            $lead_escalation_status = "{$request->escalation_level} - {$escalation_status}";
            if(LeadEscalation::leadEscalationStatus($lead_escalation_status) === false) {
                \DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => __('messages.escalation_format_error_response', ['format' => $lead_escalation_status])
                ], 400);
            }

            # when user pass an `organisation_id` to request, check if the id exists.
            $org = null;
            if($request->has('organisation_id') && ! empty($request->organisation_id)) {
                $org = Organisation::find($request->organisation_id);

                $notif_structure = [
                    'title' => 'New User',
                    'description' => 'User ' . $data['first_name'] . ' ' . $data['last_name'] . ' added',
                    'org_id' => $org->id
                ];

                activity()
                    ->causedBy( auth()->user() )
                    ->performedOn( $org )
                    ->withProperties( $notif_structure )->log( 'system_activity' );

                if(!$org) {
                    \DB::rollback();

                    return response()->json([
                        'success' => false,
                        'message' => __('messages.org_not_found_response')
                    ], 400);
                }
            }

            # check if org selected when escalation level and status are not unassigned
            if ( ($request->customer_type == 'Supply & Install') &&
                (empty($request->organisation_id)) &&
                ( !in_array($request->escalation_level, ['Unassigned', 'New']) ) ) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.organisation_required'),
                ], 400);
            }

            // Duplicating
            # check if email has value and is enquirer message checkbox assigned
            // if ( $request->customer_type == 'Supply & Install' && $request->notify_enquirer && isset( $request->email ) && ! empty( $request->email ) ) {
            // if ( isset( $request->email ) && ! empty( $request->email ) ) {
            //     # Send email to organisation if true
            //     if ($request->enquirer_send_email) {
            //         \Log::error('Email Sent')
            //         Mail::to( $request->email )->queue( new InquirerNotification( 'Leaf Stopper', $request->enquirer_message ) );
            //     }
            // }

            // Duplicating
            // if(isset( $request->contact_number ) && ! empty( $request->contact_number ) ) {
            //     # Send sms to inquirer if true
            //     if ($request->enquirer_send_sms) {
            //         $sms_sent_to = $request->contact_number;
            //         \Log::error('SMS Sent');
            //         // $notification = new Notification;
            //         Notification::send_sms($sms_sent_to, $request->enquirer_message);
            //     }
            // }

            // add search country here
            $country = Country::where('name', $request->country)->first();

            $address = Address::create([
                'address' => $request->address ?? '',
                'city' => $request->city ?? '',
                'suburb' => $request->suburb ?? '',
                'state' => $request->state ?? '',
                'postcode' => $request->postcode ?? '',
                'country_id' => $country->id,
            ]);

            $customer = Customer::create([
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name ?? '',
                'contact_number' => $request->contact_number,
                'address_id' => $address->id,
            ]);

            $data['customer_id'] = $customer->id;
            $data['metadata'] = [
                'landline_number' => $request->landline_number ?? '',
                'additional_information' => $request->additional_information ?? '',
                'address_search' => $request->address_search ?? '',
                'house_type_other' => $request->house_type_other ?? '',
                'roof_preference_other' => $request->roof_preference_other ?? '',
                'use_for_other' => $request->use_for_other ?? '',
                'inquiry_type' => $request->inquiry_type ?? '',
                'attachments' => $request->attachments ?? '',
            ];

            $lead = Lead::create($data);

            $leadEscalation = new LeadEscalation();

            $lead_esc_data = [
                'escalation_level' => $data['escalation_level'],
                'escalation_status' => $data['escalation_status'],
                'color' => $leadEscalation->leadEscalationStatus("{$data['escalation_level']} - {$data['escalation_status']}"),
                'lead_id' => $lead->id,
                'metadata' => [
                    'notif_config' => [
                        'enquirer_send_email' => $request->enquirer_send_email,
                        'enquirer_send_sms' => $request->enquirer_send_sms,
                        'send_email' => $request->send_email,
                        'send_sms' => $request->send_sms,
                    ]
                ],
                'is_active' => true,
            ];

            if ($org) {
                $lead_esc_data['organisation_id'] = $org->id;
            }

            $lead_escalation = LeadEscalation::create($lead_esc_data);

            if ( $countdown = $this->hasCountdownTimer($lead_escalation_status) ) {
                $time_type = ucfirst($countdown->metadata['type']);
                $time_key = "add{$time_type}";
                $lead_escalation->expiration_date = now()->{$time_key}($countdown->value);
                $lead_escalation->save();
                $confirmation_message = LeadEscalation::getConfirmationMessage($countdown->key, $countdown->value, $countdown->metadata['type']);
            } else if ($request->progress_period_date && $request->escalation_level == 'In Progress') {
                $lead_escalation->expiration_date = Carbon::parse($request->progress_period_date);
                $lead_escalation->save();
            }

            $lead_escalation->save();

            $lead->lead_escalation = $lead_escalation;
            $lead->customer = $customer;

            \DB::commit();

            return response()->json([
                'success' => true,
                'data' => $lead,
                'message' => __('messages.lead_success_response')
            ]);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \DB::rollback();

            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response')
            ], 400);
        }
    }

    public function inquirerDetails(Request $request) {
        # Send email to organisation
        Mail::to( $request->email )->queue( new InquirerNotification( 'Leaf Stopper', $request->enquirer_message ) );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = auth()->user();

        $new_f = Lead::where( 'lead_id', $id )->first();

        if ( $new_f ) {
            $new_f_id = $new_f->id;
            $new_f_lead_eslcation = LeadEscalation::where(['lead_id' => $new_f_id, 'is_active' => 1])->first();
            $id = $new_f_lead_eslcation->id;
        }

        $old_f = LeadEscalation::where( 'lead_id', $id )->orderBy( 'id', 'DESC' )->first();

        if ( $old_f ) {
            $id = $old_f->id;
        }

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('user')) {

            $lead = LeadEscalation::with('lead.customer.address', 'lead.customer.address.country', 'lead.customer', 'organisation')
                ->where('id', $id)
                ->first();

            return response()->json([
                'success' => true,
                'data' => $lead
            ]);
        } else {
            $organisation_user = OrganizationUser::where('user_id', $user->id)->first();
            $lead = LeadEscalation::with('lead.customer.address', 'lead.customer', 'organisation')
                ->where('id', $id)
                ->whereIn('organisation_id', $organisation_user)
                ->first();

            if(!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorize_response')
                ], 400);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $lead
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LeadRequest $request, $id)
    {
        try {
            \DB::beginTransaction();

            $escalation_status = $request->escalation_status;

            if($request->customer_type == 'Supply Only'){
                $escalation_status = 'Supply Only';
                if($this->isSpecialOpportunity($request->all())){
                    $escalation_status = "Supply Only(SP)";
                }
            }

            if($this->isSpecialOpportunity($request->all()) && $request->escalation_level == 'Unassigned'){
                if($request->customer_type == 'Supply & Install'){
                    $escalation_status = "Special Opportunity";
                }
            }

            $request->merge(['escalation_status' => $escalation_status]);

            if($request->escalation_level == 'Unassigned' && !isset($request->organisation_id)){
                $request->merge([
                    'organisation_id' => null,
                ]);
            }

            if(! empty($request->organisation_id) && $request->escalation_level == 'Unassigned'){
                $request->merge([
                    'escalation_level' => 'Accept Or Decline',
                    'escalation_status' => 'Pending',
                    'new_organisation_id' => $request->organisation_id,
                ]);
            }

            $lead_escalation_status = "{$request->escalation_level} - {$request->escalation_status}";

            if(LeadEscalation::leadEscalationStatus($lead_escalation_status) === false) {
                \DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => __('messages.escalation_format_error_response', ['format' => $lead_escalation_status])
                ], 400);
            }

            // if(!$this->isSpecialOpportunity($request->all()) && empty($request->organisation_id) && $request->customer_type == 'Supply & Install'){
            //     return response()->json([
            //         'success' => false,
            //         'message' => __('messages.organization_required')
            //     ], 400);
            // }

            # check if org selected when escalation level and status are not unassigned
            if ( ($request->customer_type == 'Supply & Install') &&
                (empty($request->organisation_id)) &&
                ( !in_array($request->escalation_level, ['Unassigned', 'New']) ) ) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.organisation_required'),
                ], 400);
            }

            // Get Lead
            $lead = Lead::find($id);

            if($lead) {
                // Update lead
                $request['metadata'] = [
                    'additional_information' => $request->additional_information,
                    'address_search' => $request->address_search,
                    'house_type_other' => $request->house_type_other,
                    'roof_preference_other' => $request->roof_preference_other,
                    'use_for_other' => $request->use_for_other,
                    'inquiry_type' => $request->inquiry_type,
                    'attachments' => $request->attachments,
                ];

                //dont include unique columns
                $lead->update($request->except('id'));

                // Update lead escalation
                $data = $request->all();
                $data['reason'] = 'Manually update by admin.';
                $lead_esc = $this->updateLeadEscalation(
                    $lead->id,
                    $request->escalation_level,
                    $request->escalation_status,
                    $request->update_type,
                    $data // Additional params,
                );

                # fallback for editing org on unassigned leads save the organisation id
                $lead_esc->organisation_id = $request->has('organisation_id') ? $request->organisation_id : $lead_esc->organisation_id;

                $lead_esc->save();

                // expiration
                if ( $countdown = $this->hasCountdownTimer($lead_escalation_status) ) {
                    $time_type = ucfirst($countdown->metadata['type']);
                    $time_key = "add{$time_type}";
                    $lead_esc->expiration_date = now()->{$time_key}($countdown->value);
                    $lead_esc->save();
                    $confirmation_message = LeadEscalation::getConfirmationMessage($countdown->key, $countdown->value, $countdown->metadata['type']);
                } else if ($request->progress_period_date && $request->escalation_level == 'In Progress') {
                    $lead_esc->expiration_date = Carbon::parse($request->progress_period_date);
                    $lead_esc->save();
                    $confirmation_message = __('messages.in_progress_extended');
                } else if(!$request->progress_period_date && strpos($request->escalation_status, 'Extended') !== false) {
                    $lead_esc->expiration_date = now()->addDays(7);
                    $confirmation_message = __('messages.in_progress_extended');
                }


                # Suspend org when fall to suspendable escalation status
                if(LeadEscalation::isSuspendableEscalationStatus($lead_escalation_status)) {
                    $organization = $lead_esc->organisation;
                    $organization->is_suspended = true;
                    $organization->save();
                }

                $country = Country::where('name', $request->country)->first();
                $address = $lead->customer->address;

                $address->update([
                    'address' => $request->address ?? '',
                    'city' => $request->city ?? '',
                    'suburb' => $request->suburb ?? '',
                    'state' => $request->state ?? '',
                    'postcode' => $request->postcode ?? '',
                    'country_id' => $country->id,
                ]);

                $customer = $lead->customer;
                $customer->update([
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'contact_number' => $request->contact_number,
                    'address_id' => $address->id,
                ]);

                $lead['lead_escalation'] = $lead_esc;

                \DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => $lead,
                    'message' => __('messages.lead_update_response'),
                ]);
            } else {
                \DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => __('messages.general_error_response'),
                ], 400);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_update_response'),
            ]);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());

            \Log::error($e->getTraceAsString());

            \DB::rollback();

            return response()->json([
                'success' => false,
                'message' => __('messages.invalid_escalation_level'),
            ], 400);
        }
    }

    /**
     * lead history
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function leadHistory(Request $request, $id) {

        $lead = Lead::with('customer', 'customer.address')->find($id);
        $user = auth()->user();
        $pageNo = $request->pageNo ?? 1;
        $pageSize = $request->pageSize ?? 20;

        if(!$lead) {
            return response()->json([
                'success' => false,
                'message' => __('messages.lead_not_found_response')
            ], 400);
        }

        if(auth()->user()->hasRole('organisation')) {

            $has_access = LeadEscalation::where('lead_id', $lead->id)
                ->where('organisation_id', $user->organisation_user->organisation_id)->exists();

            if(!$has_access) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized')
                ], 400);
            }

            $lead->lead_escalations = LeadEscalation::with('organisation')
                //dont include this escalation status in organisation
                ->whereNotIn('escalation_status', ['Unassigned', 'Supply Only', 'Supply Only(SP)', 'General Enquiry', 'General Enquiry(SP)'])
                ->where('lead_id', $lead->id)
                ->where('organisation_id', $user->organisation_user->organisation_id)
                ->orderBy('id', 'DESC');

            # Get latest Extended 1 escalation
            $latest_ip_extended_1_esc = LeadEscalation::select('id')
                ->where('lead_id', $lead->id)
                ->orderBy('id', 'DESC')->first();

            $ip_first_extension_created_date = LeadEscalation::select('created_at')
                ->where('lead_id', $lead->id)
                ->where('id', '>=', $latest_ip_extended_1_esc->id)
                ->where('escalation_status', 'LIKE', '%Extended%')
                ->orderBy('id')->first();

            $lead->lead_ip_first_extension_created_date = null;
            if($ip_first_extension_created_date) {
                $lead->lead_ip_first_extension_created_date = $ip_first_extension_created_date->created_at;
            }

            $total = $lead->lead_escalations->count();

            $lead->lead_escalations = $lead->lead_escalations
                ->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            $lead_job = LeadJob::select('sale')
                ->where('lead_id', $lead->id)
                ->where('organisation_id', $user->organisation_user->organisation_id)->first() ?? '';

            if($lead_job) {
                $lead->sale = $lead_job->sale;
            }

            //not always as active status.. maybe the last status from organisation
            $active_escalation = LeadEscalation::with(['lead', 'lead.customer', 'lead.customer.address', 'organisation', 'organisation.address','organisation.organisation_users.user'])
                ->where('lead_id', $lead->id)
                ->where('organisation_id', $user->organisation_user->organisation_id)
                ->orderBy('id', 'DESC')->first();

        } else {
            $lead->lead_escalations = LeadEscalation::with(['organisation', 'lead'])
                ->where('lead_id', $lead->id)
                ->orderBy('id', 'DESC');

            $total = $lead->lead_escalations->count();

            $lead->lead_escalations = $lead->lead_escalations
                ->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            $active_escalation = LeadEscalation::with(['lead', 'lead.customer', 'lead.customer.address', 'organisation', 'organisation.address','organisation.organisation_users.user'])
                ->where('lead_id', $lead->id)
                ->where('is_active', 1)->first();
        }

        // $lead->notifications = Notification::whereJsonContains('metadata->lead_id', $lead->id)
        //     ->whereJsonContains('metadata->to', 'inquirer')
        //     ->whereJsonContains('metadata->notification_type', 'notification')
        //     ->orderBy('id', 'DESC')
        //     ->get();

        $lead->notifications = Notification::whereJsonContains('metadata->lead_id', $lead->id)
            ->whereJsonContains('metadata->to', 'inquirer')
            ->orderBy('id', 'DESC')
            ->groupBy('created_at') // FOR SENT BY ICON - DON'T DELETE
            ->get();

        $lead->org_notifications = Notification::whereJsonContains('metadata->lead_id', $lead->id)
            ->whereJsonContains('metadata->to', 'organisation')
            ->orderBy('id', 'DESC')
            ->groupBy('created_at') // FOR SENT BY ICON - DON'T DELETE
            ->get();


            return response()->json([
                'success' => true,
                'active_escalation' => $active_escalation,
                'data' => $lead,
                'total' => $total
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(auth()->user()->hasRole('super admin') || auth()->user()->hasRole('administrator')) {
            $lead = Lead::find($id);

            if($lead) {
                $lead->lead_escalations()->delete();
                $lead->delete();

                return response()->json([
                    'success' => true,
                    'message' => __('messages.lead_delete_response'),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.lead_not_found_response'),
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorize_response'),
            ], 401);
        }
    }

    /**
     * Update Lead Escalation Status
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateLeadEscalationStatus(Request $request) {
        $request->validate([
            'escalation_level' => 'required|string',
            'escalation_status' => 'required|string',
            'lead_id' => 'required|integer',
        ]);

        $lead_escalation_status = "{$request->escalation_level} - {$request->escalation_status}";

        if(LeadEscalation::leadEscalationStatus($lead_escalation_status) === false && strpos($lead_escalation_status, 'In Progress - Extended') === false){
            return response()->json([
                'success' => false,
                'message' => __('messages.escalation_format_error_response', ['format' => $lead_escalation_status])
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $user = auth()->user();

            if($user->hasRole('organisation')) {
                $has_lead_esc_perm = LeadEscalation::where([
                    'lead_id' => $request->lead_id,
                    'organisation_id' => $user->organisation_user->organisation_id,
                    'is_active' => true,
                ])->exists();

                if(!$has_lead_esc_perm) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.unauthorize_response'),
                    ], 400);
                }
            }

            $lead_esc = $this->updateLeadEscalation(
                $request->lead_id,
                $request->escalation_level,
                $request->escalation_status,
                $request->update_type,
                $request->all() // Additional params
            );

            $confirmation_message = false;

            $modified_lead_escalation_status = isset($request->modified_escalation_status)
                ? "{$request->escalation_level} - {$request->modified_escalation_status}"
                : $lead_escalation_status;

            if ($request->has('progress_period_date') && $request->progress_period_date && $request->escalation_level == 'In Progress') {
                # if selected time is empty, set time to 9AM Sydney time as default
                $time = $request->has('selected_time') && !empty($request->selected_time) ? ($request->selected_time . ':00') : '23:00:00';
                # Progress period datetime
                $ppd_datetime = $request->progress_period_date . ' ' . $time;
                # Set time with selected time value
                $parsed_ppd_datetime = Carbon::parse($ppd_datetime, Address::find($user->address_id)->timezone);
                # Convert admin/org timezone to UTC
                $utc_conv_ppd_datetime = $parsed_ppd_datetime->timezone('UTC');

                $lead_esc->expiration_date = $utc_conv_ppd_datetime;
                $lead_esc->progress_period_date = $utc_conv_ppd_datetime->toISOString();
                $lead_esc->save();
                $confirmation_message = __('messages.in_progress_extended');

            } else if ( $countdown = $this->hasCountdownTimer($modified_lead_escalation_status) ) {
                if($request->has('tried_to_contact_date') && ! empty($request->tried_to_contact_date)) {
                    # if selected time is empty, set time to 9AM Sydney time as default
                    $time = $request->has('selected_time') && !empty($request->selected_time) ? ($request->selected_time . ':00') : '23:00:00';
                    $parsed_ttcd_datetime = Carbon::parse($request->tried_to_contact_date, Address::find($user->address_id)->timezone);
                    # Set time with selected time value
                    $parsed_ttcd_datetime = $parsed_ttcd_datetime->setTimeFromTimeString($time);
                    # Convert admin/org timezone to UTC
                    $utc_conv_ttcd_datetime = $parsed_ttcd_datetime->timezone('UTC');

                    $lead_esc->expiration_date = $utc_conv_ttcd_datetime;
                    $lead_esc->progress_period_date = $utc_conv_ttcd_datetime->toISOString();
                    $lead_esc->save();

                    $days = now()->startOfDay()->diffInDays($utc_conv_ttcd_datetime);
                    $days_display = ($days > 1 ? 'days' : 'day');
                    $confirmation_message = LeadEscalation::getConfirmationMessage('cec_awaiting_response', $days, $days_display);
                } else {
                    $time_type = ucfirst($countdown->metadata['type']);
                    $time_key = "add{$time_type}";
                    $lead_esc->expiration_date = now()->{$time_key}($countdown->value);
                    $lead_esc->save();
                    $confirmation_message = LeadEscalation::getConfirmationMessage($countdown->key, $countdown->value, $countdown->metadata['type']);
                }
            }

            # Suspend org when fall to suspendable escalation status
            if(LeadEscalation::isSuspendableEscalationStatus($lead_escalation_status)) {
                $organization = $lead_esc->organisation;
                $organization->is_suspended = true;
                $organization->save();
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'data' => $lead_esc,
                'message' => $confirmation_message ? $confirmation_message : __('messages.lead_update_response')
            ]);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \DB::rollback();

            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response')
            ], 400);
        }

        return response()->json([
            'success' => true, 'message' => __('messages.lead_status_update_response')
        ]);
    }

    /**
     * Lead Escalation Status
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function leadEscalationResponseStructure(Request $request) {
        $data = file_get_contents(storage_path('org_lead_responses.json'));
        $data = json_decode($data, true);

        $cached_data = Cache::get('org_lead_responses');
        if(!$cached_data) {
            $serialized = \serialize($data);
            Cache::put('org_lead_responses', $serialized);
        } else {
            $serialized = Cache::get('org_lead_responses');
            $data = unserialize($serialized);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Check lead if its Special Opportunity
     * @param $request
     */

    private function isSpecialOpportunity($request){
        $special_opportunity = array('Commercial', 'Townhouses/Villas', 'I am a builder', 'I am a tradesperson');

        if(in_array($request['house_type'], $special_opportunity) || in_array($request['use_for'], $special_opportunity)){
            return true;
        }
        return false;
    }

    /**
     * Update Lead Escalation Status
     *
     * @param  $lead_id
     * @param  $escalation_level
     * @param  $escalation_status
     *
     * @return array
     */
    private function updateLeadEscalation($lead_id, $escalation_level, $escalation_status, $update_type , array $args = []) {

        $active_lead_esc = LeadEscalation::where('lead_id', $lead_id)->where('is_active', true)->first();
        # init
        $new_lead_esc = null;

        # New assigned status
        $new_lead_esc_status = "{$escalation_level} - {$escalation_status}";

        # Active status
        $active_lead_esc_status = "{$active_lead_esc->escalation_level} - {$active_lead_esc->escalation_status}";

        # Check if is exempted on the same escalation level and status
        $isExempted = LeadEscalation::getExemptedEscalationLevelAndStatus($new_lead_esc_status)
            && !(isset($args['reason']) && $args['reason'] == 'Manually update by admin.');

        # Proceed when new and active status is not equal
        if(($active_lead_esc_status != $new_lead_esc_status) || $isExempted) {
            LeadEscalation::where('lead_id', $lead_id)->update(['is_active' => false]);
            $lead = Lead::with('customer.address')->find($lead_id);

            $new_lead_esc                       = new LeadEscalation();
            $new_lead_esc->is_active            = true;
            $new_lead_esc->organisation_id      = $args['organisation_id'] ?? '';
            $new_lead_esc->reason               = $args['reason'] ?? ($args['response'] ?? '');
            // Removed Causing comments on history on update leads
            if ($update_type === 'modal_update') {
                $new_lead_esc->comments = $args['comments'] ?? '';
            }
            $new_lead_esc->gutter_edge_meters   = $args['gutter_edge_meters'] ?? '';
            $new_lead_esc->valley_meters        = $args['valley_meters'] ?? '';
            $new_lead_esc->progress_period_date = $args['progress_period_date'] ?? '';
            $new_lead_esc->installed_date       = $args['installation_date'] ?? '';
            $new_lead_esc->escalation_level     = $escalation_level;
            $new_lead_esc->escalation_status    = $escalation_status;

            $new_lead_esc->color                = LeadEscalation::leadEscalationStatus($new_lead_esc_status); // Set Color
            $new_lead_esc->lead()->associate($lead);

            // $new_lead_esc->metadata   = [
            //     'response' => $args['response'] ?? '',
            //     'other_reason' => $args['other_reason'] ?? '',
            //     'indicate_reason' => $args['indicate_reason'] ?? '',
            //     'what_system' => $args['what_system'] ?? '',
            // ];

            $metadata = [
                'response' => $args['response'] ?? '',
                'other_reason' => $args['other_reason'] ?? '',
                'indicate_reason' => $args['indicate_reason'] ?? '',
                'what_system' => $args['what_system'] ?? '',
            ];

            if( strtolower($escalation_status) == 'parked' ){
                $metadata['expiration_date'] = $active_lead_esc->expiration_date;
            }

            $new_lead_esc->metadata = $metadata;

            if(array_key_exists('new_organisation_id', $args) && !empty($args['new_organisation_id'])) {
                $new_lead_esc->organisation_id = $args['new_organisation_id'];
            } else {
                $new_lead_esc->organisation_id = $active_lead_esc->organisation_id;
            }

            # Save
            $new_lead_esc->save();
        }
            # Save Lead Comments
            // Removed block of code - Making Comments when Saving Leads

        return isset($new_lead_esc) ? $new_lead_esc : $active_lead_esc;
    }

    /**
     * Reassign Lead
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function reassignLead(Request $request, $lead_id) {
        $request->validate([
            //'old_organization_id' => 'required|integer|exists:organisations,id',
            'new_organization_id' => 'required|integer|exists:organisations,id',
            'escalation_level' => 'required|string',
            'escalation_status' => 'required|string'
        ]);

        try {
            \DB::beginTransaction();

            # Get Lead
            $lead = Lead::find($lead_id);

            if(!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.lead_not_found_response')
                ], 400);
            }

            if($request->old_organization_id == $request->new_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.organisations_the_same_id')
                ], 400);
            }

            $lead_escalation_status = "{$request->escalation_level} - {$request->escalation_status}";
            if(LeadEscalation::leadEscalationStatus($lead_escalation_status) === false) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.escalation_format_error_response', ['format' => $lead_escalation_status])
                ], 400);
            }

            # Status should be `'Discontinued - Discontinued'` or `'Abandoned - Abandoned'` or `'Declined - Declined' or `'Accept Or Decline - Declined-Lapsed'` only.
            if(!in_array($lead_escalation_status, ['Discontinued - Discontinued', 'Abandoned - Abandoned', 'Declined - Declined', 'Accept Or Decline - Declined-Lapsed'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.escalation_reassign_status_error_response')
                ], 400);
            }

            # Set inactive all escalations
            $active_lead_escalation = LeadEscalation::where('lead_id', $lead->id)
                ->update(['is_active' => false]);

            # Set Abandoned/Discontinued escalation from the old organisation
            if($request->old_organization_id != 0){
                $last_inactive_lead_esc                       = new LeadEscalation();
                $last_inactive_lead_esc->is_active            = false;
                $last_inactive_lead_esc->reason               = $request->reason ?? '';
                $last_inactive_lead_esc->comments             = $request->comments ?? '';
                $last_inactive_lead_esc->escalation_level     = $request->escalation_level;
                $last_inactive_lead_esc->escalation_status    = $request->escalation_status;
                $last_inactive_lead_esc->color                = LeadEscalation::leadEscalationStatus($lead_escalation_status); // Set Color
                $last_inactive_lead_esc->lead_id              = $lead->id;
                $last_inactive_lead_esc->organisation_id      = $request->old_organization_id;

                # Metadata key `new_organisation_assigned` will be used to email notification
                $last_inactive_lead_esc->metadata             = [
                    'new_organisation_assigned' => ['organisation_id' => $request->new_organization_id],
                    'send_sms' => $request->send_sms ?? false,
                    'send_email' => $request->send_email ?? false,
                    'reassign' => true,
                ];
                $last_inactive_lead_esc->save();
            }

            # Assign lead to new organization
            $new_escalation_level = 'Accept Or Decline';
            $new_escalation_status = 'Pending';

            # Manually validate the new escalation status
            $new_lead_escalation_status = "{$new_escalation_level} - {$new_escalation_status}";
            if(LeadEscalation::leadEscalationStatus($new_lead_escalation_status) === false) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.escalation_format_error_response', ['format' => $new_lead_escalation_status])
                ], 400);
            }

            # Add new escalation status
            $new_lead_esc                       = new LeadEscalation();
            $new_lead_esc->is_active            = true;
            $new_lead_esc->escalation_level     = $new_escalation_level;
            $new_lead_esc->escalation_status    = $new_escalation_status;
            $new_lead_esc->color                = LeadEscalation::leadEscalationStatus($new_lead_escalation_status); // Set Color
            $new_lead_esc->lead_id              = $lead->id;
            $new_lead_esc->organisation_id      = $request->new_organization_id;

            # Metadata key `prevent_inquirer_notification`
            $new_lead_esc->metadata   = [
                'prevent_inquirer_notification' => true,
                'send_sms' => $request->send_sms ?? false,
                'send_email' => $request->send_email ?? false,
                'response' => $request->response ?? '',
                'other_reason' => $request->other_reason ?? '',
                'indicate_reason' => $request->indicate_reason ?? '',
                'what_system' => $request->what_system ?? '',
                'reassign' => true,
            ];
            $new_lead_esc->save();

            $confirmation_message = false;

            if ( $countdown = $this->hasCountdownTimer($lead_escalation_status) ) {
                $time_type = ucfirst($countdown->metadata['type']);
                $time_key = "add{$time_type}";
                $new_lead_esc->expiration_date = now()->{$time_key}($countdown->value);
                $new_lead_esc->save();
                $confirmation_message = LeadEscalation::getConfirmationMessage($countdown->key, $countdown->value, $countdown->metadata['type']);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'data' => $new_lead_esc,
                'message' => $confirmation_message ? $confirmation_message : __('messages.lead_reassign_success_response'),
            ]);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \DB::rollback();

            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response')
            ], 400);
        }
    }

    public function saveSale(Request $request, $id) {
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('user') || $user->hasRole('organisation')){
            $lead = Lead::find($id);

            if(!$lead){
                return response()->json([
                    'success' => true,
                    'message' => __('messages.lead_not_found_response'),
                    'data' => []
                ]);
            }

            if($user->hasRole('organisation') && $request->has('sale')) {
                $org_user = $user->organisation_user;

                $lead_jobs = LeadJob::where('organisation_id', $org_user->organisation_id)
                    ->where('lead_id', $lead->id)->first();

                if($lead_jobs) {
                    $lead_jobs->update(['sale' => $request->sale]);
                } else {
                    LeadJob::create([
                        'lead_id' => $lead->id,
                        'organisation_id' => $org_user->organisation_id,
                        'sale' => $request->sale
                    ]);
                }
            }

            $lead->fill($request->all())->save();

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_sale_save'),
                'data' => $lead
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function sendInquirerMessage(Request $request, $id) {
        $request->validate([
            'message' => 'required|string',
            'send_sms' => 'required|boolean',
            'send_email' => 'required|boolean',
        ]);

        $lead = Lead::find($id);

        if(!$lead){
            return response()->json([
                'success' => true,
                'message' => __('messages.lead_not_found_response'),
                'data' => []
            ]);
        }

        # Get customer info
        $customer = $lead->customer;
        # Get organisation info
        $organisation = Organisation::find($lead->active_escalation[0]->organisation_id);
        //$organisation_email = User::find($lead->active_escalation[0]->organisation_id)->email;
        try{$organisation_email = User::find($organisation->user_id)->email;
        }catch(\Exception $ex){ $organisation_email = null; }

        $lead_escalation = "{$lead->active_escalation[0]->escalation_level} - {$lead->active_escalation[0]->escalation_status}";
        $organisation_id = $lead->active_escalation[0]->organisation_id ?? 0;
        $notifications = array();

        // FOR SENT BY ICON - DON'T DELETE
        if($request->send_email && $request->send_sms){
            $email_and_sms = "both";
        }
        elseif($request->send_email && !$request->send_sms){
            $email_and_sms = "email";
        }
        elseif(!$request->send_email && $request->send_sms){
            $email_and_sms = "sms";
        }
        else{
            $email_and_sms = "none";
        }

        if($request->send_email && (!empty($customer->email) || !empty($organisation_email))) {
            # Send email to inquirer
            $email_sent_to = $request->send_to === 'inquirer' ? $customer->email : $organisation_email;

            Mail::to($email_sent_to)
            ->queue(new InquirerNotification('Leaf Stopper', $request->message));

            $notification_types = ['notification', 'email'] ;

            foreach ($notification_types as $type) {
                $notif_structure = [
                    'title' => $request->send_to === 'inquirer' ? "ENQUIRER NOTIFICATIONS" : "ORGANIZATION NOTIFICATIONS",
                    'description' => $request->message,
                    'metadata' => [
                        'to' => $request->send_to,
                        'lead_id' => (int) $id,
                        'organisation_id' => $organisation_id,
                        'notification_type' => $type,
                        'lead_escalation_status' => $lead_escalation,
                        'lead_escalation_color' => $lead->active_escalation[0]->color,
                        'email_and_sms' => $email_and_sms // FOR SENT BY ICON - DON'T DELETE
                    ]
                ];

                $notification = Notification::create($notif_structure);
                $notifications[] = $notification;

                $notification->logActivity(
                    $lead->active_escalation[0],
                    $notif_structure
                );
            }
        }

        if($request->send_sms && (!empty($customer->contact_number) || !empty( $organisation->contact_number))) {
            # Send sms to inquirer
            $sms_sent_to = $request->send_to === 'inquirer' ? $customer->contact_number : $organisation->contact_number;
            $notification = new Notification;
            Notification::send_sms($sms_sent_to, $request->message);

            $notif_structure = [
                'title' => $request->send_to === 'inquirer' ? "ENQUIRER NOTIFICATIONS" : "ORGANIZATION NOTIFICATIONS",
                'description' => $request->message,
                'metadata' => [
                    'to' => $request->send_to,
                    'lead_id' => (int) $id,
                    'organisation_id' => $organisation_id,
                    'notification_type' => 'sms',
                    'lead_escalation_status' => $lead_escalation,
                    'lead_escalation_color' => $lead->active_escalation[0]->color,
                    'email_and_sms' => $email_and_sms // FOR SENT BY ICON - DON'T DELETE
                ]
            ];

            $notification = Notification::create($notif_structure);
            $notifications[] = $notification;

            $notification->logActivity(
                $lead->active_escalation[0],
                $notif_structure
            );
        }


        return response()->json([
            'success' => true,
            'message' => __('messages.enquirer_message_sent'),
            'data' => $notifications
        ]);
    }

    public function sendInquirerDetails(Request $request, $id) {
        $request->validate([
            'send_sms' => 'required|boolean',
            'send_email' => 'required|boolean',
        ]);

        $lead = Lead::find($id);

        if(!$lead){
            return response()->json([
                'success' => true,
                'message' => __('messages.lead_not_found_response'),
                'data' => []
            ]);
        }

        # Get customer info
        $customer = $lead->customer;
        # Get organisation info
        $organisation = Organisation::find($lead->active_escalation[0]->organisation_id);
        //$organisation_email = User::find($lead->active_escalation[0]->organisation_id)->email;
        try{$organisation_email = User::find($organisation->user_id)->email;
        }catch(\Exception $ex){ $organisation_email = null; }

        $lead_escalation = "{$lead->active_escalation[0]->escalation_level} - {$lead->active_escalation[0]->escalation_status}";
        $organisation_id = $lead->active_escalation[0]->organisation_id ?? 0;
        $notifications = array();

        // FOR SENT BY ICON - DON'T DELETE
        if($request->send_email && $request->send_sms){
            $email_and_sms = "both";
        }
        elseif($request->send_email && !$request->send_sms){
            $email_and_sms = "email";
        }
        elseif(!$request->send_email && $request->send_sms){
            $email_and_sms = "sms";
        }
        else{
            $email_and_sms = "none";
        }

        if(!empty( $organisation->contact_number) && $request->send_email) {
            # Send email to organisation
            $email_sent_to = $organisation_email;

            Mail::to($email_sent_to)
            ->queue(new EnquirerDetailsNotification('New Lead - Enquirer Details', $lead->active_escalation[0]));


            $notification_types = ['notification', 'email'] ;

            foreach ($notification_types as $type) {
                $notif_structure = [
                    'title' => "ORGANIZATION NOTIFICATIONS",
                    'description' => 'New Lead - Enquirer Details ',
                    'metadata' => [
                        'to' => $request->send_to,
                        'lead_id' => (int) $id,
                        'organisation_id' => $organisation_id,
                        'notification_type' => $type,
                        'lead_escalation_status' => $lead_escalation,
                        'lead_escalation_color' => $lead->active_escalation[0]->color,
                        'email_and_sms' => $email_and_sms // FOR SENT BY ICON - DON'T DELETE
                    ]
                ];

                $notification = Notification::create($notif_structure);
                $notifications[] = $notification;

                $notification->logActivity(
                    $lead->active_escalation[0],
                    $notif_structure
                );
            }
        }

        if(!empty( $organisation->contact_number) && $request->send_sms) {
            # Send email to organisation
            $sms_sent_to = $organisation->contact_number;
            $notification = new Notification;
            $active_escalation =$lead->active_escalation[0];

            // Tested SMS on different phone numbers on both local and staging - I didn't received any messages
            $custom_message = 'Enquirer Details' . "\r\n" .'Enquirer Name: ' . $active_escalation->lead->customer->first_name . ' ' . $active_escalation->lead->customer->last_name . ',' . "\r\n" . 'Enquirer Address: ' . $active_escalation->lead->customer->address->full_address . ',' . "\r\n" . 'Enquirer Email: ' . $active_escalation->lead->customer->email . ',' . "\r\n" . 'Enquirer Contact Number: ' . $active_escalation->lead->customer->contact_number;

            Notification::send_sms($sms_sent_to, $custom_message);

            $notif_structure = [
                'title' => "ORGANIZATION NOTIFICATIONS",
                'description' => 'New Lead - Enquirer Details',
                'metadata' => [
                    'to' => $organisation->contact_number,
                    'lead_id' => (int) $id,
                    'organisation_id' => $organisation_id,
                    'notification_type' => 'sms',
                    'lead_escalation_status' => $lead_escalation,
                    'lead_escalation_color' => $lead->active_escalation[0]->color,
                    'email_and_sms' => $email_and_sms // FOR SENT BY ICON - DON'T DELETE
                ]
            ];

            $notification = Notification::create($notif_structure);
            $notifications[] = $notification;

            //save all notification types
            $notification->logActivity(
            $lead->active_escalation[0],
            $notif_structure
        );
        }




        return response()->json([
            'success' => true,
            'message' => __('messages.enquirer_message_sent'),
            'data' => $notifications
        ]);
    }

    public function hasCountdownTimer($lead_escalation_status) {

        $settings = Setting::where('name', $lead_escalation_status)->first();
        $timeMeasurements = ['minutes', 'hours', 'days', 'months', 'years'] ;

        if ( isset($settings)
            && !empty($settings->value)
            && isset($settings->metadata['type'])
            && in_array($settings->metadata['type'], $timeMeasurements)) {

            return $settings;
        }
    }

    public function comments($leadId) {
        $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
        $pageSize = isset($request->pageSize) ? $request->pageSize : 20;
        $comments = LeadComment::with('user')->where('lead_id', $leadId)->orderBy('created_at', 'desc');

        $total = $comments->count();
        $data = $comments
                ->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $data,
                'total' => 0
            ]
        ]);
    }

    public function saveLeadComment(Request $request, $leadId) {
        $user = auth()->user();

        $comment = LeadComment::where('id', $request->lead_comment_id)->first();
        $data = [
            'user_id' => $user->id,
            'lead_id' => $leadId,
            'comment' => $request->comment,
        ];

        if ( isset($comment) ) {
            $comment->update($data);
        } else {
            $comment = LeadComment::create($data);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Lead comment was added successfully.',
            'data' => LeadComment::with('user')->where('id', $comment->id)->first()
        ]);
    }

    public function deleteLeadComment($id){
        $deleteOrgComments = LeadComment::find($id);
        $deleteOrgComments->delete();

        return response()->json([
            'success' => true,
            'msg' =>  'Lead comment was deleted successfully.',
            'data' => []
        ]);
    }

    // ASSIGN USER TO LEAD
    public function fetchUsers() {
        $current_user = auth()->user();

        // $users = User::where("role_id", "!=", 3)->get();
        $users = User::whereIn('role_id', ["1", "2", "4"])->with( 'user_role' )->get();

        return response()->json([
            'success' => true,
            'msg' => 'Success',
            'data' => $users
        ]);
    }

    public function fetchAssignedUsers($id) {
        $lead = Lead::find($id);
        $ids = $lead->user_ids ?? [];
        $users = User::whereIn('id', $ids)->get();

        return response()->json([
            'success' => true,
            'msg' => 'Success',
            'data' => $users
        ]);
    }

    public function assignUserToLead(Request $request, $id) {
        $assignUserToLead = Lead::firstOrCreate(['id' => $id]);
        $assignUserToLead->user_ids = $request->allUsers;
        $assignUserToLead->save();

        return response()->json([
            'success' => true,
            'msg' => 'User was assigned successfully.',
            'data' => User::whereIn('id', $request->allUsers)->get()
        ]);
    }

    public function staffComment($leadId) {
        $staffComment = Lead::where('id', $leadId)->get();

        return response()->json([
            'success' => true,
            'data' => $staffComment
        ]);
    }

    public function updateLeadEscalationMeters(Request $request) {

        $request->validate([
            'gutter_edge_meters' => 'required',
            'valley_meters' => 'required',
            'installation_date' => 'required',
        ], [
            'gutter_edge_meters.required' => 'Please enter a number or "0"',
            'valley_meters.required' => 'Please enter a number or "0"',
            'installation_date' => 'Please pick an installation date.'
        ]);

        $leadEscalation = LeadEscalation::with(['organisation', 'lead'])
            ->where('id', $request->id)
            ->first();

        if ( isset($leadEscalation) ) {
            $leadEscalation->update([
                'gutter_edge_meters' => $request->gutter_edge_meters,
                'valley_meters' => $request->valley_meters,
                'installed_date' => $request->installation_date,
            ]);

            return response()->json([
                'success' => true,
                'message' =>  'Actual Meters WON was updated successfully.',
                'data' => $leadEscalation,
            ]);
        }


        return response()->json([
            'success' => false,
            'message' => __('messages.lead_escalcation_not_found'),
        ], 400);
    }

    public function orgsIconAction( Request $request ) {
        $type = $request->type;
        $number = $request->contact_number;
        $message = $request->message;
        $email = $request->email;

        if ( $type == 'sms' ) {
            Notification::send_sms( $number, $message );
        } else {
            Mail::to( $email )->queue( new InquirerNotification( 'Leaf Stopper', $message ) );
        }

        return response()->json( true );
    }

    public function leadNameValidate(Request $request ) {
        try {
            $first_name = $request->first_name;
            $last_name = $request->last_name;


            $leads = Lead::join('customers', 'leads.customer_id', '=', 'customers.id')->where('first_name', $first_name)->where('last_name', $last_name)->get();

            if ($leads) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'is_existing' => $leads->count() > 0,
                    ]
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response')
            ], 400);
        }

    }
}
