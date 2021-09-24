<?php

namespace App\Jobs;

use App\Address;
use App\Customer;
use App\Lead;
use App\LeadJob;
use App\LeadComment;
use App\LeadEscalation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateLeadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lead = $this->data;
        # Address
        $new_address = Address::create($lead['address']);
        $lead['customer']['address_id'] = $new_address->id;

        # Customer
        $new_customer = Customer::create($lead['customer']);
        $lead['customer_id'] = $new_customer->id;

        // # Lead
        $new_lead = Lead::create($lead);

        if(count($lead['lead_comments'])) {
            foreach($lead['lead_comments'] as $key => $lead_comment) {
                $lead['lead_comments'][$key]['lead_id'] = $new_lead->id;
            }

            LeadComment::insert($lead['lead_comments']);
        }

        if(count($lead['lead_job_comments'])) {
            foreach($lead['lead_job_comments'] as $key => $lead_comment) {
                $lead['lead_job_comments'][$key]['lead_id'] = $new_lead->id;
            }

            LeadJob::insert($lead['lead_job_comments']);
        }

        if(count($lead['lead_escalations'])) {
            foreach($lead['lead_escalations'] as $key => $lead_escalation) {
                $lead['lead_escalations'][$key]['lead_id'] = $new_lead->id;
            }

            LeadEscalation::insert($lead['lead_escalations']);
        }
    }
}
