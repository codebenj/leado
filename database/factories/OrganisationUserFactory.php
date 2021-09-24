<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\OrganizationUser;
use Faker\Generator as Faker;

$factory->define(OrganizationUser::class, function (Faker $faker) {
    return [
        'user_id' => factory(App\User::class)->create()->id,
        'organisation_id' => factory(App\Organisation::class)->create()->id,
    ];
});
