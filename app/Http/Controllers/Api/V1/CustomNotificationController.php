<?php

namespace App\Http\Controllers\Api\V1;

use App\CustomNotification as AppCustomNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\CustomNotificationEvent;
use App\Mail\CustomNotification;
use Illuminate\Support\Facades\Mail;
use App\Organisation;
use App\Lead;
use App\Notification;
use App\User;

class CustomNotificationController extends Controller
{
    public function customNotificationSend(Request $request){
        $to_notify_groups = $this->sendToGroups($request);

        return response()->json([
            'success' => true,
            'data' => $to_notify_groups,
            'message' => __('notifications.custom_notification_success')
        ]);
    }

    private function sendToGroups($request){
        $to_notify_groups = [];
        $all_orgs = Organisation::with('user')->get();
        $group_index = 0;
        if($request->notify == 'orgs-all'){
            $to_notify_groups = $all_orgs;
        }else if($request->notify == 'orgs-auto'){
            $to_notify_groups = Organisation::whereJsonContains('metadata->manual_update', false)->get();
        }else if($request->notify == 'orgs-manual'){
            $to_notify_groups = Organisation::whereJsonContains('metadata->manual_update', true)->get();
        }else if($request->notify == 'orgs-critical-leads'){
            foreach($all_orgs as $org){
                if($org->has_critical){
                    $to_notify_groups[] = $org;
                }
            }
        }else if($request->notify == 'orgs-on-hold-sys'){
            foreach($all_orgs as $org){
                if (strpos($org->account_status_type_selection, 'Sys') !== false) {
                    $to_notify_groups[] = $org;
                }
            }
        }else if($request->notify == 'orgs-on-hold-admin'){
            foreach($all_orgs as $org){
                if (strpos($org->account_status_type_selection, 'Admin') !== false) {
                    $to_notify_groups[] = $org;
                }
            }
        }else if($request->notify == 'enquirers-all'){
            $to_notify_groups = Lead::with('customer')->get();
            $group_index = 1;
        }else if($request->notify == 'enquirers-supply-install'){
            $to_notify_groups = Lead::where('customer_type', 'Supply & Install')->with('customer')->get();
            $group_index = 1;
        }else if($request->notify == 'enquirers-supply-only'){
            $to_notify_groups = Lead::where('customer_type', 'Supply Only')->with('customer')->get();
            $group_index = 1;
        }else if($request->notify == 'enquirers-general-enquiry'){
            $to_notify_groups = Lead::where('customer_type', 'General Enquiry')->with('customer')->get();
            $group_index = 1;
        }else if($request->notify == 'users-all'){
            $to_notify_groups = User::with(['user_role', 'organisation_user', 'organisation_user.organisation'])->get();
            $group_index = 2;
        }else if($request->notify == 'users-admin-role'){
            $to_notify_groups = User::with(['user_role', 'organisation_user', 'organisation_user.organisation'])->whereHas('roles', function($q){
                $q->whereIn('name', ['super admin', 'administrator']);
            })->get();
            $group_index = 2;
        }else if($request->notify == 'users-user-role'){
            $to_notify_groups = User::with(['user_role', 'organisation_user', 'organisation_user.organisation'])->whereHas('roles', function($q){
                $q->where('name', 'user');
            })->get();
            $group_index = 2;
        }

        //group_index 0=orgs, 1=customers, 2=users
        $notification_type = $request->notification_type;

        if($group_index == 0){
            foreach($to_notify_groups as $group){
                foreach($notification_type as $type){
                    if($type == 'SMS'){
                        if(!empty($group->contact_number)){
                            Notification::send_sms($group->contact_number, $request->body);
                            $request->merge(['contact_number' => $group->contact_number, 'user_id' => $group->user_id]);
                        }
                    }
                    if($type == 'Email'){
                        if(!empty($group->user->email)){
                            Mail::to($group->user->email)->queue(new CustomNotification($request->title, $request->body));
                            $request->merge(['email_address' => $group->user->email, 'user_id' => $group->user_id]);
                        }
                    }
                    if($type == 'Real Time'){
                        event(new CustomNotificationEvent($group->user_id, $request->all()));
                        $request->merge(['user_id' => $group->user_id]);
                    }
                }
                $this->saveCustomNotification($request);
            }
        }else if($group_index == 1){
            foreach($to_notify_groups as $group){
                foreach($notification_type as $type){
                    if($type == 'SMS'){
                        if(!empty($group->customer->contact_number)){
                            Notification::send_sms($group->customer->contact_number, $request->body);
                            $name = $group->customer->first_name .' '. $group->customer->last_name;
                            $request->merge(['contact_number' => $group->customer->contact_number, 'enquirer_name' => $name]);

                        }
                    }
                    if($type == 'Email'){
                        if(!empty($group->customer->email)){
                            Mail::to($group->customer->email)->queue(new CustomNotification($request->title, $request->body));
                            $request->merge(['email_address' => $group->customer->email]);
                        }
                    }
                    if($type == 'Real Time'){
                    }
                }
                $this->saveCustomNotification($request);
            }
        }else if($group_index == 2){
            foreach($to_notify_groups as $group){
                foreach($notification_type as $type){
                    if($type == 'SMS'){
                        if(!empty($group->phone)){
                            Notification::send_sms($group->phone, $request->body);
                            $contact_number = (!empty($group->phone)) ? $group->phone : $group->organisation_user->organisation->contact_number;
                            $request->merge(['contact_number' => $contact_number, 'user_id' => $group->id]);
                        }
                    }
                    if($type == 'Email'){
                        if(!empty($group->email)){
                            Mail::to($group->email)->queue(new CustomNotification($request->title, $request->body));
                            $request->merge(['email_address' => $group->email]);
                        }
                    }
                    if($type == 'Real Time'){
                        event(new CustomNotificationEvent($group->id, $request->all()));
                        $request->merge(['user_id' => $group->id]);
                    }
                }
                $this->saveCustomNotification($request);
            }
        }

        return $to_notify_groups;
    }

    private function saveCustomNotification(Request $request){
        $request->merge(['message' => $request->body]);
        AppCustomNotification::create($request->except(['body']));
    }

    private function sendSms($number, $message){
        $sms_global_from = env('SMSGLOBAL_FROM', 'Traleado');
        $sms_global_user = env('SMSGLOBAL_USER', 'topidea');
        $sms_global_password = env('SMSGLOBAL_PASSWORD', 'dOxmcsTQ');

        if(env('SMS_ENABLED', true) == false) {
            return 'no email was sent. sms is disbled.';
        }

        $contact_number = $number;

        # for debug purpose
        if(env('SMS_RECEIVER')) {
            $contact_number = env('SMS_RECEIVER');
            $message = 'test message SMSGlobal';
            $contact_number = '+639426771301';
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')) {
            $notification = AppCustomNotification::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $notification,
                'message' => __('notifications.custom_notification_success'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorize_response'),
            'data' => [],
        ], 400);
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
