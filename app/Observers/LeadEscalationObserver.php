<?php

namespace App\Observers;

use App\LeadEscalation;
use App\Events\LeadEscalationCreatedEvent;

class LeadEscalationObserver
{
    /**
     * Handle the lead escalation "created" event.
     *
     * @param  \App\LeadEscalation  $leadEscalation
     * @return void
     */
    public function created(LeadEscalation $leadEscalation)
    {
        event(new LeadEscalationCreatedEvent($leadEscalation));
    }

    /**
     * Handle the lead escalation "updated" event.
     *
     * @param  \App\LeadEscalation  $leadEscalation
     * @return void
     */
    public function updated(LeadEscalation $leadEscalation)
    {
        //
    }

    /**
     * Handle the lead escalation "deleted" event.
     *
     * @param  \App\LeadEscalation  $leadEscalation
     * @return void
     */
    public function deleted(LeadEscalation $leadEscalation)
    {
        //
    }

    /**
     * Handle the lead escalation "restored" event.
     *
     * @param  \App\LeadEscalation  $leadEscalation
     * @return void
     */
    public function restored(LeadEscalation $leadEscalation)
    {
        //
    }

    /**
     * Handle the lead escalation "force deleted" event.
     *
     * @param  \App\LeadEscalation  $leadEscalation
     * @return void
     */
    public function forceDeleted(LeadEscalation $leadEscalation)
    {
        //
    }
}
