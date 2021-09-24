<?php

namespace App\Observers;

//use App\Activity;
use Spatie\Activitylog\Models\Activity;
use App\Events\ActivityCreatedEvent;

class ActivityObserver
{
    /**
     * Handle the activity "created" event.
     *
     * @param Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function created(Activity $activity)
    {
        event(new ActivityCreatedEvent($activity));
    }

    /**
     * Handle the activity "updated" event.
     *
     * @param  Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function updated(Activity $activity)
    {
        //
    }

    /**
     * Handle the activity "deleted" event.
     *
     * @param  Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function deleted(Activity $activity)
    {
        //
    }

    /**
     * Handle the activity "restored" event.
     *
     * @param  Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function restored(Activity $activity)
    {
        //
    }

    /**
     * Handle the activity "force deleted" event.
     *
     * @param  \App\Activity  $activity
     * @return void
     */
    public function forceDeleted(Activity $activity)
    {
        //
    }
}
