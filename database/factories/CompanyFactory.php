<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Company::class, function (Faker $faker) {
    return [
        'fair_id' => $faker->randomDigit,
        'company_name' => 'Gold',
        'company_stand_type' => $faker->name,
        'company_email' => $faker->unique()->companyEmail,
        'company_post_code' => $faker->postcode,
        'company_country' => $faker->country,
        'company_logo'    => 'c3a241ae6d1e03513dfed6f5061f4a4b.png'
    ];
});
