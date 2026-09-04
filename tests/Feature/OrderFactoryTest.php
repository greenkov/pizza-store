<?php

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Models\PizzaPreset;
use App\Models\Topping;

test('it builds an order that the analyser counts', function () {
    $order = Order::factory()->create();

    expect($order->user_id)->not->toBeNull()
        ->and($order->status)->toBe(Order::STATUS_PAID)
        ->and(Order::$availableStatuses)->toContain($order->status);
});

test('it builds a custom pizza line with its own order', function () {
    $pizza = OrderedPizza::factory()->create();

    expect($pizza->order_id)->not->toBeNull()
        ->and($pizza->type)->toBe(OrderedPizza::TYPE_CUSTOM)
        ->and($pizza->preset_id)->toBeNull()
        ->and($pizza->size)->toBeIn(PizzaPreset::$availableSizes)
        ->and($pizza->topping_codes)->each->toBeIn(Topping::getAvailableToppingCodes());
});

test('it attaches many lines to one order', function () {
    $order = Order::factory()->create();

    OrderedPizza::factory()->count(3)->create(['order_id' => $order->id]);

    expect(OrderedPizza::where('order_id', $order->id)->count())->toBe(3)
        ->and(Order::count())->toBe(1);
});

test('forPreset keeps type, preset_id, name and codes consistent', function () {
    $preset = PizzaPreset::factory()->create();

    $pizza = OrderedPizza::factory()->forPreset($preset)->create();

    expect($pizza->type)->toBe(OrderedPizza::TYPE_PRESET)
        ->and($pizza->preset_id)->toBe($preset->id)
        ->and($pizza->name)->toBe($preset->name)
        ->and($pizza->topping_codes)->toBe($preset->topping_codes);
});

test('it stores repeated topping codes without collapsing them', function () {
    $pizza = OrderedPizza::factory()->create(['topping_codes' => ['CHZ_1', 'CHZ_1', 'MT_2']]);

    expect($pizza->fresh()->topping_codes)->toBe(['CHZ_1', 'CHZ_1', 'MT_2']);
});

test('it sorts topping codes into canonical order on write', function () {
    $pizza = OrderedPizza::factory()->create(['topping_codes' => ['MT_2', 'CHZ_1']]);

    expect($pizza->fresh()->topping_codes)->toBe(['CHZ_1', 'MT_2']);
});
