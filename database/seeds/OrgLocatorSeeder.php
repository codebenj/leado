<?php

use Illuminate\Database\Seeder;

class OrgLocatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\OrgLocator::class, 5)->create();
    }
}
