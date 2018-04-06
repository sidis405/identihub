<?php

use Faker\Generator as Faker;

$factory->define(App\FontFamily::class, function (Faker $faker) {
    return [
        'family' => $faker->word,
        'category' => $faker->word,
        'version' => $faker->word
    ];
});
