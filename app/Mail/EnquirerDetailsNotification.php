<?php

namespace App\Mail;

use App\LeadEscalation;
use App\Organisation;
use App\Notification;
use Carbon\Carbon;
use App\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class EnquirerDetailsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($title, LeadEscalation $lead_escalation)
    {
        $data = [];

        # get data
        $data['title'] = $title;

        $logo = Setting::where('key', 'main-logo')->first();

        if($logo && $logo->value) {
            $data['custom_logo'] = $logo->value;
        }
        $timezone = Auth::user()->metadata['timezone'] ?? 'Asia/Manila';

        $data['inquirer'] = [
            'name' => "{$lead_escalation->lead->customer->first_name} {$lead_escalation->lead->customer->last_name}",
            'address' => $lead_escalation->lead->customer->address->full_address,
            'email' => $lead_escalation->lead->customer->email,
            'contact_number' => $lead_escalation->lead->customer->contact_number
        ];

        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.organization.inquirer_details')
            ->from(config('mail.from.address'), 'Traleado' )
            ->subject($this->data['title']);
    }
}
