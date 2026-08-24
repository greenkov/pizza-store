<?php

namespace App\Services\Ai\Tools\LoadExistingToppings;

use App\Models\Topping;
use App\Services\Ai\Tools\AbstractTool;

final class LoadExistingToppingsTool extends AbstractTool
{
    private const string DESCRIPTION = <<<'TXT'
        Returns the shop's full topping catalogue as a JSON array. Each entry has:
        "code" - the identifier you must use in every topping list you produce;
        "name" - the human-readable name, use it in report text and in pizza names;
        "md_cal" - calories for a medium pizza, use it to judge whether a combination is light.
        Call this before any other tool: every topping code you output must come from this list.
        TXT;

    private const string NAME = 'load_available_toppings';

    /**
     * @return string[]
     */
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => self::NAME,
            'description' => self::DESCRIPTION,
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

        $result = Topping::all(['id', 'name', 'code', 'md_cal'])->toArray();

        $this->logInfo('Result: ', $result);

        return json_encode($result, JSON_THROW_ON_ERROR);
    }
}
