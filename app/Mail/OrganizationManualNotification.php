<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;
use App\Setting;

class OrganizationManualNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];

    private $final = ['Won', 'Lost'];

    private $status = ['Pending', 'Declined-Lapsed', 'Awaiting Response', 'Awaiting Response - Reminder Sent', 'Awaiting Response - Admin Notified', 'Parked'];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Organisation $organisation)
    {
        $data = [];

        $data['title'] = 'Leaf Stopper Leads Update Request';
        $data['organisation_name'] = $organisation->name;

        $organisation_id = $organisation->id;

        $leads = Lead::whereHas('active_escalation', function($q) use($organisation_id){
            $q->where('organisation_id', $organisation_id);
            $q->whereNull('metadata->is_final');
            $q->whereIn('escalation_status', $this->status);
        })->with(['active_escalation', 'customer.address'])->get();

        foreach($leads as $lead){
            if(in_array($lead->active_escalation[0]->escalation_level, $this->final)){
                $lead_escalation = LeadEscalation::find($lead->active_escalation[0]->id);
                $metadata = $lead_escalation->metadata;
                $metadata['is_final'] = true;
                $lead_escalation->metadata = $metadata;
                $lead_escalation->update();
            }

            $data['leads'][] = [
                'lead_id' => $lead->lead_id ?? $lead->id,
                'enquirer' => $lead->customer->first_name . ' ' . $lead->customer->last_name,
                'address' => $lead->customer->address->full_address,
                'contact_number' => $lead->customer->contact_number,
            ];
        }

        $logo = Setting::where('key', 'main-logo')->first();

        if($logo && $logo->value) {
            $data['custom_logo'] = $logo->value;
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
        return $this->markdown('emails.organization.manual')
            ->from(config('mail.from.address'), 'Traleado' )
            ->subject($this->data['title']);
    }
}
