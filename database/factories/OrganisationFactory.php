<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Address;
use App\Organisation;
use Faker\Generator as Faker;

$factory->define(Organisation::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'contact_number' => '2814697790',
        'org_code' => $faker->randomNumber(8),
        'address_id' => factory(App\Address::class)->create()->id,
        'user_id' => factory(App\User::class)->create()->id,
        'metadata' => ['manual_update' => false, 'address_search' => null],
        'notifications' => []
    ];
});
