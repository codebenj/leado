<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Setting;
use App\User;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\LeadEscalationLevel::class,
        \App\Console\Commands\LeadWebForm::class,
        \App\Console\Commands\StateLongToShortName::class,
        \App\Console\Commands\OrganizationManualNotifications::class,
        \App\Console\Commands\OrganisationOnHold::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //expiration
        try{
            $schedule->command('lead:escalation-level')
                ->everyMinute()
                ->withoutOverlapping();
        }catch(\Exception $ex){}

        //web form process
        try{
            $schedule->command('lead-web-form:process')
                ->everyFiveMinutes()
                ->withoutOverlapping();
        }catch(\Exception $ex){
            \Log::error($ex->getMessage());
        }

        //organisation onhold
        try{
            $schedule->command('organisation:onhold')
                ->everyFiveMinutes()
                ->withoutOverlapping();
        }
        catch(\Exception $ex){
            \Log::error($ex->getMessage());
            \Log::error($ex->getTraceAsString());
        }

        //organisation manual notification
        try{
            $setting = Setting::where('key', 'manual-notifications-organisation')->first();
            $day = $setting->metadata['day'];
            $time = explode(',', $setting->value);
            $time = explode(':', $time[1]);
            $hour = $time[0] ?? '08:00';
            $minute = $setting->metadata['minute'] ?? '0:00';
            $am_pm = $setting->metadata['am_pm'] ?? 'AM';
            $time = $hour . ':' . $minute;

            if($am_pm == 'PM'){
                $hour = ((int)$hour) + 12;
                $time = $hour . ':' . $minute;
            }

            $time = date("H:i", strtotime("$time"));

            //running manual notification base from user timezone(timezone update when user login)
            $time = date("H:i", strtotime("{$time}"));

            $users = User::select('metadata')->distinct('metadata')->get();
            $timezones = [];
            foreach($users as $user){
                $timezones[] = $user->metadata['timezone'] ?? 'Australia/Sydney';
            }
            $timezones = array_unique($timezones);

            //0 is not set, (0 and 7 is sunday in cron)
            if($day != 0){
                foreach($timezones as $timezone){
                    $schedule->command('notification:organisations', [$timezone])
                        ->timezone($timezone)
                        ->weeklyOn($day, $time);
                }
            }
        }
        catch(\Exception $ex){}

        if((config('app.env') == 'production')){
            $schedule->command('backup:clean')->daily()->at('01:00')->withoutOverlapping();
            $schedule->command('backup:run')->daily()->at('02:00')->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
