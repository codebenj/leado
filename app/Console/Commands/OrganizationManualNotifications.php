<?php

namespace App\Console\Commands;

use App\Mail\OrganizationManualNotification;
use Illuminate\Console\Command;
use App\Organisation;
use Illuminate\Support\Facades\Mail;
use App\Exports\OrganizationManualNotificationExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class OrganizationManualNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:organisations {timezone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending notification to organisation on what status of the leads assigned to them';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //get timezone from the paramenters
        $timezone = $this->argument('timezone');

        $status = ['Pending', 'Declined-Lapsed', 'Awaiting Response', 'Awaiting Response - Reminder Sent', 'Awaiting Response - Admin Notified', 'Parked'];

        $organisations = Organisation::whereHas('active_escalation', function($q) use($status){
            $q->whereNull('metadata->is_final');
            $q->whereIn('escalation_status', $status);
        })->whereHas('user', function($q) use($timezone){
            if($timezone == 'Australia/Sydney'){
                $q->whereNull('metadata');
                $q->orWhereJsonContains('metadata->timezone', $timezone);
            }else{
                $q->whereJsonContains('metadata->timezone', $timezone);
            }
        })
        ->whereJsonContains('metadata->manual_update', true)->orderBy('name', 'asc')->get();

        foreach($organisations as $organisation){
            try{ $email = $organisation->organisation_users[0]->user->email; }
            catch(\Exception $ex){ $email = ''; }

            if(! empty($email)){
                Mail::to($email)->send(new OrganizationManualNotification($organisation));
            }
        }
    }
}
