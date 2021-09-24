<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Setting;

class InquirerNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($title, $message)
    {
        $this->data['title'] = $title ?? '';
        $this->data['message'] = $message ?? '';

        $logo = Setting::where('key', 'main-logo')->first();

        if($logo && $logo->value) {
            $this->data['custom_logo'] = $logo->value;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.inquirer.notification')
            ->from(config('mail.from.address'), 'LeafStopper')
            ->subject($this->data['title']);
    }
}
