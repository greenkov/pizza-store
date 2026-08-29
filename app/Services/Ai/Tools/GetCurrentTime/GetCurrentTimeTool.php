<?php

namespace App\Services\Ai\Tools\GetCurrentTime;

use App\Services\Ai\Agents\TrackingMeta;
use App\Services\Ai\Tools\AbstractTool;
use Carbon\Carbon;

final class GetCurrentTimeTool extends AbstractTool
{
    private const string DESCRIPTION = <<<'TXT'
        Returns the shop server's current date and time as an ISO-8601 string in UTC.
        Use it only when the report needs to state today's date. Order data is already
        filtered by period, so you do not need this to analyse orders.
        TXT;

    private const string NAME = 'get_current_time';

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
     */
    public function use(array $params, TrackingMeta $trackingMeta): string
    {
        $this->logInfo('Tool called...', $params);

        $result = Carbon::now()->toIso8601String();

        $this->logInfo('Result: ', [$result]);

        return $result;
    }
}
