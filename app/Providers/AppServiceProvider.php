<?php

namespace App\Providers;

use App\User;
use App\Notification;
use Spatie\Activitylog\Models\Activity;
use App\Observers\UserObserver;
use App\Observers\NotificationObserver;
use App\Observers\ActivityObserver;
use App\LeadEscalation;
use App\Observers\LeadEscalationObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningUnitTests()) {
            Schema::defaultStringLength(191);
        }

        User::observe(UserObserver::class);
        Notification::observe(NotificationObserver::class);
        Activity::observe(ActivityObserver::class);
        LeadEscalation::observe(LeadEscalationObserver::class);

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing') && class_exists(DuskServiceProvider::class)) {
            $this->app->register(DuskServiceProvider::class);
        }
    }
}
