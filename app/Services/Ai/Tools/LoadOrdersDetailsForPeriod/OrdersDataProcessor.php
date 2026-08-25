<?php

namespace App\Services\Ai\Tools\LoadOrdersDetailsForPeriod;

use App\Models\OrderedPizza;
use App\Models\PizzaPreset;
use App\Models\Topping;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OrdersDataProcessor
{
    private const PERIOD_WEEK = 'week';

    private const COMBINATIONS_CAP = 25;

    /**
     * @var Collection|PizzaPreset[]
     */
    private Collection $pizzaPresets;

    /**
     * @var Collection|Topping[]
     */
    private Collection $toppings;

    /**
     * @var int
     */
    private int $baseMdCal;

    /**
     * @param Collection|OrderedPizza[] $orderedPizzas
     * @param string $period
     */
    public function __construct(private Collection $orderedPizzas, private string $period)
    {
        $pizzaPresetIds = $this->orderedPizzas->pluck('preset_id')->filter()->unique()->values()->toArray();
        $this->pizzaPresets = PizzaPreset::withTrashed()
            ->select(['id', 'name', 'topping_codes', 'hot', 'deleted_at'])
            ->whereIn('id', $pizzaPresetIds)
            ->get()
            ->keyBy('id');
        $this->toppings = Topping::select(['id', 'name', 'code', 'md_cal', 'md_price'])->get()->keyBy('code');

        $this->baseMdCal = config('calories.md_base_cal');
    }

    /**
     * @return array
     */
    public function buildPayload(): array
    {
        $period = $this->buildPeriodField();
        $totalAndDistribution = $this->buildTotalsAndDistributionFields();
        $combinations = $this->buildCombinationsField();
        $presetSales = $this->buildPresetSalesField();
        $toppingFrequency = $this->buildToppingFrequencyField();

        return array_merge(
            $period,
            $totalAndDistribution,
            $combinations,
            $presetSales,
            $toppingFrequency,
        );
    }

    /**
     * @return array[]
     */
    private function buildPeriodField(): array
    {
        $from = $this->period === self::PERIOD_WEEK
            ? Carbon::now()->subWeek()
            : Carbon::now();

        $to = Carbon::now();

        return [
            'period' => [
                'label' => $this->period,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function buildTotalsAndDistributionFields(): array
    {
        $resultTotals = [];
        $resultTotals['orders'] = $this->orderedPizzas->pluck('order_id')->unique()->count();
        $resultTotals['pizzas'] = $this->orderedPizzas->count();
        $resultTotals['preset_pizzas'] = $this->orderedPizzas->filter(fn (OrderedPizza $pizza) => $pizza->type === OrderedPizza::TYPE_PRESET)->count();
        $resultTotals['custom_pizzas'] = $resultTotals['pizzas'] - $resultTotals['preset_pizzas'];

        $resultSizeDistribution = $this->orderedPizzas->countBy('size')->all();
        foreach (PizzaPreset::$availableSizes as $size) {
            $resultSizeDistribution[$size] = $resultSizeDistribution[$size] ?? 0;
        }

        return [
            'totals' => $resultTotals,
            'size_distribution' => $resultSizeDistribution,
        ];
    }

    /**
     * @return array
     */
    private function buildPresetSalesField(): array
    {
        $groupedPresets = collect($this->orderedPizzas)
            ->whereNotNull('preset_id')
            ->groupBy('preset_id');

        $result = $groupedPresets->map(function ($itemsInGroup, $presetId) {
            $preset = $this->pizzaPresets->get($presetId);
            if ($preset === null) {
                return null;
            }
            $result = $preset->except(['deleted_at']);
            $result['is_available'] = $preset->deleted_at === null;
            $result['ordered_count'] = $itemsInGroup->count();

            return $result;
        })
            ->filter()
            ->sortBy('ordered_count', descending: true)
            ->values()
            ->toArray();

        return [
            'preset_sales' => $result,
        ];
    }

    /**
     * @return array
     */
    private function buildToppingFrequencyField(): array
    {
        $result = [];
        $this->orderedPizzas->each(function ($orderedPizza) use (&$result) {
            /** @var OrderedPizza $orderedPizza */
            foreach ($orderedPizza->topping_codes as $code) {
                if (! $this->toppings->has($code)) {
                    continue;
                }

                if (! array_key_exists($code, $result)) {
                    $result[$code] = $this->toppings->get($code)->except(['id']);
                    $result[$code]['in_ordered_pizzas'] = 1;
                } else {
                    $result[$code]['in_ordered_pizzas']++;
                }
            }
        });

        $result = collect($result)->sortBy('in_ordered_pizzas', descending: true)
            ->values()
            ->toArray();

        return [
            'topping_frequency' => $result,
        ];
    }

    /**
     * @return array
     */
    private function buildCombinationsField(): array
    {
        $orderedPizzasWithAdditionalGroupingField = $this->orderedPizzas->map(function ($orderedPizza) {
            $result = $orderedPizza->toArray();
            $toppingsList = $orderedPizza->topping_codes;
            sort($toppingsList);
            $result['toppingsString'] = implode(',', $toppingsList);

            return $result;
        });

        $orderedPizzaGroupedByToppings = $orderedPizzasWithAdditionalGroupingField->groupBy('toppingsString');
        $combinations = $orderedPizzaGroupedByToppings->map(function ($group, $toppingCodesKey) {
            $toppingCodes = explode(',', $toppingCodesKey);

            $result = [];
            $result['topping_codes'] = $toppingCodes;
            $result['pizzas'] = $group->count();
            $result['orders'] = $group->pluck('order_id')->unique()->count();
            $result['preset_id'] = $group->pluck('preset_id')->filter()->first();
            $result['as_preset'] = $group->reduce(function (?int $carry, array $item) {
                return $carry + ($item['type'] === OrderedPizza::TYPE_PRESET ? 1 : 0);
            }, 0);
            $result['as_custom'] = $group->reduce(function (?int $carry, array $item) {
                return $carry + ($item['type'] === OrderedPizza::TYPE_CUSTOM ? 1 : 0);
            }, 0);

            $result['pizza_md_cal'] = $this->calcCalories($toppingCodes);

            return $result;
        })->sortBy('pizzas', descending: true);

        $totalCombinations = $combinations->count();
        $result = $combinations->take(self::COMBINATIONS_CAP)->values()->toArray();

        return [
            'combinations' => $result,
            'combinations_truncated' => $totalCombinations > self::COMBINATIONS_CAP,
            'combinations_total' => $totalCombinations,
        ];
    }

    /**
     * @param array $toppingCodes
     *
     * @return int
     */
    private function calcCalories(array $toppingCodes): int
    {
        $result = $this->baseMdCal;
        foreach ($toppingCodes as $code) {
            $topping = $this->toppings->get($code);
            if ($topping === null) {
                continue;
            }

            $result += $topping->md_cal;
        }

        return $result;
    }
}
