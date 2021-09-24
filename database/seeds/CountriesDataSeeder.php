<?php

use App\Country;
use Illuminate\Database\Seeder;

class CountriesDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Country::truncate();
        $countries = file_get_contents(storage_path('countries.json'));
        $countries = json_decode($countries, true);
        Country::insert($countries);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
