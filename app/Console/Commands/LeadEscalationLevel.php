<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LeadEscalation;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadEscalationLevel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:escalation-level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check lead escalation level when its expired change the status';

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
        // GET ALL LEAD ESCALATION THAT IS NOT EXPIRED
        // $lead_not_expireds = LeadEscalation::where('is_active', 1)
        //     ->whereNotNull('expiration_date')
        //     ->where(DB::raw("DATE_FORMAT(expiration_date, '%Y-%m-%d %H:%i')"), '>', now()->format('Y-m-d H:i'))
        //     ->get();

        // Log::info( 'Lead Count::' . $lead_not_expireds->count() );

        // foreach ( $lead_not_expireds as $lead_not_expired ) {
        //     $lead_escalation_status_ne = "{$lead_not_expired->escalation_level} - {$lead_not_expired->escalation_status}";
        //     $countdown_ne = Setting::where( 'name', $lead_escalation_status_ne )->first();
        //     Log::info( 'Settings::' . json_encode( $countdown_ne ) . ' :: ' . $lead_escalation_status_ne );

        //     if ( $countdown_ne && ! empty( $countdown_ne->value ) ) {
        //         $time_key_ne = $countdown_ne->value;
        //         $time_type_ne = ucfirst( $countdown_ne->metadata['type'] );
        //         $time_key_func_ne = "add{$time_type_ne}";

        //         Log::info("=========={$lead_not_expired->id}=========");
        //         Log::info("Lead Escalation Status: " . $lead_escalation_status_ne);
        //         Log::info("Cap: " . $time_key_ne . " " . $time_type_ne . " -- " . $time_key_func_ne);

        //         // EXTENSIONS
        //         $extensions = explode( ", ", LeadEscalation::getExtensions() );
        //         $extension_in_seconds = $extensions[0];
        //         $extension_in_hours = $extensions[1];
        //         $extension_in_days = $extensions[2];
        //         $extension_in_months = $extensions[3];

        //         Log::info("Extension in seconds: " . $extension_in_seconds);
        //         Log::info("Extension in hours: " . $extension_in_hours);
        //         Log::info("Extension in days: " . $extension_in_days);
        //         Log::info("Extension in months: " . $extension_in_months);

        //         // Log::info("Current Expiration Date: " . $lead_not_expired->expiration_date);
        //         $expDate = $lead_not_expired->expiration_date;

        //         // SECONDS
        //         if ( $time_type_ne == "Seconds" && $extension_in_seconds >= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Seconds - 0):', $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne ) );
        //             $this->addLog( 'Current Expiration Date:', $expDate );

        //         } else if ( $time_type_ne == "Seconds" && $extension_in_seconds <= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Seconds - 1):', $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds ) );
        //             $this->addLog( 'Current Expiration Date:', $expDate );
        //         }

        //         // HOURS
        //         if ( $time_type_ne == "Hours" && $extension_in_hours >= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Hours - 0):', $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne ) );
        //             $this->addLog( 'Current Expiration Date:', $expDate );

        //         } else if ( $time_type_ne == "Hours" && $extension_in_hours <= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Hours - 1):', $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds ));
        //             $this->addLog( 'Current Expiration Date:', $expDate );
        //         }

        //         // DAYS
        //         if ( $time_type_ne == "Days" && $extension_in_days >= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Days - 0):', $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne ) );
        //             $this->addLog( 'Current Expiration Date:', $expDate );

        //         } else if ( $time_type_ne == "Days" && $extension_in_days <= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Days - 1):', $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds ) );
        //             $this->addLog( 'Current Expiration Date:', $expDate );

        //         }

        //         // MONTHS
        //         if ( $time_type_ne == "Months" && $extension_in_months >= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Months - 0):', $this->parseNewExpDate( $expDate, $time_key_func_ne, $time_key_ne ) );
        //             $this->addLog( 'Current Expiration Date:', $expDate );

        //         } else if ( $time_type_ne == "Months" && $extension_in_months <= $time_key_ne ) {
        //             $lead_not_expired->expiration_date = $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds );
        //             $lead_not_expired->save();
        //             $this->addLog( 'Extended Expiration Date (Months - 1):', $this->parseNewExpDate( $expDate, 'addSeconds', $extension_in_seconds ) );
        //             $this->addLog( 'Current Expiration Date:', $expDate );
        //         }
        //         Log::info("=========={$lead_not_expired->id}=========");
        //     }
        // }

        //get all expired lead escalation get by date, hour and minute
        $lead_escalations = LeadEscalation::where('is_active', 1)
            ->whereNotNull('expiration_date')
            ->where(DB::raw("DATE_FORMAT(expiration_date, '%Y-%m-%d %H:%i')"), '<', now()->format('Y-m-d H:i'))
            ->get();

        foreach($lead_escalations as $lead_escalation){
            try{
                $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";

                if(LeadEscalation::leadEscalationStatus($lead_escalation_status) === false) {
                    continue;
                }

                //replicate lead escalation
                $new_lead_escalation = $lead_escalation->replicate();

                //set to in-active
                LeadEscalation::where(['lead_id' => $lead_escalation->lead_id])->update(['is_active' => 0]);

                if($lead_escalation_status == 'Accept Or Decline - Pending'){
                    $new_lead_escalation->escalation_level = 'Accept Or Decline';
                    $new_lead_escalation->escalation_status = 'Declined-Lapsed';
                    $lead_escalation_status = "{$new_lead_escalation->escalation_level} - {$new_lead_escalation->escalation_status}";
                    $new_lead_escalation->color = LeadEscalation::leadEscalationStatus($lead_escalation_status);
                }
                else if($lead_escalation_status == 'Confirm Enquirer Contacted - Awaiting Response'){
                    $new_lead_escalation->escalation_level = 'Confirm Enquirer Contacted';
                    $new_lead_escalation->escalation_status = 'Awaiting Response - Reminder Sent';
                    $lead_escalation_status = "{$new_lead_escalation->escalation_level} - {$new_lead_escalation->escalation_status}";
                    $new_lead_escalation->color = LeadEscalation::leadEscalationStatus($lead_escalation_status);
                }
                else if($lead_escalation_status == 'Confirm Enquirer Contacted - Awaiting Response - Reminder Sent'){
                    $new_lead_escalation->escalation_level = 'Confirm Enquirer Contacted';
                    $new_lead_escalation->escalation_status = 'Awaiting Response - Admin Notified';
                    $lead_escalation_status = "{$new_lead_escalation->escalation_level} - {$new_lead_escalation->escalation_status}";
                    $new_lead_escalation->color = LeadEscalation::leadEscalationStatus($lead_escalation_status);
                }
                else if(strpos($lead_escalation_status, 'In Progress - Extended') !== false){
                    $new_lead_escalation->escalation_level = 'In Progress';
                    $new_lead_escalation->escalation_status = 'Awaiting Response - Reminder Sent';
                    $new_lead_escalation->progress_period_date = null;
                    $lead_escalation_status = "{$new_lead_escalation->escalation_level} - {$new_lead_escalation->escalation_status}";
                    $new_lead_escalation->color = LeadEscalation::leadEscalationStatus($lead_escalation_status);
                }
                else if($lead_escalation_status == 'In Progress - Awaiting Response'){
                    $new_lead_escalation->escalation_level = 'In Progress';
                    $new_lead_escalation->escalation_status = 'Awaiting Response - Reminder Sent';
                    $lead_escalation_status = "{$new_lead_escalation->escalation_level} - {$new_lead_escalation->escalation_status}";
                    $new_lead_escalation->color = LeadEscalation::leadEscalationStatus($lead_escalation_status);
                }
                else if($lead_escalation_status == 'In Progress - Awaiting Response - Reminder Sent'){
                    $new_lead_escalation->escalation_level = 'In Progress';
                    $new_lead_escalation->escalation_status = 'Awaiting Response - Admin Notified';
                    $lead_escalation_status = "{$new_lead_escalation->escalation_level} - {$new_lead_escalation->escalation_status}";
                    $new_lead_escalation->color = LeadEscalation::leadEscalationStatus($lead_escalation_status);
                }

                if(LeadEscalation::isSuspendableEscalationStatus($lead_escalation_status)) {
                    $organization = $lead_escalation->organisation;
                    $organization->is_suspended = true;
                    $organization->save();
                }

                //set expiration_date null
                $new_lead_escalation->expiration_date = null;

                # update expiration date
                $countdown = Setting::where('name', $lead_escalation_status)->first();

                if($countdown && !empty($countdown->value)) {
                    $time_type = ucfirst($countdown->metadata['type']);
                    $time_key = "add{$time_type}";
                    $new_lead_escalation->expiration_date = now()->{$time_key}($countdown->value);
                    $new_lead_escalation->progress_period_date = now()->toISOString();
                    $new_lead_escalation->save();

                    \Log::info('TRIGGER NEW EXPIRATION DATE');
                    \Log::info($new_lead_escalation->expiration_date);
                    \Log::info($new_lead_escalation->progress_period_date);
                }

                $new_lead_escalation->reason = '';
                $new_lead_escalation->is_active = true;
                $new_lead_escalation->save();
            }catch(\Exception $ex){
                \Log::error($ex->getMessage());
                \Log::error($ex->getTraceAsString());
            }
        }

        return 0;
    }

    /**
     * Parse new Expiration date
     *
     * @return new expiration date based on min & max
     */
    public function parseNewExpDate( $oldDate, $keyTime, $time ) {
        return Carbon::parse($oldDate)->{$keyTime}($time);
    }

    /**
     * Create Log
     *
     * @return nothing
     */
    public function addLog( $title, $content ) {
        Log::info( $title . ':' . $content );
    }
}
