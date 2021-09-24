<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\NotifyOrganizationSuspendedStatus;
use App\Notification;
use App\Organisation;
use App\LeadEscalation;
use App\Mail\AdminNotification;
use App\Setting;
use App\User;

use Mail;

class NotifyOrgSuspendedStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data = [];
    private $org, $metadata;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($org_id, $metadata = [])
    {
        $org = Organisation::find($org_id);

        if( isset($org) ){

            $data = [];

            if($org->is_suspended) {
                $data['title'] = 'ORGANISATION SUSPENDED';
                $data['message'] = $org->name.' has been suspended from receiving new leads - Manually by admin';
            }else{
                $data['title'] = 'ORG UNSUSPENDED';
                $data['message'] = 'Your account was unsuspended by Admin and you can now receive new leads.';
            }

            $data['login_link'] = asset('/login');
            $data['org_name'] = $org->name;

            $this->org = $org;
            $this->data = $data;
            $this->metadata = $metadata;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            if($this->org && !$this->org->is_suspended) {
                //no logs for this actions
                // $notifStructure = [
                //     'title' => $this->data['title'] ?? '',
                //     'description' => 'org unsuspension',
                //     'metadata' => [
                //         'to' => 'organization',
                //         'message' => $this->data['message'],
                //         'organisation_id' => $this->org->id,
                //         'notification_type' => 'sms, email, activity log',
                //     ]
                // ];

                // // save notification
                // $notification = Notification::create($notifStructure);

                // send sms
                if( isset($this->org->contact_number) && !empty($this->org->contact_number)) {
                    Notification::send_sms($this->org->contact_number, $this->data['message']);
                }

                //no logs for this actions
                // // log activity
                // $notification->logActivity(
                //     new Organisation,
                //     $notifStructure,
                //     'org unsuspension notification'
                // );

                // send email notification
                $mail = new NotifyOrganizationSuspendedStatus($this->data);
                Mail::to($this->org->user->email)->send($mail);

            }else{
                //email recipients from admin settings
                // $recipients = Setting::where('key', 'admin-email-notification-receivers')->first();
                // $recipients = explode(',', $recipients->value);

                // $lead_escalation = LeadEscalation::first();

                // foreach($recipients as $recipient){
                //     Mail::to($recipient)->queue(new AdminNotification($lead_escalation, $this->data, false));
                // }
            }

           \DB::commit();
        } catch(\Exception $e) {
            \Log::error('Error on sending notification:');
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \DB::rollback();

            $this->fail(new \Exception('Unable to send notification'));
        }
    }
}
