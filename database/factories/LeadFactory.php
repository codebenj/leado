<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Lead;
use Faker\Generator as Faker;

$factory->define(Lead::class, function (Faker $faker) {
    $house_type = $faker->randomElement(['Single Storey dwelling', 'Double Storey dwelling', 'Townhouses/Villas',
        'Commercial', 'Carport/Pergola/Shed', 'Other']);

    $commercial = ($house_type == 'Commercial') ? $faker->randomElement(['School', 'Hospital', 'Factory', 'Office Building', 'Other']) : '';

    $sale_string = $faker->randomFloat($nbMaxDecimals = 2, $min = 1, $max = 1000);

    $lead_type = $faker->randomElement(['SUPPLY & INSTALL']);

    $escalation_level = $faker->randomElement(['Accept Or Decline']);

    $escalation_status = $faker->randomElement(['Pending']);

    $roof_preference = $faker->randomElement(['Tile', 'Metal', 'Tile & Metal', 'Other', 'Metal Other', 'Metal Corrugated']);

    $source = $faker->randomElement(['Searched on the internet', 'Flyer - from a Store', 'Facebook', 'Radio',
        'Other', 'Television', 'Referred By Someone', 'Billboard', 'Instagram', 'Newspaper', 'Sign on a building',
        'Flyer - Other', 'Vehicle Sign']);

    $situation = $faker->randomElement(['Other', 'I am not in the trade - thisis my own project.', 'I am a tradesperson/builder - this is a once off project.',
        'I am not in the trade - this is my own project.', 'I am a tradesperson/builder - this could be ongoing for me/us.']);

    $received_via = $faker->randomElement(['Email', 'Phone', 'Web Form', 'Walk-In']);

    return [
        'customer_type' => $lead_type,
        'house_type' => $house_type,
        'roof_preference' => $roof_preference,
        'commercial' => $commercial,
        'situation' => $situation,
        'gutter_edge_meters' => $faker->randomNumber(3),
        'valley_meters' => $faker->randomNumber(3),
        'comments' => $faker->sentence,
        'source' => $source,
        'staff_comments' => $faker->sentence,
        'sale_string' => '$' . $sale_string,
        'sale' => $sale_string,
        'use_for' => 'Single Storey dwelling',
        // 'additional_information' => 'Enquirer Additional Information',
        'enquirer_message' => 'Thank you for your Leaf Stopper Enquiry. If you do not receive a call from an installer within 72 hours, please call us on 1300 334 333 or email us at office@leafstopper.com.au',
        'notify_enquirer' => true,
        'received_via' => $received_via,

        // lead escalation information
        // 'escalation_level' => $escalation_level,
        // 'escalation_status' => $escalation_status,
        // 'color' => 'blue',

        // organisation information
        // 'organisation_id' => factory(App\Organisation::class),

        //'customer_id' => factory(App\Customer::class),
        // customer information
        // 'email' => $faker->unique()->safeEmail,
        // 'contact_number' => $faker->e164PhoneNumber,
        // 'first_name' => $faker->firstName,
        // 'last_name' => $faker->lastName,

        // address information
        // 'address' => $faker->streetAddress,
        // 'city' => $faker->city,
        // 'state' => $faker->state,
        // 'postcode' => "6000",
        // 'country' => 'Australia',
    ];
});
