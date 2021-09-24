<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\OrgLocator;
use Faker\Generator as Faker;

$factory->define(OrgLocator::class, function (Faker $faker) {
    return [
        'org_id' => $faker->randomNumber(5),
        'name' => $faker->company(),
        'street_address' => $faker->streetAddress(),
        'suburb' => $faker->state(),
        'postcode' => $faker->postcode(),
        'phone' => $faker->e164PhoneNumber(),
        'state' => $faker->state(),
        'last_year_sales' => $faker->randomFloat(2, 0, 100),
        'year_to_date_sales' => $faker->randomFloat(2, 0, 100),
        'keeps_stock' => '',
        'pricing_book' => $faker->randomFloat(2, 0, 100),
        'priority' => '',
    ];
});
