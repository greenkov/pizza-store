<?php

namespace App\Services\Ai\Tools\UpdateHotFlags;

use App\Models\PizzaPreset;
use App\Services\Ai\Exceptions\InvalidParametersException;
use App\Services\Ai\Tools\AbstractTool;
use DB;

final class UpdateHotFlagsTool extends AbstractTool
{
    private const string DESCRIPTION = <<<'TXT'
        Replaces the shop's featured ("hot") presets with the given set. This overwrites the
        whole selection: every preset listed in "ids" becomes featured, and every preset not
        listed stops being featured. Always pass the complete final list, never just the
        additions. Call it exactly once per analysis, and only with ids returned by
        load_available_pizza_presets.
        TXT;

    private const string NAME = 'update_hot_flags';

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
                    'ids' => [
                        'type' => 'array',
                        'description' => 'The complete list of preset ids that should be featured after this '
                            . 'call. Any preset omitted here loses its featured status. Use 3 to 5 ids, ranked '
                            . 'by how many pizzas were sold from each preset in the period. Must be existing '
                            . 'preset ids.',
                        'items' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                'required' => ['ids'],
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
     * @throws InvalidParametersException
     * @throws \JsonException
     */
    public function use(array $params): string
    {
        $this->logInfo('Tool called...', $params);

        $pizzaPresetIds = $params['ids'] ?? [];
        if (count($pizzaPresetIds) === 0) {
            $this->logError('Empty ids list specified.', $params);

            throw new InvalidParametersException('Empty ids list specified.');
        }

        $existingPizzaIdsCount = PizzaPreset::whereIn('id', $pizzaPresetIds)->count();
        if ($existingPizzaIdsCount !== count($pizzaPresetIds)) {
            $result = 'Some pizza presets could not be found by specified ids.';
            $this->logInfo('Result: ', [$result]);

            return $result;
        }

        DB::transaction(function () use ($pizzaPresetIds) {
            PizzaPreset::query()->whereNotIn('id', $pizzaPresetIds)->where('hot', true)
                ->update(['hot' => false]);

            PizzaPreset::query()->whereIn('id', $pizzaPresetIds)->where('hot', false)
                ->update(['hot' => true]);
        });

        $result = 'Hot status updated.';

        $this->logInfo('Result: ', [$result]);

        return $result;
    }
}
