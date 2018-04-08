<?php

use Faker\Generator as Faker;

$factory->define(App\Color::class, function (Faker $faker) {
    return [
        'hex' => '255',
        'rgb' => '2550',
        'cmyk' => '2555',
        'bridge_id' => factory(App\Bridge::class)->create()->id,
        'section_id' => factory(App\Section::class)->create()->id,
        'order' => 1
    ];
});
