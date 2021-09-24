<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Setting;

class NotifyOrganizationSuspendedStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $setting = Setting::where('key', 'email-brand-logo-path')->first();

        $image_path = url('/app-assets/img/traleado-logo.png');
        $image_path = ($setting && $setting->url) ? $setting->url : $image_path;
        $data['custom_logo'] = $image_path;

        $this->data = $data;
    }
      
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.organization.notification_unsuspension')
            ->replyTo( config('mail.reply_to.address'), config('mail.reply_to.name') )
            ->from(config('mail.from.address'), 'Traleado')
            ->subject($this->data['title']);
    }
}
