<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Address;
use App\Country;
use Faker\Generator as Faker;

$factory->define(Address::class, function (Faker $faker) {
    return [
        'address' => $faker->streetAddress,
        'city' => $faker->city,
        'suburb' => $faker->citySuffix,
        'postcode' => $faker->postcode,
        'state' => $faker->state,
        'country_id' => 14,
    ];
});
