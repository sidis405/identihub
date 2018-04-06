<?php

use Faker\Generator as Faker;

$factory->define(App\FontVariant::class, function (Faker $faker) {
    return [
        'font_id' => factory(App\FontFamily::class)->create()->id,
        'variant' => $faker->word,
        'link' => $faker->word
    ];
});
