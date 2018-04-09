<?php

use Faker\Generator as Faker;

$factory->define(App\Icon::class, function (Faker $faker) {
    return [
        'filename' => $faker->word,
        'width_ratio' => 0.5,
        'section_id' => factory(App\Section::class)->create()->id,
        'bridge_id' => factory(App\Bridge::class)->create()->id,
        'order' => 1
    ];
});
