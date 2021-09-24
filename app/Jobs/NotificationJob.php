<?php

namespace App\Jobs;

use App\Notification;
use App\Organisation;
use App\LeadEscalation;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\NotificationCreateEvent;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $lead_escalation = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(LeadEscalation $lead_escalation)
    {
        $this->lead_escalation = $lead_escalation;
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

            $notification = new Notification();
            $lead_escalation_status = "{$this->lead_escalation->escalation_level} - {$this->lead_escalation->escalation_status}";

            $this->lead_escalation->organisation = Organisation::find($this->lead_escalation->organisation_id);
            $organisation_notifications = $this->lead_escalation->organisation->notifications ?? ['sms', 'notification', 'email'];

            //we need to change email value to notification, since notification is use from messages.
            $key_to_change = array_search('email', $organisation_notifications, true);
            if(!$key_to_change){
                // $organisation_notifications[$key_to_change] = 'notification';
                $organisation_notifications[count($organisation_notifications)] = 'email';
            }

            # Save Notifications
            $user_types = ['admin', 'organization'];
            $notification_types = ['sms', 'notification', 'email', 'activity_log'];
            foreach($user_types as $user_type) {
                foreach($notification_types as $notification_type) {
                    if($notification->hasNotify($lead_escalation_status, $user_type, $notification_type)) {
                        // $allowSaveActivity = true;
                        $message = $notification->getMessage($lead_escalation_status, $user_type, $notification_type);

                        //change if respose is "I've tried contacting the Enquirer"
                        if(isset($this->lead_escalation->metadata['response']) && ! empty($this->lead_escalation->metadata['response']) &&
                            $this->lead_escalation->metadata['response'] == "I've tried contacting the Enquirer" && $user_type == 'organization' &&
                            $lead_escalation_status == 'Confirm Enquirer Contacted - Awaiting Response'){

                                $message['description'] = "Your response has been received. Please contact the Enquirer as soon as possible and update the lead status";
                                // $allowSaveActivity = false;
                        }

                        //change if respose is "I've tried contacting the Enquirer"
                        if(isset($this->lead_escalation->metadata['response']) && ! empty($this->lead_escalation->metadata['response']) &&
                            $this->lead_escalation->metadata['response'] == "I've tried contacting the Enquirer" && $user_type == 'admin' &&
                            $lead_escalation_status == 'Confirm Enquirer Contacted - Awaiting Response'){

                                $message['description'] = "[org_name] updated the lead with the following response - " . $this->lead_escalation->metadata['response'];
                                // $allowSaveActivity = false;
                        }

                        $description = $this->replaceReasonAndComments($message['description'], $this->lead_escalation);
                        $description = str_replace('[org_name]', $this->lead_escalation->organisation->name ?? '', $description);

                        //if reason is empty, we will use metatdata['response']
                        if(isset($this->lead_escalation->metadata['response'])){
                            $description = str_replace('[reason]', $this->lead_escalation->metadata['response'], $description);
                        }

                        if(isset($this->lead_escalation->metadata['new_organisation_assigned'])){
                            $organization_id = $this->lead_escalation->metadata['new_organisation_assigned']['organisation_id'];
                            $organization = Organisation::find($organization_id);
                            $description = str_replace('[new_org_name]', $organization->name, $description);
                        }

                        $description = str_replace('[suburb_name]', $this->lead_escalation->lead->customer->address->suburb ?? '', $description);
                        $description = str_replace('[postcode]', $this->lead_escalation->lead->customer->address->postcode ?? '', $description);
                        $description = str_replace('[state]', $this->lead_escalation->lead->customer->address->state ?? '', $description);
                        $description = str_replace('[date_chosen]', date('d/m/Y', strtotime($this->lead_escalation->progress_period_date)), $description);
                        $description = str_replace('[installed_date]', date('d/m/Y', strtotime($this->lead_escalation->installed_date)), $description);
                        $description = str_replace(
                            '[installation_dimensions]',
                            "Gutter edge {$this->lead_escalation->gutter_edge_meters}m;Valley: {$this->lead_escalation->valley_meters}", $description
                        );
                        $description = str_replace('[lead_history_link]', asset('/'), $description);

                        if(isset($this->lead_escalation->metadata['option']) && ! empty($this->lead_escalation->metadata['option'])) {
                            $description = str_replace('[option]', $this->lead_escalation->metadata['option'], $description);
                        }else{
                            $description = str_replace('[option]', '', $description);
                        }

                        $customer_address = $this->lead_escalation->lead->customer->address->full_address ?? '';
                        $lead_id = ($this->lead_escalation->lead->lead_id) ? $this->lead_escalation->lead->lead_id : $this->lead_escalation->lead_id;
                        $enquirer_name = "{$this->lead_escalation->lead->customer->first_name} {$this->lead_escalation->lead->customer->last_name}";

                        $lead_detail = "%0ALead ID: {$lead_id}%0AEnquirer: {$enquirer_name}%0AAddress: {$customer_address}";

                        $description = str_replace('[lead_id]', $lead_id, $description);
                        $description = str_replace('[lead_detail]', $lead_detail, $description);

                        $notif_structure = [
                            'title' => $message['title'],
                            'description' => $description,
                            'metadata' => [
                                'to' => $user_type,
                                'lead_id' => $this->lead_escalation->lead_id,
                                'organisation_id' => $this->lead_escalation->organisation_id,
                                'notification_type' => $notification_type,
                                'lead_escalation_status' => $lead_escalation_status,
                                'lead_escalation_color' => $this->lead_escalation->leadEscalationStatus($lead_escalation_status) ?? 'purple',
                                'lead_id_format' => $lead_id,
                            ]
                        ];

                        //checked if allow to save base from organisation settings
                        if(in_array($notification_type, $organisation_notifications) && $user_type == 'organization'){
                            Notification::create($notif_structure);
                        }else{
                            //save as admin
                            Notification::create($notif_structure);
                        }
                        // else{
                        //     if($allowSaveActivity){
                        //         //checked if allow to save base from organisation settings
                        //         if(in_array($notification_type, $organisation_notifications) && $user_type == 'organization'){
                        //             $notification->logActivity(
                        //                 $this->lead_escalation,
                        //                 $notif_structure
                        //             );
                        //         }else{
                        //             //save as admin
                        //             $notification->logActivity(
                        //                 $this->lead_escalation,
                        //                 $notif_structure
                        //             );
                        //         }
                        //     }
                        // }

                        //save all notification types
                        $notification->logActivity(
                            $this->lead_escalation,
                            $notif_structure
                        );

                        if($notification_type == 'sms' && $user_type == 'organization'
                            && isset($this->lead_escalation->organisation->contact_number)
                            && ! empty($this->lead_escalation->organisation->contact_number)
                            && (! isset($this->lead_escalation->metadata['send_sms']) || $this->lead_escalation->metadata['send_sms'] == true)
                            && (! isset($this->lead_escalation->metadata['notif_config']['send_sms']) || $this->lead_escalation->metadata['notif_config']['send_sms'] == true)) {

                            if(in_array('sms', $organisation_notifications)){
                                Notification::send_sms($this->lead_escalation->organisation->contact_number, $description);
                            }
                        }
                    }
                }
            }

            \DB::commit();
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \DB::rollback();
        }
    }

    public function replaceReasonAndComments($content, $lead_escalation) {
        if(!empty($lead_escalation->reason)){
            $content = str_replace('[reason]', $lead_escalation->reason, $content);
        }
        $content = str_replace('[comments]', $lead_escalation->comments, $content);

        return $content;

        // $newContent = str_replace('[reason]', $lead_escalation->reason, $content);
        // $newContent = str_replace('[comments]', $lead_escalation->comments, $newContent);
        //return $newContent;
    }
}
