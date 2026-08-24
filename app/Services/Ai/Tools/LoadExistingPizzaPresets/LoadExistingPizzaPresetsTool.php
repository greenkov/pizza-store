<?php

namespace App\Services\Ai\Tools\LoadExistingPizzaPresets;

use App\Models\PizzaPreset;
use App\Services\Ai\Tools\AbstractTool;

final class LoadExistingPizzaPresetsTool extends AbstractTool
{
    private const string DESCRIPTION = <<<'TXT'
        Returns the pizza presets the shop currently sells, as a JSON array. Each entry has:
        "id" - the preset id, the only value accepted by update_hot_flags;
        "name" - the menu name of the preset;
        "topping_codes" - the toppings it is made of, repeats are meaningful;
        "hot" - 1 if the preset is featured as popular right now, 0 if not.
        Use it to learn which ids are valid before featuring presets, and to avoid
        recommending a combination the shop already sells.
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
                        'description' => 'Preset ids to return. Pass an empty array to get the whole menu, '
                            . 'which is what you normally want. Pass specific ids only when re-checking presets '
                            . 'you already know about.',
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
    public function use(array $params): string
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
