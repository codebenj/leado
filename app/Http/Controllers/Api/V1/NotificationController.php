<?php

namespace App\Http\Controllers\Api\V1;

use App\CustomNotification as AppCustomNotification;
use App\Events\CustomNotificationEvent;
use App\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SMSGlobal\Credentials as SMSGlobalCredentials;
use SMSGlobal\Resource\Sms as SMSGlobalSms;
use App\Lead;
use App\Events\NotificationCreateEvent;
use App\Mail\CustomNotification;
use Illuminate\Support\Facades\Mail;
use App\Organisation;
use App\Role;
use App\User;
use App\LeadEscalation;

class NotificationController extends Controller
{
    public function getNotification(Request $request){
        $user = auth()->user();

        $notifications = Notification::getNotifications($request->all())->FilterAsRole();

        $total = $notifications->count();

        $notifications = $notifications->paginate(100);

        return $notifications;
        return response()->json([
            'success' => true,
            'data' => $notifications,
            'message' => __('messages.notification_found'),
            'total' => $total
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // $leads = Lead::getLeads($request)
        //     ->orderBy('id', 'ASC')
        //     ->paginate(10);

        // $leads = Lead::with(['customer'])->orderBy('id', 'ASC')->paginate(10);
        $user_org = $user->organisation_user;
        $query = $request->all();
        $search = $query['key'] ?? '';

        $leads = Lead::filterWithOrg($user_org)
        ->with(['customer'])
        ->whereHas('lead_escalations', function($q) {
             $q->select(\DB::raw('COUNT(*) as lead_count'));
             $q->having('lead_count', '>', 0);
        })->whereHas('lead_escalations', function($q) {
             $q->whereNotIn('escalation_status', ['Unassigned', 'Supply Only']);
        })->orderBy('id', 'ASC');

        if($request->has('key') && $request->key) {
            $search = $request->key;
            $leads->where(function($q) use($search) {
                $q->whereHas('customer', function($q) use($search) {
                    $q->where(\DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', "%{$search}%");
                });

                $q->orWhere('lead_id', 'LIKE', '%' . $search . '%');

                $q->orWhere('id', $search);
            });
        }

        $leads = $leads->paginate(10);


        $query = $request->all();

        if($user->hasRole('organisation')) {
            // $filtered_leads = $leads->filter(function($lead) use($user_org) {
            //     $leadsEscalation = LeadEscalation::where(['lead_id' => $lead->id, 'is_active' => 1])->first();
            //     if ($leadsEscalation->organisation_id == $user_org->id) {
            //         return $lead;
            //     }
            // });
            foreach ($leads as $lead) {
            $lead['notifications'] = Notification::select('notifications.*', 'organisations.name as org_name')
            ->leftjoin('organisations', 'organisations.id', '=', 'notifications.metadata->organisation_id')
            ->whereJsonContains('notifications.metadata->lead_id', $lead->id)
            ->get();
            }

        } else {
        foreach ($leads as $lead) {
            $lead['notifications'] = Notification::select('notifications.*', 'organisations.name as org_name')
            ->leftjoin('organisations', 'organisations.id', '=', 'notifications.metadata->organisation_id')
            ->whereJsonContains('notifications.metadata->lead_id', $lead->id)
            // ->where('notifications.metadata->notification_type', '=', 'notification')
            ->where('notifications.metadata->to', '=', 'admin')
            ->orderBy('id', 'DESC')
            // ->take(10)
            ->get();
        }
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

    public function readNotification($id) {
        $user = auth()->user();
        $notification = Notification::where('id', $id)->first();

        if ( isset($notification) ){
            if($user->hasRole('organisation')){
                $notification->is_read_organization = 1;
            }else{
                $notification->is_read = 1;
            }

            $notification->save();

            return response()->json([
                'success' => true,
                'data' => $notification,
                'message' => __('messages.notification_found')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => __('messages.notification_not_found')
            ]);
        }
    }

    public function readAllNotifications() {
        $user = auth()->user();
        $notifications = Notification::all();

        if ( isset($notifications) ){
            if($user->hasRole('organisation')){
                $notifications->where('metadata.organisation_id', $user->id);
                foreach($notifications as $notification){
                    $notification->update(['is_read_organization' => 1]);
                }
            }else{
                foreach($notifications as $notification){
                    $notification->update(['is_read' => 1]);
                }
            };

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'message' => __('messages.notification_found')
            ]);
        } else {

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => __('messages.notification_not_found')
            ]);
        }
    }

    public function testSms(){
        $sms_global_from = env('SMSGLOBAL_FROM', 'Traleado');
        $sms_global_user = env('SMSGLOBAL_USER', 'topidea');
        $sms_global_password = env('SMSGLOBAL_PASSWORD', 'dOxmcsTQ');

        $message = 'test message SMSGlobal';
        $contact_number = '+639426771301';

        if(env('SMS_ENABLED', true) == false) {
            return 'no email was sent. sms is disbled.';
        }

        # for debug purpose
        if(env('SMS_RECEIVER')) {
            $contact_number = env('SMS_RECEIVER');
        }

        $client = new \GuzzleHttp\Client();

        $response = $client->get('https://api.smsglobal.com/http-api.php', [
            'query' => [
                'action' => 'sendsms',
                'user' => $sms_global_user,
                'password' => $sms_global_password,
                'from' => $sms_global_from,
                'to' => $contact_number,
                'text' => rawurlencode($message),
                'maxsplit' => 2
            ]
        ]);

        return $response;
    }
}
