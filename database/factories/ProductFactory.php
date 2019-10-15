<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    $image = $faker->randomElement([
        "http://qiniu.wlight.top/1.png",
        "http://qiniu.wlight.top/2.png",
        "http://qiniu.wlight.top/3.png",
        "http://qiniu.wlight.top/4.png",
        "http://qiniu.wlight.top/5.png",
        "http://qiniu.wlight.top/6.png",
        "http://qiniu.wlight.top/7.png",
        "http://qiniu.wlight.top/8.png",
        "http://qiniu.wlight.top/9.png",
    ]);

    return [
        'title' => $faker->word,
        'description' => $faker->sentence,
        'image' => $image,
        'on_sale' => true,
        'rating' => $faker->numberBetween(0,5),
        'sold_count' => 0,
        'review_count' => 0,
        'price' => 0,
    ];
});
