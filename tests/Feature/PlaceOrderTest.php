<?php

use App\Models\Order;
use App\Models\PizzaPreset;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->user = User::factory()->create();

    seedToppingCatalog();

    $preset = PizzaPreset::factory()->create();
    $this->actingAs($this->user)
        ->post(route('card.store'), ['preset_id' => $preset->id, 'size' => 'md']);
});

/**
 * @return array<string, string>
 */
function cardDetails(array $overrides = []): array
{
    return array_replace([
        'card_number' => '4111111111111111',
        'expires_at' => CarbonImmutable::now()->addYear()->format('m/y'),
        'cvv' => '123',
    ], $overrides);
}

it('passes the payment methods to the order page', function () {
    $this->actingAs($this->user)
        ->get(route('order.create'))
        ->assertInertia(function ($page) {
            $methods = $page->toArray()['props']['paymentMethods'];

            expect($methods)->toHaveCount(count(config('payment.methods')))
                ->and(array_column($methods, 'value'))
                ->toBe(array_keys(config('payment.methods')));
        });
});

it('renders the configured fee and limit as notes', function () {
    $this->actingAs($this->user)
        ->get(route('order.create'))
        ->assertInertia(function ($page) {
            $methods = collect($page->toArray()['props']['paymentMethods'])->keyBy('value');

            expect($methods['google_pay']['notes'])->toBe(['Fee: 1%', 'Limit: $100'])
                ->and($methods['pay_pal']['notes'])->toBe([]);
        });
});

it('accepts a card payment with complete details', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'visa',
            'payment_details' => cardDetails(),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

it('accepts a paypal payment with an email', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'pay_pal',
            'payment_details' => ['email' => 'buyer@example.com'],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

it('accepts a wallet payment that needs no details', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), ['payment_method' => 'google_pay'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

it('rejects an unknown payment method', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), ['payment_method' => 'bitcoin'])
        ->assertSessionHasErrors('payment_method');
});

it('requires a payment method', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [])
        ->assertSessionHasErrors('payment_method');
});

it('requires every field the picked method declares', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), ['payment_method' => 'visa'])
        ->assertSessionHasErrors([
            'payment_details.card_number',
            'payment_details.expires_at',
            'payment_details.cvv',
        ]);
});

it('ignores fields belonging to another method', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'pay_pal',
            'payment_details' => ['email' => 'buyer@example.com', 'cvv' => 'nope'],
        ])
        ->assertSessionHasNoErrors();
});

it('strips grouping characters from the card number', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'mastercard',
            'payment_details' => cardDetails(['card_number' => '4111 1111-1111 1111']),
        ])
        ->assertSessionHasNoErrors();
});

it('rejects a card number that is not 13 to 19 digits', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'visa',
            'payment_details' => cardDetails(['card_number' => '4111']),
        ])
        ->assertSessionHasErrors('payment_details.card_number');
});

it('rejects a malformed expiry date', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'visa',
            'payment_details' => cardDetails(['expires_at' => '2027-11']),
        ])
        ->assertSessionHasErrors('payment_details.expires_at');
});

it('rejects an expired card', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'visa',
            'payment_details' => cardDetails([
                'expires_at' => CarbonImmutable::now()->subMonth()->format('m/y'),
            ]),
        ])
        ->assertSessionHasErrors('payment_details.expires_at');
});

it('accepts a card expiring this month', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'visa',
            'payment_details' => cardDetails([
                'expires_at' => CarbonImmutable::now()->format('m/y'),
            ]),
        ])
        ->assertSessionHasNoErrors();
});

it('rejects a cvv that is not 3 or 4 digits', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'visa',
            'payment_details' => cardDetails(['cvv' => '12']),
        ])
        ->assertSessionHasErrors('payment_details.cvv');
});

it('rejects a malformed paypal email', function () {
    $this->actingAs($this->user)
        ->post(route('order.store'), [
            'payment_method' => 'pay_pal',
            'payment_details' => ['email' => 'not-an-email'],
        ])
        ->assertSessionHasErrors('payment_details.email');
});

it('does not leak validation rules to the browser', function () {
    $this->actingAs($this->user)
        ->get(route('order.create'))
        ->assertInertia(function ($page) {
            $methods = collect($page->toArray()['props']['paymentMethods'])->keyBy('value');

            expect($methods['visa']['fields'])->toHaveCount(3)
                ->and(array_column($methods['visa']['fields'], 'name'))
                ->toBe(['card_number', 'expires_at', 'cvv'])
                ->and($methods['visa']['fields'][0])->not->toHaveKey('rules')
                ->and($methods['google_pay']['fields'])->toBe([]);
        });
});

it('refuses to create an order when the cart is empty', function () {
    $this->actingAs($this->user)
        ->withSession(['cart' => []])
        ->post(route('order.store'), [
            'payment_method' => 'visa',
            'payment_details' => cardDetails(),
        ])
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0);
});
