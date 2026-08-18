<?php

use App\Models\PizzaPreset;
use App\Models\Topping;
use App\Models\User;

beforeEach(function () {
    foreach (Topping::getAvailableToppingCodes() as $code) {
        Topping::factory()->code($code)->create();
    }

    $this->user = User::factory()->create();
    $this->preset = PizzaPreset::factory()->create(['topping_codes' => ['CHZ_1']]);

    $this->actingAs($this->user)
        ->post(route('card.store'), ['preset_id' => $this->preset->id, 'size' => 'md']);

    $this->itemId = session('cart')[0]['id'];
});

it('starts a freshly added item at quantity one', function () {
    expect(session('cart')[0]['quantity'])->toBe(1);
});

it('sets a new quantity', function () {
    $this->actingAs($this->user)
        ->patch(route('card.update', $this->itemId), ['quantity' => 4])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(session('cart')[0]['quantity'])->toBe(4);
});

it('multiplies the line price by the quantity', function () {
    $unitPrice = round(
        config('calories.md_base_price') + config('calories.toppings.CHZ_1.price'),
        2
    );

    $this->actingAs($this->user)
        ->patch(route('card.update', $this->itemId), ['quantity' => 3]);

    $this->get(route('dashboard'))
        ->assertInertia(function ($page) use ($unitPrice) {
            $cart = $page->toArray()['props']['cart'];

            expect((float) $cart['items'][0]['price'])->toBe(round($unitPrice * 3, 2))
                ->and($cart['items'][0]['quantity'])->toBe(3)
                ->and((float) $cart['total'])->toBe(round($unitPrice * 3, 2));
        });
});

it('rejects a quantity below one', function () {
    $this->actingAs($this->user)
        ->patch(route('card.update', $this->itemId), ['quantity' => 0])
        ->assertSessionHasErrors('quantity');

    expect(session('cart')[0]['quantity'])->toBe(1);
});

it('rejects a quantity above the cap', function () {
    $this->actingAs($this->user)
        ->patch(route('card.update', $this->itemId), ['quantity' => 100])
        ->assertSessionHasErrors('quantity');

    expect(session('cart')[0]['quantity'])->toBe(1);
});

it('leaves the cart untouched for an unknown item id', function () {
    $this->actingAs($this->user)
        ->patch(route('card.update', 'not-a-real-id'), ['quantity' => 5])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(session('cart'))->toHaveCount(1)
        ->and(session('cart')[0]['quantity'])->toBe(1);
});
