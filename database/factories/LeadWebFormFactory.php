<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\LeadWebForm;
use Faker\Generator as Faker;

$factory->define(LeadWebForm::class, function (Faker $faker) {
    $house_type = $faker->randomElement(['Single Storey dwelling', 'Double Storey dwelling', 'Townhouses/Villas', 'Commercial', 'Carport/Pergola/Shed']);
    $commercial = ($house_type == 'Commercial') ? $faker->randomElement(['School', 'Hospital', 'Office Building', 'Warehouse', 'Malls']) : '';
    $customer_type = $faker->randomElement(['Supply & Install', 'Supply Only']);

    return [
        'customer_type' => $customer_type,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'address' => $faker->streetAddress,
        'city' => $faker->city,
        'suburb' => $faker->citySuffix,
        'postcode' => $faker->postcode,
        'state' => $faker->state,
        'country' => $faker->country,
        'email' => $faker->unique()->safeEmail,
        'contact_number' => '2814697790',
        'house_type' => $house_type,
        'roof_preference' => $faker->randomElement(['Tile', 'Metal', 'Tile & Metal']),
        'source' => $faker->randomElement(['Facebook', 'Billboard', 'Flyer - From A Store', 'Flyer - In A Letter Box', 'Instragram', 'Newspaper', 'Television', 'Radio',
            'Referred By Someone', 'Searched on the internet', 'Sign on a building', 'Vehicle Sign']),
        'comments' => $faker->sentence,
        'gutter_edge_meters' => '',
        'valley_meters' => '',
        'commercial' => $commercial,
        'situation' => $faker->randomElement(['I am a homeowner', 'I am a builder', 'I am a tradesperson/builder - this is a once off project.',
            'I am a tradesperson/builder - this could be ongoing for me/us.', 'I am not in the trade - this is my own project.']),
    ];
});
