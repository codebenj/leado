<?php

namespace App\Mail;

use App\LeadEscalation;
use App\Organisation;
use App\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(LeadEscalation $lead_escalation, $email_data = [], $is_lead_escalation = true)
    {
        if($is_lead_escalation){
            $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
            $lead_id = ($lead_escalation->lead->lead_id) ? $lead_escalation->lead->lead_id : $lead_escalation->lead_id;

            if(strpos($lead_escalation_status, 'In Progress - Extended') !== false) {
                $lead_escalation_status = 'In Progress - Extended';
            }

            $data = [];

            # get data
            $notification = new Notification();

            $email_data = $email_data ?? $notification->parseMessages();
            $data['title'] = $email_data[$lead_escalation_status]['admin']['email']['title'] ?? '';
            $data['message'] = $email_data[$lead_escalation_status]['admin']['email']['message'] ?? '';

            //change if respose is "I've tried contacting the Enquirer"
            if(isset($lead_escalation->metadata['response']) && !empty($lead_escalation->metadata['response'])
                && $lead_escalation->metadata['response'] == "I've tried contacting the Enquirer" &&
                $lead_escalation_status == 'Confirm Enquirer Contacted - Awaiting Response'){

                $data['message'] = "[org_name] updated the lead with the following response - ".$lead_escalation->metadata['response'];
            }

            $message = $data['message'];
            $message = str_replace('[org_name]', $lead_escalation->organisation->name ?? '', $message);
            $message = str_replace('[date_chosen]', date('d/m/Y', strtotime($lead_escalation->progress_period_date)), $message);
            $message = str_replace('[reason]', $lead_escalation->reason, $message);
            $message = str_replace('[comments]', $lead_escalation->comments, $message);

            if(isset($lead_escalation->metadata['option']) && !empty($lead_escalation->metadata['option'])) {
                $message = str_replace('[option]', $this->lead_escalation->metadata['option'], $message);
            }else{
                $message = str_replace('[option]', '', $message);
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
            $data['message'] = $message;

            if(isset($email_data[$lead_escalation_status]['admin']['email']['show_fields'])
                && in_array('org', $email_data[$lead_escalation_status]['admin']['email']['show_fields'])) {

                $data['org'] = [
                    'lead_id' => $lead_id,
                    'name' => $lead_escalation->organisation->name ?? '',
                    'contact_number' => $lead_escalation->organisation->contact_number ?? '',
                    'email' => $lead_escalation->organisation->user->email,
                    'manual_escalation' => $this->isManualEscalation($lead_escalation->organisation),
                ];
            }

            if(isset($email_data[$lead_escalation_status]['admin']['email']['show_fields'])
                && in_array('inquirer', $email_data[$lead_escalation_status]['admin']['email']['show_fields'])) {

                $data['inquirer'] = [
                    'name' => "{$lead_escalation->lead->customer->first_name} {$lead_escalation->lead->customer->last_name}",
                    'address' => $lead_escalation->lead->customer->address->full_address ?? '',
                    'email' => $lead_escalation->lead->customer->email,
                ];
            }

            if(isset($email_data[$lead_escalation_status]['admin']['email']['show_fields'])
                && in_array('reason', $email_data[$lead_escalation_status]['admin']['email']['show_fields'])) {

                $message = $lead_escalation->reason;
                if(! empty($lead_escalation->metadata['other_reason'])){
                    $message .= ' - ' . $lead_escalation->metadata['other_reason'];
                }
                if(! empty($lead_escalation->metadata['indicate_reason'])){
                    $message .= ' - ' . $lead_escalation->metadata['indicate_reason'];
                }

                $data['reason'] = $message;
            }

            if(isset($email_data[$lead_escalation_status]['admin']['email']['show_fields'])
                && in_array('comments', $email_data[$lead_escalation_status]['admin']['email']['show_fields'])) {
                    $data['comments'] = $lead_escalation->comments;
            }

            if(isset($email_data[$lead_escalation_status]['admin']['email']['show_fields'])
                && in_array('lead_id', $email_data[$lead_escalation_status]['admin']['email']['show_fields'])) {

                $data['lead_id'] = $lead_id;
            }

            if(isset($email_data[$lead_escalation_status]['admin']['email']['show_fields'])
                && in_array('installed', $email_data[$lead_escalation_status]['admin']['email']['show_fields'])) {

                $data['installed'] = [
                    'meters_gutter_edge' => $lead_escalation->gutter_edge_meters ?? '',
                    'meters_valley' => $lead_escalation->valley_meters ?? '',
                ];
            }

            if(isset($email_data[$lead_escalation_status]['admin']['email']['show_fields'])
                && in_array('date_installed', $email_data[$lead_escalation_status]['admin']['email']['show_fields'])) {

                $installed_date = (! empty($lead_escalation->installed_date)) ? date('d/m/Y', strtotime($lead_escalation->installed_date)) : '';

                $data['date_installed'] = $installed_date;
            }
        }else{
            $data = $email_data;
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
        return $this->markdown('emails.admin.notification')
            ->replyTo(config('mail.from.address'),'Traleado' )
            ->from(config('mail.from.address'),'Traleado' )
            ->subject($this->data['title']);
    }

    /**
     *  Check organisation if manual escalation is enabled
     *
     * @return boolean
     */
    public function isManualEscalation( $org ) {
        return $org->metadata &&
                $org->metadata['manual_update'] &&
                $org->metadata['manual_update'] == true;
    }
}
