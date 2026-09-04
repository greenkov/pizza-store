<?php

use App\Models\Topping;

test('it builds a topping from the catalog', function () {
    $topping = Topping::factory()->create();

    expect(Topping::getAvailableToppingCodes())->toContain($topping->code)
        ->and($topping->name)->toBe(Topping::getNameByCode($topping->code))
        ->and($topping->md_cal)->toBe(config("calories.toppings.$topping->code.cal"))
        ->and($topping->md_price)->toBe(config("calories.toppings.$topping->code.price"));
});

test('it builds the topping requested by code', function () {
    $topping = Topping::factory()->code('MT_2')->create();

    expect($topping->code)->toBe('MT_2')
        ->and($topping->name)->toBe('Bacon')
        ->and($topping->md_cal)->toBe(config('calories.toppings.MT_2.cal'))
        ->and($topping->md_price)->toBe(config("calories.toppings.$topping->code.price"));
});

test('it creates every catalog topping without colliding on code', function () {
    $codes = Topping::getAvailableToppingCodes();

    foreach ($codes as $code) {
        Topping::factory()->code($code)->create();
    }

    expect(Topping::count())->toBe(count($codes));
});

test('it rejects a code that is not in the catalog', function () {
    Topping::factory()->code('NOPE_1')->create();
})->throws(Exception::class, 'Unknown Topping Code: NOPE_1');
