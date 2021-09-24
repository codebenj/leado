<?php

namespace App\Http\Controllers\Api\V1;

use Spatie\Activitylog\Models\Activity;
use App\Notification;
use App\LeadEscalation;
use App\Organisation;
use App\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Lead;

class ActivityController extends Controller
{
    public function getEmailData($activity_id){
        $notification = new Notification();
        $data = [];

        $notification_messages = $notification->parseMessages();
        $activity = Activity::find($activity_id);

        $lead_escalation = LeadEscalation::find($activity->subject_id);

        $lead_escalation_status = "{$activity->properties['metadata']['lead_escalation_status']}";
        $lead_id = ($lead_escalation->lead->lead_id) ? $lead_escalation->lead->lead_id : $lead_escalation->lead_id;

        $role = $activity->properties['metadata']['to'];

        $data['title'] = $notification_messages[$lead_escalation_status][$role]['email']['title'] ?? '';
        $data['message'] = $notification_messages[$lead_escalation_status][$role]['email']['message'] ?? '';

        $message = $data['message'];
        if($lead_escalation){
            if($lead_escalation->organisation){
                $message = str_replace('[org_name]', $lead_escalation->organisation->name, $message);
            }
            if($role == 'organization'){
                $message = str_replace('[reason]', $lead_escalation->reason, $message);
                $message = str_replace('[suburb_name]', $lead_escalation->lead->customer->address->suburb, $message);
                $message = str_replace('[comments]', $lead_escalation->reason, $message);
                $message = str_replace('[date_chosen]', $lead_escalation->progress_period_date, $message);
                $message = str_replace('[date_of_installation]', $lead_escalation->installed_date, $message);
                $message = str_replace('[cec-ar]', $lead_escalation->expiration_date, $message);
                $message = str_replace('[cec-ar-es]', $lead_escalation->expiration_date, $message);
                $message = str_replace(
                    '[installation_dimensions]',
                    "Gutter edge {$lead_escalation->gutter_edge_meters}m;Valley: {$lead_escalation->valley_meters}m", $message
                );
            }
        }

        if($role == 'organization'){
            $setting = Setting::where('name', $lead_escalation_status)->first();
            if($setting) {
                $time_type = $setting->metadata['type'];
                $time_left = "{$setting->value} {$time_type}";
                $message = str_replace("[$setting->key]", $time_left, $message);
            }
        }

        if(is_array($lead_escalation->metadata)) {
            if(array_key_exists('new_organisation_assigned', $lead_escalation->metadata) &&
                array_key_exists('organisation_id', $lead_escalation->metadata['new_organisation_assigned'])) {

                $organisation = Organisation::find($lead_escalation->metadata['new_organisation_assigned']['organisation_id']);

                $message = str_replace(
                    '[new_org_name]', $organisation->name, $message
                );
            }
        }

        $logo = Setting::where('key', 'main-logo')->first();

        if($logo && $logo->value) {
            $data['custom_logo'] = $logo->value;
        }

        $message = str_replace('[lead_history_link]', asset('/'), $message);

        $data['message'] = $message;

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('org', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['org'] = [
                'lead_id' => $lead_id,
                'name' => $lead_escalation->organisation->name ?? '',
                'contact_number' => $lead_escalation->organisation->contact_number ?? '',
                'email' => $lead_escalation->organisation->user->email,
            ];
        }

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('inquirer', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['inquirer'] = [
                'name' => "{$lead_escalation->lead->customer->first_name} {$lead_escalation->lead->customer->last_name}",
                'address' => $lead_escalation->lead->customer->address->full_address,
                'email' => $lead_escalation->lead->customer->email,
            ];
        }

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('reason', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['reason'] = $lead_escalation->reason;
        }

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('comments', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['comments'] = $lead_escalation->comments;
        }

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('lead_id', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['lead_id'] = $lead_id;
        }

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('installed', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['installed'] = [
                'meters_gutter_edge' => $lead_escalation->gutter_edge_meters ?? '',
                'meters_valley' => $lead_escalation->valley_meters ?? ''
            ];
        }

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('date_installed', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['date_installed'] = $lead_escalation->installed_date ?? '';
        }

        if(isset($notification_messages[$lead_escalation_status][$role]['email']['show_fields'])
            && in_array('login_link', $notification_messages[$lead_escalation_status][$role]['email']['show_fields'])) {

            $data['login_link'] = asset('/');
        }

        return $data;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user = auth()->user();
        // get all leads
        // get activity per lead using whereJsonContains
        // get the org data using meta data
        $query = $request->all();
        $search = $query['key'] ?? '';

        $leads = Lead::
        join('customers', function($q) {
            $q->on('customers.id', '=', 'leads.customer_id');
        })
        ->join('lead_escalations', function($q) {
            $q->on('lead_escalations.lead_id', '=', 'leads.id');
        })
        ->join('activity_log', function($q) {
            $q->on('activity_log.properties->metadata->lead_id', '=', 'leads.id')
            ->where('lead_escalations.created_at', '<=', \DB::raw('activity_log.created_at'));
        })
        ->select('leads.id', 'leads.lead_id', 'customers.first_name', 'customers.email', 'customers.last_name', 'customers.contact_number', 'activity_log.created_at')
        ->where('lead_escalations.is_active', 1)
        ->whereNotIn('lead_escalations.escalation_status', ['Unassigned', 'Supply Only'])
        ->groupBy('activity_log.properties->metadata->lead_id')
        ->orderBy('activity_log.created_at', 'DESC');

        if($request->has('key') && $request->key) {
            $search = $request->key;
            $leads->where(function($q) use($search) {
                $q->whereHas('customer', function($q) use($search) {
                    $q->where(\DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', "%{$search}%");
                });

                $q->orWhere('leads.lead_id', 'LIKE' , '%' . $search . '%');

                $q->orWhere('leads.id', $search);
            });
        }

        $leads = $leads->paginate(10);

        $query = $request->all();

        // if($user->hasRole('organisation')) {
        //     foreach ($leads as $lead) {
        //     $lead['activities'] = Activity::select('activity_log.*', 'organisations.name as org_name')
        //     ->leftjoin('organisations', 'organisations.id', '=', 'activity_log.properties->organisation_id')
        //     ->whereJsonContains('activity_log.properties->lead_id', $lead->id)
        //     ->get();
        //     }

        // } else {
        // foreach ($leads as $lead) {
        //     $lead['activities'] = Activity::select('activity_log.*', 'organisations.name as org_name')
        //     ->leftjoin('organisations', 'organisations.id', '=', 'activity_log.properties->organisation_id')
        //     ->whereJsonContains('activity_log.properties->lead_id', $lead->id)
        //     ->orderBy('id', 'DESC')
        //     ->take(10)
        //     ->get();
        // }
        // }
        foreach ($leads as $lead) {
            $lead['activities'] = Activity::
            whereJsonContains('properties->metadata->lead_id', $lead->id)
            ->orderBy('created_at', 'DESC')
            ->get();
        }

        return $leads;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
