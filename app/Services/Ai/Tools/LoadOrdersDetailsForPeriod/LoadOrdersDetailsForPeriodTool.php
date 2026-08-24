<?php

namespace App\Services\Ai\Tools\LoadOrdersDetailsForPeriod;

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Services\Ai\Exceptions\InvalidParametersException;
use App\Services\Ai\Tools\AbstractTool;
use Carbon\Carbon;

final class LoadOrdersDetailsForPeriodTool extends AbstractTool
{
    public const PERIOD_DAY = 'day';

    public const PERIOD_WEEK = 'week';

    private const string DESCRIPTION = <<<'TXT'
        Returns every pizza ordered during the requested period, as a JSON object keyed by
        order id. Each pizza in an order has:
        "name" - what the pizza was called at the time of ordering;
        "size" - sm, md or lg;
        "type" - "preset" if ordered from the menu, "custom" if the customer built it;
        "topping_codes" - the toppings actually on that pizza, repeats are meaningful;
        "order_id" - the order it belongs to;
        "preset_id" - the preset it came from, null for custom pizzas.
        One entry means one pizza sold. Cancelled and unpaid orders are already excluded,
        so everything you receive is a real, paid sale.
        TXT;

    private const string NAME = 'load_orders_details_for_period';

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
                        'enum' => [self::PERIOD_DAY, self::PERIOD_WEEK],
                        'description' => 'Time window to analyse: "day" returns orders placed today, '
                            . '"week" returns orders placed over the last seven days including today. '
                            . 'Use the period the user asked for.',
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
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function use(array $params): string
    {
        $this->logInfo('Tool called...', $params);

        $period = $params['period'] ?? self::PERIOD_DAY;
        if (! in_array($period, [self::PERIOD_DAY, self::PERIOD_WEEK])) {
            $this->logError('Wrong period specified.', $params);

            throw new InvalidParametersException('Wrong period specified.');
        }

        $ordersForPeriodQuery = Order::select(['id', 'status'])->whereNotIn('status', [Order::STATUS_PENDING, Order::STATUS_CANCELED]);
        if ($period === self::PERIOD_DAY) {
            $ordersForPeriodQuery->whereDate('created_at', today());
        } elseif ($period === self::PERIOD_WEEK) {
            $ordersForPeriodQuery->whereDate('created_at', '>=', Carbon::now()->subWeek())
                ->whereDate('created_at', '<=', Carbon::now());
        }
        $orderIdsForPeriod = $ordersForPeriodQuery->get()->pluck('id')->toArray();

        $result = OrderedPizza::select(['name', 'size', 'type', 'topping_codes', 'order_id', 'preset_id'])
            ->whereIn('order_id', $orderIdsForPeriod)
            ->get()
            ->groupBy('order_id')
            ->toArray();

        $this->logInfo('Result: ', $result);

        return json_encode($result, JSON_THROW_ON_ERROR);
    }
}
