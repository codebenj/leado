<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Organisation;
use Carbon\Carbon;

class OrganisationOnHold extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organisation:onhold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update organisation availability date';

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
        $organisations = Organisation::where(['is_available' => 0])
            ->whereDate('available_when', '<', Carbon::now())->get();

        foreach($organisations as $organisation){
            try{
                $organisation->is_available = 1;
                $organisation->available_when = null;
                $organisation->save();
            }catch(\Exception $ex){
                \Log::error($organisation);
                \Log::error($ex->getMessage());
                \Log::error($ex->getTraceAsString());
            }
        }
    }
}
