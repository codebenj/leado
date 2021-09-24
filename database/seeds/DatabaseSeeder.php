<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
	public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(CountriesDataSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(OrganizationSeeder::class);
        $this->call(StatusSeeder::class);
    }
}
