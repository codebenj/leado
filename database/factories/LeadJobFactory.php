<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Lead;
use App\LeadJob;
use Faker\Generator as Faker;

$factory->define(LeadJob::class, function (Faker $faker) {
    $lead_count = Lead::all()->count();
    $lead_id = $faker->numberBetween(1, $lead_count);

    return [
        'lead_id' => $lead_id,
        'meters_gutter_edge' => $faker->randomNumber(3),
        'meters_valley' => $faker->randomNumber(3),
        'comments' => $faker->sentence,
        'organisation_id' => factory(App\Organisation::class),
        //'sale' => $faker->randomFloat($nbMaxDecimals = 2, $min = 1, $max = 1000),
    ];
});
