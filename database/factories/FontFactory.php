<?php

use Faker\Generator as Faker;

$factory->define(App\Font::class, function (Faker $faker) {
    return [
        'variant_id' => factory(App\FontVariant::class)->create()->id,
        'section_id' => factory(App\Section::class)->create()->id,
        'bridge_id' => factory(App\Bridge::class)->create()->id,
        'order' => 1
    ];
});
