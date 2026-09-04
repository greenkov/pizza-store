<?php

use App\Objects\CartPresenter;
use App\Services\Cart\Objects\CartItemDTO;

beforeEach(function () {
    seedToppingCatalog();
});

it('prices a medium pizza as base plus its toppings', function () {
    $item = CartItemDTO::buildFromCustomToppingsList('Custom', 'md', ['MSHR_1', 'CHZ_1']);

    $expected = config('calories.md_base_price')
        + config('calories.toppings.MSHR_1.price')
        + config('calories.toppings.CHZ_1.price');

    expect($item->calcPrice())->toBe(round($expected, 2));
});

it('charges a duplicate topping twice', function () {
    $single = CartItemDTO::buildFromCustomToppingsList('Custom', 'md', ['CHZ_1']);
    $double = CartItemDTO::buildFromCustomToppingsList('Custom', 'md', ['CHZ_1', 'CHZ_1']);

    expect($double->calcPrice())
        ->toBe(round($single->calcPrice() + config('calories.toppings.CHZ_1.price'), 2));
});

it('scales the whole pizza by the size coefficient', function (string $size, float $coefficient) {
    $item = CartItemDTO::buildFromCustomToppingsList('Custom', $size, ['SSG_1']);

    $medium = config('calories.md_base_price') + config('calories.toppings.SSG_1.price');

    expect($item->calcPrice())->toBe(round($medium * $coefficient, 2));
})->with([
    ['sm', 0.7],
    ['md', 1.0],
    ['lg', 1.3],
]);

it('prices a pizza with no toppings at the base price', function () {
    $item = CartItemDTO::buildFromCustomToppingsList('Custom', 'md', []);

    expect($item->calcPrice())->toBe(round((float) config('calories.md_base_price'), 2));
});

it('ignores an unknown topping code instead of failing', function () {
    $item = CartItemDTO::buildFromCustomToppingsList('Custom', 'md', ['NOT_A_TOPPING']);

    expect($item->calcPrice())->toBe(round((float) config('calories.md_base_price'), 2));
});

it('agrees with the price the cart shows', function () {
    $item = CartItemDTO::buildFromCustomToppingsList('Custom', 'lg', ['MT_1', 'CHZ_2']);

    $presented = CartPresenter::present([$item->toArray()]);

    expect((float) $presented['items'][0]['price'])->toBe($item->calcPrice());
});
