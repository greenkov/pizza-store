<?php

use App\Models\PizzaPreset;
use App\Models\Topping;

test('it builds a preset from catalog topping codes', function () {
    $preset = PizzaPreset::factory()->create();

    expect($preset->name)->not->toBeEmpty()
        ->and($preset->topping_codes)->toBeArray()
        ->and($preset->topping_codes)->each->toBeIn(Topping::getAvailableToppingCodes());
});

test('it draws distinct codes by default, though repeats are valid in the domain', function () {
    $codes = PizzaPreset::factory()->create()->topping_codes;

    expect(array_unique($codes))->toBe($codes);
});

test('it accepts a repeated topping code when one is asked for', function () {
    $preset = PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1', 'CHZ_1', 'MT_2']]);

    expect($preset->fresh()->topping_codes)->toBe(['CHZ_1', 'CHZ_1', 'MT_2']);
});

test('it creates many presets without colliding on the unique topping_codes index', function () {
    PizzaPreset::factory()->count(60)->create();

    expect(PizzaPreset::count())->toBe(60)
        ->and(PizzaPreset::pluck('topping_codes')->map(fn ($c) => implode(',', $c))->unique())
        ->toHaveCount(60);
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
