<?php

namespace App\Mail;

use App\LeadEscalation;
use App\Organisation;
use App\Notification;
use App\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganizationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(LeadEscalation $lead_escalation, $notification_messages = [])
    {
        $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
        $lead_id = ($lead_escalation->lead->lead_id) ? $lead_escalation->lead->lead_id : $lead_escalation->lead_id;

        if(strpos($lead_escalation_status, 'In Progress - Extended') !== false) {
            $lead_escalation_status = 'In Progress - Extended';
        }

        $data = [];

        # get data
        $data['title'] = $notification_messages[$lead_escalation_status]['organization']['email']['title'] ?? '';
        $data['message'] = $notification_messages[$lead_escalation_status]['organization']['email']['message'] ?? '';

        //change if respose is "I've tried contacting the Enquirer"
        if(isset($lead_escalation->metadata['response']) && !empty($lead_escalation->metadata['response'])
            && $lead_escalation->metadata['response'] == "I've tried contacting the Enquirer" &&
            $lead_escalation_status == 'Confirm Enquirer Contacted - Awaiting Response'){

            $data['message'] = "Your response has been received. Please contact the Enquirer as soon as possible and update the lead status";
        }

        $message = $data['message'];
        $message = str_replace('[org_name]', $lead_escalation->organisation->name, $message);
        $message = str_replace('[reason]', $lead_escalation->reason, $message);
        $message = str_replace('[suburb_name]', $lead_escalation->lead->customer->address->suburb ?? '', $message);
        $message = str_replace('[postcode]', $lead_escalation->lead->customer->address->postcode ?? '', $message);
        $message = str_replace('[state]', $lead_escalation->lead->customer->address->state ?? '', $message);
        $message = str_replace('[comments]', $lead_escalation->reason, $message);
        $message = str_replace('[date_chosen]', date('d/m/Y', strtotime($lead_escalation->progress_period_date)), $message);
        $message = str_replace('[date_of_installation]', date('d/m/Y', strtotime($lead_escalation->installed_date)), $message);

        $message = str_replace(
            '[installation_dimensions]',
            "Gutter edge {$lead_escalation->gutter_edge_meters}m;Valley: {$lead_escalation->valley_meters}m", $message
        );

        $setting = Setting::where('name', $lead_escalation_status)->first();

        if($setting) {
            $time_type = $setting->metadata['type'];
            $time_left = "{$setting->value} {$time_type}";
            $message = str_replace("[$setting->key]", $time_left, $message);
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

        if(isset($notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])
            && in_array('inquirer', $notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])) {

            $data['inquirer'] = [
                'name' => "{$lead_escalation->lead->customer->first_name} {$lead_escalation->lead->customer->last_name}",
                'address' => $lead_escalation->lead->customer->address->full_address ?? '',
                'email' => $lead_escalation->lead->customer->email,
            ];
        }

        if(isset($notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])
            && in_array('reason', $notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])) {

            $message = $lead_escalation->reason;
            if(!empty($lead_escalation->metadata['other_reason'])){
                $message .= ' - '.$lead_escalation->metadata['other_reason'];
            }

            $data['reason'] = $message;
        }

        if(isset($notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])
            && in_array('comments', $notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])) {

            $data['comments'] = $lead_escalation->comments;
        }

        if(isset($notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])
            && in_array('lead_id', $notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])) {

            $data['lead_id'] = $lead_id;
        }

        if(isset($notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])
            && in_array('installed', $notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])) {

            $data['installed'] = [
                'meters_gutter_edge' => $lead_escalation->gutter_edge_meters ?? '',
                'meters_valley' => $lead_escalation->valley_meters ?? ''
            ];
        }

        if(isset($notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])
            && in_array('date_installed', $notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])) {

            $data['date_installed'] = $lead_escalation->installed_date ?? '';
        }

        if(isset($notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])
            && in_array('login_link', $notification_messages[$lead_escalation_status]['organization']['email']['show_fields'])) {

            $data['login_link'] = asset('/');
        }

        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.organization.notification')
            ->from(config('mail.from.address'), 'Traleado' )
            ->subject($this->data['title']);
    }
}
