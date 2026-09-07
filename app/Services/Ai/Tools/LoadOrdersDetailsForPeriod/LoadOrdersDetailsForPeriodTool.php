<?php

namespace App\Services\Ai\Tools\LoadOrdersDetailsForPeriod;

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Services\Ai\Agents\TrackingMeta;
use App\Services\Ai\Exceptions\InvalidParametersException;
use App\Services\Ai\Tools\AbstractTool;
use Carbon\Carbon;
use InvalidArgumentException;

final class LoadOrdersDetailsForPeriodTool extends AbstractTool
{
    public const PERIOD_DAY = 'day';

    public const PERIOD_WEEK = 'week';

    public const PERIOD_MONTH = 'month';

    public const ALL_PERIODS = [self::PERIOD_DAY, self::PERIOD_WEEK, self::PERIOD_MONTH];

    /**
     * @var array|string[]
     */
    public static array $countableStatuses = [
        Order::STATUS_PAID,
        Order::STATUS_DELIVERING,
        Order::STATUS_COMPLETED,
    ];

    private const string DESCRIPTION = <<<'TXT'
        Aggregated sales for the period. Everything is already counted - read the figures,
        do not re-count. Keys:
        "period" - {label, from, to};
        "totals" - {orders, pizzas, preset_pizzas, custom_pizzas};
        "size_distribution" - pizzas sold per size;
        "combinations" - distinct recipes sold, most sold first. Each has "topping_codes"
        (sorted, repeats meaningful), "pizzas", "orders", "as_preset"/"as_custom" (ordered
        from the menu vs built by hand; they sum to "pizzas"), "preset_id" (the preset the
        as_preset sales came from, null if none were) and "pizza_md_cal" (whole medium
        pizza, dough base included);
        "combinations_total" - distinct recipes sold in all; if it exceeds the entries
        listed, the rest were dropped and the listed "pizzas" no longer add up to "totals";
        "preset_sales" - presets that sold, most sold first: "id", "name", "topping_codes",
        "hot", "is_available", "ordered_count";
        "topping_frequency" - toppings by how many pizzas contained them, repeats counted.
        Cancelled and unpaid orders are already excluded.
        TXT;

    private const string NAME = 'load_orders_details_for_period';

    /**
     * @param string[] $allowedPeriods Periods this instance offers the model, a subset of self::ALL_PERIODS.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(private array $allowedPeriods = self::ALL_PERIODS)
    {
        $unknownPeriods = array_diff($this->allowedPeriods, self::ALL_PERIODS);
        if ($this->allowedPeriods === [] || $unknownPeriods !== []) {
            throw new InvalidArgumentException(
                'Allowed periods must be a non-empty subset of: ' . implode(', ', self::ALL_PERIODS)
            );
        }
    }

    /**
     * @return string[]
     */
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => self::NAME,
            'description' => self::DESCRIPTION,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'period' => [
                        'type' => 'string',
                        'enum' => array_values($this->allowedPeriods),
                        'description' => 'Time window ending today: "day" is today only, "week" is today plus the '
                            . '7 days before it, "month" is today plus the calendar month before it. The exact '
                            . 'dates come back in "period". Use the period the user asked for.',
                    ],
                ],
                'required' => ['period'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param array $params
     * @param TrackingMeta $trackingMeta
     *
     * @return string
     *
     * @throws InvalidParametersException
     * @throws \JsonException
     */
    public function use(array $params, TrackingMeta $trackingMeta): string
    {
        $this->logInfo('Tool called...', $params);

        $period = $params['period'] ?? null;
        if (!in_array($period, $this->allowedPeriods, true)) {
            $this->logError('Wrong period specified.', $params);

            throw new InvalidParametersException(
                'Wrong period specified. Allowed values: ' . implode(', ', $this->allowedPeriods)
            );
        }

        $ordersForPeriodQuery = Order::select(['id', 'status'])->whereIn('status', self::$countableStatuses);
        if ($period === self::PERIOD_DAY) {
            $ordersForPeriodQuery->whereDate('created_at', today());
        } elseif ($period === self::PERIOD_WEEK) {
            $ordersForPeriodQuery->whereDate('created_at', '>=', Carbon::now()->subWeek())
                ->whereDate('created_at', '<=', Carbon::now());
        } elseif ($period === self::PERIOD_MONTH) {
            $ordersForPeriodQuery->whereDate('created_at', '>=', Carbon::now()->subMonth())
                ->whereDate('created_at', '<=', Carbon::now());
        }
        $orderIdsForPeriod = $ordersForPeriodQuery->get()->pluck('id')->toArray();

        $orderedPizzas = OrderedPizza::select(['name', 'size', 'type', 'topping_codes', 'order_id', 'preset_id'])
            ->whereIn('order_id', $orderIdsForPeriod)
            ->get();

        $orderDataProcessor = new OrdersDataProcessor($orderedPizzas, $period);

        $result = $orderDataProcessor->buildPayload();

        $this->logInfo('Result: ', $result);

        return json_encode($result, JSON_THROW_ON_ERROR);
    }
}
