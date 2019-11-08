<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use App\Subtitle;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

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

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'user_type' => 'legender',
        'email_verified_at' => now(),
        'password' => bcrypt('123456'), // password
        'remember_token' => Str::random(10),
    ];
});

$factory->define(Subtitle::class, function (Faker $faker) {
    $type = $faker->randomElement(array('SERIE','FILME'));
    return [
        'name' => $faker->text($maxNbChars = 20),
        'year' => $faker->year,
        'url' => 'https://www.google.com/',
        'episode' => $type==='SERIE'?'s0'.$faker->numberBetween($min = 1, $max = 9).'e'.$faker->numberBetween($min = 1, $max = 99):'',
        'type' => $type,
        'image' => '',
        'status' => 'APROVADA',
        'author' => $faker->numberBetween($min = 1, $max = 200),
        'category' => 1
    ];
});
