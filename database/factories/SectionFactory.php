<?php

use Faker\Generator as Faker;

$factory->define(App\Section::class, function (Faker $faker) {
    return [
        'section_type_id' => 1,
        'bridge_id' => factory(App\Bridge::class)->create()->id,
        'title' => $faker->word,
        'description' => $faker->word,
        'order' => 1,
    ];
});
