<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\LeadEscalation;
use App\Organisation;
use App\Lead;
use App\Setting;
use Illuminate\Support\Carbon;
use Faker\Generator as Faker;

$factory->define(LeadEscalation::class, function (Faker $faker) {
    // $organisation_count = Organisation::inRandomOrder()->first();
    // $organisation_id = $faker->numberBetween(1, $organisation_count);
    $organisation = Organisation::inRandomOrder()->first();
    $countdown = Setting::where('name', 'Accept Or Decline - Pending')->first();
    $time_type = ucfirst($countdown->metadata['type']);
    $expiration_date = Date('Y-m-d H:i:s', strtotime('+'.$countdown->value." $time_type"));

    return [
        'escalation_level' => 'Accept Or Decline',
        'escalation_status' => 'Pending',
        'color' => 'blue',
        'is_active' => true,
        'valley_meters' => $faker->randomNumber(3),
        'gutter_edge_meters' => $faker->randomNumber(3),
        'organisation_id' => $organisation->id,
        'expiration_date' => $expiration_date,
    ];
});
