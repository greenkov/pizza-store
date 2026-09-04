<?php

use App\Models\Topping;
use Database\Seeders\ToppingSeeder;

test('it seeds the whole catalog with consistent names and calories', function () {
    $this->seed(ToppingSeeder::class);

    $codes = Topping::getAvailableToppingCodes();

    expect(Topping::count())->toBe(count($codes));

    foreach (Topping::all() as $topping) {
        expect($topping->name)->toBe(Topping::getNameByCode($topping->code))
            ->and($topping->md_cal)->toBe(config("calories.toppings.$topping->code.cal"));
    }
});
