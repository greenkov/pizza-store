<?php

namespace App\Services\Ai\Tools\LoadExistingPizzaPresets;

use App\Models\PizzaPreset;
use App\Services\Ai\Agents\TrackingMeta;
use App\Services\Ai\Tools\AbstractTool;

final class LoadExistingPizzaPresetsTool extends AbstractTool
{
    private const string DESCRIPTION = <<<'TXT'
        The presets the shop currently sells: "id" (the only value update_hot_flags accepts),
        "name", "topping_codes" (repeats meaningful), "hot" (1 if featured right now).
        Discontinued presets are not listed. Use it to know which ids are valid and which
        recipes already exist.
        TXT;

    private const string NAME = 'load_available_pizza_presets';

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
                        'description' => 'Pass an empty array for the whole menu - the normal case. '
                            . 'Pass ids only to re-check specific presets.',
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
     * @throws \JsonException
     */
    public function use(array $params, TrackingMeta $trackingMeta): string
    {
        $this->logInfo('Tool called...', $params);

        $ids = $params['ids'] ?? [];

        $result = PizzaPreset::query()
            ->when(count($ids) > 0, function ($query) use ($ids) {
                $query->whereIn('id', $ids);
            })
            ->get(['id', 'name', 'topping_codes', 'hot'])
            ->toArray();

        $this->logInfo('Result: ', $result);

        return json_encode($result, JSON_THROW_ON_ERROR);
    }
}
