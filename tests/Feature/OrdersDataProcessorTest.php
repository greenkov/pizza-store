<?php

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Models\PizzaPreset;
use App\Services\Ai\Tools\LoadOrdersDetailsForPeriod\OrdersDataProcessor;

beforeEach(function () {
    seedToppingCatalog();
});

/**
 * Run every ordered pizza in the database through the processor, the way the tool does.
 *
 * @return array<string, mixed>
 */
function analyseAllSales(string $period = 'week'): array
{
    return (new OrdersDataProcessor(OrderedPizza::all(), $period))->buildPayload();
}

/**
 * A spread of sales wide enough for the sum invariants to mean something: several
 * presets sold different numbers of times, two hand-built lines reproducing a preset
 * recipe, and custom lines of their own, across four orders.
 */
function seedMixedSales(): void
{
    $presets = PizzaPreset::factory()->count(5)->create();
    $orders = Order::factory()->count(4)->create(['status' => Order::STATUS_PAID]);

    foreach ($presets as $index => $preset) {
        OrderedPizza::factory()
            ->count($index + 1)
            ->forPreset($preset)
            ->create(['order_id' => $orders[$index % $orders->count()]->id]);
    }

    OrderedPizza::factory()->count(2)->create([
        'order_id' => $orders[0]->id,
        'topping_codes' => $presets[0]->topping_codes,
    ]);

    OrderedPizza::factory()->count(3)->create(['order_id' => $orders[3]->id]);
}

test('as_preset and as_custom sum to pizzas in every combination', function () {
    seedMixedSales();

    $payload = analyseAllSales();

    expect($payload['combinations'])->not->toBeEmpty();

    foreach ($payload['combinations'] as $combination) {
        expect($combination['as_preset'] + $combination['as_custom'])
            ->toBe($combination['pizzas'], 'combination ' . implode(',', $combination['topping_codes']));
    }
});

test('it splits total pizzas into the preset and custom lines actually sold', function () {
    seedMixedSales();

    $totals = analyseAllSales()['totals'];

    expect($totals['preset_pizzas'])->toBe(OrderedPizza::where('type', OrderedPizza::TYPE_PRESET)->count())
        ->and($totals['custom_pizzas'])->toBe(OrderedPizza::where('type', OrderedPizza::TYPE_CUSTOM)->count())
        ->and($totals['preset_pizzas'] + $totals['custom_pizzas'])->toBe($totals['pizzas'])
        ->and($totals['pizzas'])->toBe(OrderedPizza::count());
});

test('combination pizza counts sum to total pizzas when the list is not truncated', function () {
    seedMixedSales();

    $payload = analyseAllSales();

    expect($payload['combinations_truncated'])->toBeFalse()
        ->and($payload['combinations_total'])->toBe(count($payload['combinations']))
        ->and(array_sum(array_column($payload['combinations'], 'pizzas')))->toBe($payload['totals']['pizzas']);
});

test('size distribution sums to total pizzas and lists every size', function () {
    seedMixedSales();

    $payload = analyseAllSales();

    expect(array_keys($payload['size_distribution']))->toEqualCanonicalizing(PizzaPreset::$availableSizes)
        ->and(array_sum($payload['size_distribution']))->toBe($payload['totals']['pizzas']);
});

test('it counts orders that sold at least one pizza, not orders overall', function () {
    seedMixedSales();
    Order::factory()->create(['status' => Order::STATUS_PAID]);

    $payload = analyseAllSales();

    expect($payload['totals']['orders'])->toBe(4)
        ->and(Order::count())->toBe(5);
});

test('it groups a preset line and an identical hand-built line into one combination', function () {
    $preset = PizzaPreset::factory()->create();
    $order = Order::factory()->create(['status' => Order::STATUS_PAID]);

    OrderedPizza::factory()->forPreset($preset)->create(['order_id' => $order->id]);
    OrderedPizza::factory()->create([
        'order_id' => $order->id,
        'topping_codes' => array_reverse($preset->topping_codes),
    ]);

    $payload = analyseAllSales();

    expect($payload['combinations'])->toHaveCount(1);

    $combination = $payload['combinations'][0];

    expect($combination['topping_codes'])->toBe($preset->topping_codes)
        ->and($combination['pizzas'])->toBe(2)
        ->and($combination['orders'])->toBe(1)
        ->and($combination['as_preset'])->toBe(1)
        ->and($combination['as_custom'])->toBe(1)
        ->and($combination['preset_id'])->toBe($preset->id);
});

