<?php

namespace App\Services\Ai\Tools\LoadExistingToppings;

use App\Models\Topping;
use App\Services\Ai\Agents\TrackingMeta;
use App\Services\Ai\Tools\AbstractTool;

final class LoadExistingToppingsTool extends AbstractTool
{
    private const string DESCRIPTION = <<<'TXT'
        The shop's full topping catalogue: "code" (use these in every topping list you
        output), "name" (for report text and pizza names), "md_cal" (calories this topping
        adds to a medium pizza). Call it first - every code you output must come from here.
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
     * @param TrackingMeta $trackingMeta
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function use(array $params, TrackingMeta $trackingMeta): string
    {
        $this->logInfo('Tool called...', $params);

        $result = Topping::all(['id', 'name', 'code', 'md_cal'])->toArray();

        $this->logInfo('Result: ', $result);

        return json_encode($result, JSON_THROW_ON_ERROR);
    }
}
