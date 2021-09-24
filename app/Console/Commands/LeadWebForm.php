<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LeadWebForm as WebForm;
use App\Lead;
use App\Customer;
use App\Address;
use App\LeadEscalation;
use App\Country;
use App\Events\LeadEscalationCreatedEvent;
use App\Events\NotificationCreateEvent;
use App\Jobs\NotificationJob;
use Propaganistas\LaravelPhone\PhoneNumber;

class LeadWebForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead-web-form:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process lead web form to lead in traleado';

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
        //get lead form that is not process
        $webforms = WebForm::where('is_uploaded', '!=', 1)->get();
        //$enquirer_names = array();
        $today = date('ymd');
        $count = count($webforms);
        foreach($webforms as $webform){
            try{
                \DB::beginTransaction();

                $format_counter = str_pad(++$count, 3, '0', STR_PAD_LEFT);
                $lead_id = $today . $format_counter;

                $country = $webform->country ?? 'Australia';
                $contact_number = $webform->contact_number ?? '';

                //add condition if country is United States of America (USA)
                if($country == 'United States of America (USA)'){
                    $country = 'United States of America';
                }

                try{
                    $country_data = (new \League\ISO3166\ISO3166)->name($country);
                    $mobile_number = PhoneNumber::make($contact_number, $country_data['alpha2']);
                    $contact_number = $mobile_number->formatE164();
                }catch(\Exception $ex){}

                $country = Country::where('name', $webform->country)->first();

                //check id if found. if not set to 14(Australia)
                $country_id = $country->id ?? 14;

                $address = Address::create([
                    'address' => $webform->address,
                    'city' => $webform->city,
                    'suburb' => $webform->suburb ?? '',
                    'state' => $webform->state,
                    'postcode' => $webform->postcode,
                    'country_id' => $country_id,
                ]);

                $customer = Customer::create([
                    'email' => $webform->email,
                    'first_name' => $webform->first_name,
                    'last_name' => $webform->last_name,
                    'contact_number' => $contact_number,
                    'address_id' => $address->id,
                ]);

                $data['customer_id'] = $customer->id;
                $data['customer_type'] = $webform->customer_type;
                $data['house_type'] = $webform->house_type;
                $data['roof_preference'] = $webform->roof_preference;
                $data['source'] = $webform->source;
                $data['comments'] = $webform->comments;
                $data['gutter_edge_meters'] = $webform->gutter_edge_meters;
                $data['valley_meters'] = $webform->valley_meters;
                $data['commercial'] = $webform->commercial;
                $data['use_for'] = $webform->situation;
                $data['commercial'] = $webform->commercial;
                $data['commercial_other'] = $webform->metadata['commercial_other'] ?? '';
                $data['received_via'] = 'Web Form';
                $data['metadata'] = $webform->metadata;
                $data['lead_id'] = $lead_id;
                //create lead
                $lead = Lead::create($data);

                $lead->updated_at = $webform->updated_at;
                $lead->save();

                // webform lead
                $webform->is_uploaded = 1;
                $webform->save();

                $leadEscalation = new LeadEscalation();

                if ( $webform->customer_type == 'General Enquiry' ) {

                    $escalation_level = 'New';
                    $escalation_status = 'General Enquiry';

                } elseif($webform->customer_type != 'SUPPLY ONLY'){

                    $escalation_level = 'Unassigned';
                    $escalation_status = 'Unassigned';

                    if($webform->is_special_opportunity){
                        $escalation_status = 'Special Opportunity';
                    }

                }  else {
                    $escalation_level = 'New';
                    $escalation_status = 'Supply Only';
                }

                $lead_esc_data = [
                    'escalation_level' => $escalation_level,
                    'escalation_status' => $escalation_status,
                    'color' => $leadEscalation->leadEscalationStatus("{$escalation_level} - {$escalation_status}"),
                    'lead_id' => $lead->id,
                    'is_active' => true,
                ];

                LeadEscalation::create($lead_esc_data);
                \DB::commit();

            }catch(\Exception $e){
                \Log::error($e->getMessage());
                \Log::error($e->getTraceAsString());

                \Log::info($e->getMessage());
                \Log::info($e->getTraceAsString());
                \DB::rollback();
            }
        }
        return 0;
    }

    public function uploadWebForm($webform_id){
        WebForm::where('id', $webform_id)->update(['is_uploaded' => 1]);
    }
}
