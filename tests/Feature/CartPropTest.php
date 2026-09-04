<?php

use App\Models\PizzaPreset;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    seedToppingCatalog();
});

/**
 * @return array{items: array<int, array<string, mixed>>, total: float}
 */
function cartPropFrom(AssertableInertia $page): array
{
    return $page->toArray()['props']['cart'];
}

it('shares an empty cart when nothing has been added', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(function (AssertableInertia $page) {
            $cart = cartPropFrom($page);

            expect($cart['items'])->toBe([])
                ->and((float) $cart['total'])->toBe(0.0);
        });
});

it('shares the cart with resolved names and prices', function () {
    $preset = PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1', 'MT_2']]);

    $this->actingAs(User::factory()->create())
        ->post(route('card.store'), ['preset_id' => $preset->id, 'size' => 'md']);

    $expected = round(
        config('calories.md_base_price')
            + config('calories.toppings.CHZ_1.price')
            + config('calories.toppings.MT_2.price'),
        2
    );

    $this->get(route('dashboard'))
        ->assertInertia(function (AssertableInertia $page) use ($preset, $expected) {
            $cart = cartPropFrom($page);

            expect($cart['items'])->toHaveCount(1)
                ->and($cart['items'][0]['name'])->toBe($preset->name)
                ->and($cart['items'][0]['size'])->toBe('md')
                ->and($cart['items'][0]['toppings'])->toBe(['Mozzarella', 'Bacon'])
                ->and((float) $cart['items'][0]['price'])->toBe($expected)
                ->and((float) $cart['total'])->toBe($expected);
        });
});

it('scales the price by the size coefficient', function () {
    $preset = PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1']]);

    $this->actingAs(User::factory()->create())
        ->post(route('card.store'), ['preset_id' => $preset->id, 'size' => 'lg']);

    $expected = round(
        (config('calories.md_base_price') + config('calories.toppings.CHZ_1.price'))
            * config('calories.lg_coefficient'),
        2
    );

    $this->get(route('dashboard'))
        ->assertInertia(function (AssertableInertia $page) use ($expected) {
            expect((float) cartPropFrom($page)['items'][0]['price'])->toBe($expected);
        });
});

it('charges twice for a repeated topping', function () {
    $single = PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1']]);
    $double = PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1', 'CHZ_1']]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('card.store'), ['preset_id' => $single->id, 'size' => 'md']);
    $this->actingAs($user)
        ->post(route('card.store'), ['preset_id' => $double->id, 'size' => 'md']);

    $this->get(route('dashboard'))
        ->assertInertia(function (AssertableInertia $page) {
            $items = cartPropFrom($page)['items'];

            expect(round((float) $items[1]['price'] - (float) $items[0]['price'], 2))
                ->toBe(round(config('calories.toppings.CHZ_1.price'), 2))
                ->and($items[1]['toppings'])->toHaveCount(2);
        });
});

it('falls back to a placeholder when the preset was deleted', function () {
    $preset = PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1']]);

    $this->actingAs(User::factory()->create())
        ->post(route('card.store'), ['preset_id' => $preset->id, 'size' => 'md']);

    $preset->delete();

    $this->get(route('dashboard'))
        ->assertInertia(function (AssertableInertia $page) {
            expect(cartPropFrom($page)['items'][0]['name'])->toBe('Unavailable pizza');
        });
});
