<?php

use App\Models\PizzaPreset;
use App\Models\User;
use Inertia\Support\SessionKey;

it('adds a preset pizza to the session cart', function () {
    $preset = PizzaPreset::factory()->create();

    $this->actingAs(User::factory()->create())
        ->from(route('dashboard'))
        ->post(route('card.store'), [
            'preset_id' => $preset->id,
            'size' => 'md',
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasNoErrors();

    $cart = session('cart');

    expect($cart)->toHaveCount(1)
        ->and($cart[0]['preset_id'])->toBe($preset->id)
        ->and($cart[0]['size'])->toBe('md')
        ->and($cart[0]['topping_codes'])->toBe($preset->topping_codes)
        ->and($cart[0]['id'])->toBeString();
});

it('keeps earlier items when a second pizza is added', function () {
    $first = PizzaPreset::factory()->create();
    $second = PizzaPreset::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('card.store'), ['preset_id' => $first->id, 'size' => 'sm']);

    $this->actingAs($user)
        ->post(route('card.store'), ['preset_id' => $second->id, 'size' => 'lg'])
        ->assertSessionHasNoErrors();

    expect(session('cart'))->toHaveCount(2);
});

it('flashes a success toast', function () {
    $preset = PizzaPreset::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('card.store'), ['preset_id' => $preset->id, 'size' => 'md']);

    expect(session(SessionKey::FLASH_DATA)['toast'])->toBe([
        'type' => 'success',
        'message' => 'Item added to cart.',
    ]);
});

it('rejects an unknown preset without hitting the cart', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('card.store'), ['preset_id' => 999999, 'size' => 'md'])
        ->assertSessionHasErrors('preset_id');

    expect(session('cart'))->toBeNull();
});

it('rejects a payload with neither a preset nor toppings', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('card.store'), ['size' => 'md'])
        ->assertSessionHasErrors();

    expect(session('cart'))->toBeNull();
});
