<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Address;
use App\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $address = Address::create([
            'address' => '15 Binney Road',
            'city' => 'Kings Park',
            'suburb' => 'Kings Park',
            'state' => 'NSW',
            'postcode' => '2148',
        ]);

        $admin = [
            'role_id' => 2, // for administrator
            'first_name' => 'Traleado',
            'last_name' => 'Admin',
            'email' => 'admin@traleado.com',
            'password' => Hash::make('loopingAdmin21'),
            'address_id' => $address->id
        ];

        $user = User::create($admin);
        $user->assignRole('administrator');

        $address = Address::create([
            'address' => '15 Binney Road',
            'city' => 'Kings Park',
            'suburb' => 'Kings Park',
            'state' => 'NSW',
            'postcode' => '2148',
        ]);

        $organisation = [
            'role_id' => 3, // for organisation
            'first_name' => 'Traleado',
            'last_name' => 'Organisation',
            'email' => 'organisation@traleado.com',
            'password' => Hash::make('password'),
            'address_id' => $address->id
        ];

        $user = User::create($organisation);
        $user->assignRole('organisation');
    }
}
