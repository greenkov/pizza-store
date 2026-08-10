<?php

use App\Models\PizzaPreset;
use App\Models\Topping;

test('it builds a preset from catalog topping codes', function () {
    $preset = PizzaPreset::factory()->create();

    expect($preset->name)->not->toBeEmpty()
        ->and($preset->topping_codes)->toBeArray()
        ->and($preset->topping_codes)->each->toBeIn(Topping::getAvailableToppingCodes());
});

test('it never repeats a topping code within one preset', function () {
    $codes = PizzaPreset::factory()->create()->topping_codes;

    expect(array_unique($codes))->toBe($codes);
});

test('it reads topping codes back as an array', function () {
    PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1', 'MT_2']]);

    expect(PizzaPreset::sole()->topping_codes)->toBe(['CHZ_1', 'MT_2']);
});

test('a deleted preset is hidden from the menu but still readable', function () {
    $preset = PizzaPreset::factory()->create();

    $preset->delete();

    expect(PizzaPreset::count())->toBe(0)
        ->and(PizzaPreset::withTrashed()->find($preset->id)->topping_codes)
        ->toBe($preset->topping_codes);
});
