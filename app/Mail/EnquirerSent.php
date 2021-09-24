<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Setting;

class EnquirerSent extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($stores, $message)
    {
        $this->data['title'] = 'Store Details';
        $this->data['message'] = $message ?? '';
        $this->data['stores'] = $stores;

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
        return $this->markdown('emails.inquirer.sent')
            ->from(config('mail.from.address'), 'LeafStopper')
            ->subject($this->data['title']);
    }
}