test('it leaves preset_id null for a combination nothing on the menu matches', function () {
    OrderedPizza::factory()->create(['topping_codes' => ['CHZ_1', 'MT_2']]);

    $combination = analyseAllSales()['combinations'][0];

    expect($combination['preset_id'])->toBeNull()
        ->and($combination['as_preset'])->toBe(0)
        ->and($combination['as_custom'])->toBe(1);
});

test('it counts a repeated topping once per occurrence', function () {
    OrderedPizza::factory()->create(['topping_codes' => ['CHZ_1', 'CHZ_1', 'MT_2']]);

    $payload = analyseAllSales();

    $frequency = collect($payload['topping_frequency'])->keyBy('code');

    expect($frequency->get('CHZ_1')['in_ordered_pizzas'])->toBe(2)
        ->and($frequency->get('MT_2')['in_ordered_pizzas'])->toBe(1)
        ->and($payload['combinations'][0]['topping_codes'])->toBe(['CHZ_1', 'CHZ_1', 'MT_2']);
});

test('it prices calories per occurrence on top of the dough base', function () {
    OrderedPizza::factory()->create(['topping_codes' => ['CHZ_1', 'CHZ_1', 'MT_2']]);

    $expected = config('calories.md_base_cal')
        + (2 * config('calories.toppings.CHZ_1.cal'))
        + config('calories.toppings.MT_2.cal');

    expect(analyseAllSales()['combinations'][0]['pizza_md_cal'])->toBe($expected);
});

test('it truncates the combination list and still reports the true total', function () {
    $presets = PizzaPreset::factory()->count(30)->create();
    $order = Order::factory()->create(['status' => Order::STATUS_PAID]);

    foreach ($presets as $preset) {
        OrderedPizza::factory()->forPreset($preset)->create(['order_id' => $order->id]);
    }

    $payload = analyseAllSales();

    expect($payload['combinations'])->toHaveCount(25)
        ->and($payload['combinations_total'])->toBe(30)
        ->and($payload['combinations_truncated'])->toBeTrue()
        ->and($payload['totals']['pizzas'])->toBe(30);
});

test('it orders combinations by pizzas sold, most sold first', function () {
    $order = Order::factory()->create(['status' => Order::STATUS_PAID]);

    OrderedPizza::factory()->count(3)->create(['order_id' => $order->id, 'topping_codes' => ['CHZ_1']]);
    OrderedPizza::factory()->create(['order_id' => $order->id, 'topping_codes' => ['MT_2']]);
    OrderedPizza::factory()->count(2)->create(['order_id' => $order->id, 'topping_codes' => ['SSG_1']]);

    $counts = array_column(analyseAllSales()['combinations'], 'pizzas');

    expect($counts)->toBe([3, 2, 1]);
});

test('it still reports a preset that was removed from the menu after it sold', function () {
    $preset = PizzaPreset::factory()->create();
    OrderedPizza::factory()->count(2)->forPreset($preset)->create();

    $preset->delete();

    $presetSales = analyseAllSales()['preset_sales'];

    expect($presetSales)->toHaveCount(1)
        ->and($presetSales[0]['id'])->toBe($preset->id)
        ->and($presetSales[0]['ordered_count'])->toBe(2)
        ->and($presetSales[0]['is_available'])->toBeFalse()
        ->and($presetSales[0])->not->toHaveKey('deleted_at');
});

test('it reports the window the period names', function () {
    expect(analyseAllSales('week')['period'])->toMatchArray([
        'label' => 'week',
        'from' => now()->subWeek()->toDateString(),
        'to' => now()->toDateString(),
    ]);

    expect(analyseAllSales('month')['period'])->toMatchArray([
        'label' => 'month',
        'from' => now()->subMonth()->toDateString(),
        'to' => now()->toDateString(),
    ]);

    expect(analyseAllSales('day')['period'])->toMatchArray([
        'label' => 'day',
        'from' => now()->toDateString(),
        'to' => now()->toDateString(),
    ]);
});

test('it reports empty structures rather than failing when nothing sold', function () {
    $payload = analyseAllSales();

    expect($payload['totals'])->toBe([
        'orders' => 0,
        'pizzas' => 0,
        'preset_pizzas' => 0,
        'custom_pizzas' => 0,
    ])
        ->and($payload['combinations'])->toBe([])
        ->and($payload['combinations_total'])->toBe(0)
        ->and($payload['combinations_truncated'])->toBeFalse()
        ->and($payload['preset_sales'])->toBe([])
        ->and($payload['topping_frequency'])->toBe([])
        ->and(array_sum($payload['size_distribution']))->toBe(0);
});
