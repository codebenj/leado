<?php

use App\Permission;
use App\Status;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

		//disable foreign key check for this connection before running seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $statuses = file_get_contents(storage_path('status.json'));
        $statuses = json_decode($statuses, true);

        // truncate status table
        Status::truncate();

        \Log::error($statuses);

        // insert status
        foreach ($statuses as $key => $defaultStatus) {

            $status = Status::create([
                'name' => $defaultStatus['name']
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
