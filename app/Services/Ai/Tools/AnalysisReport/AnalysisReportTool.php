<?php

namespace App\Services\Ai\Tools\AnalysisReport;

use App\Models\AiOrdersAnalysis;
use App\Services\Ai\Exceptions\InvalidParametersException;
use App\Services\Ai\Tools\AbstractTool;
use Throwable;

final class AnalysisReportTool extends AbstractTool
{
    private const string DESCRIPTION = <<<'TXT'
        Stores the finished order analysis so a shop manager can read it later. Call it
        exactly once, as the last tool call of the analysis, after update_hot_flags.
        TXT;

    private const string NAME = 'store_order_analysis_report';

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
                    'report_text' => [
                        'type' => 'string',
                        'description' => 'Plain prose for a shop manager, 4 to 8 sentences, no markdown or '
                            . 'bullets: period and volume, preset versus custom split, strongest combinations '
                            . 'with figures, which presets you featured and why, and where your '
                            . 'recommendations point. Name toppings, never use codes.',
                    ],
                    'recommendations' => [
                        'type' => 'string',
                        'description' => 'A JSON-encoded string. It decodes to an object with exactly the '
                            . 'keys "health", "rich_taste" and "popularity_trend", each mapping pizza name to '
                            . 'an array of topping codes. All three must be present, 1 to 3 options each, '
                            . '2 to 6 codes per option. Example: '
                            . '{"health":{"Garden Protein Balance":["CHZ_1","MSHR_1","MT_1"]},'
                            . '"rich_taste":{"Four-Cheese Bacon Crown":["BOARDS","CHZ_1","CHZ_2","MT_2"]},'
                            . '"popularity_trend":{"Double Smoke Cheddar Rush":["CHZ_2","SSG_1","SSG_2"]}}',
                    ],
                    'hot_ids' => [
                        'type' => 'array',
                        'description' => 'The same preset ids you passed to update_hot_flags.',
                        'items' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                'required' => ['report_text', 'recommendations', 'hot_ids'],
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

        $reportText = $params['report_text'] ?? '';
        $recommendations = $params['recommendations'] ?? [];
        $pizzaPresetHotIds = $params['hot_ids'] ?? [];

        if (trim($reportText) === '') {
            $this->logError('Report can not be empty.', $params);

            throw new InvalidParametersException('Report can not be empty.');
        }

        $newEntry = null;
        try {
            $newEntry = AiOrdersAnalysis::create([
                'report' => $reportText,
                'presets_recommendations' => json_decode($recommendations, true, 512, JSON_THROW_ON_ERROR),
                'new_hot_ids' => $pizzaPresetHotIds,
            ]);
        } catch (Throwable $throwable) {
            $this->logError('Failed to save report.', $params, $throwable);
            report($throwable);
        }

        $result = ($newEntry !== null)
            ? 'Report saved.'
            : 'Failed to save report.';

        $this->logInfo('Result: ', [$result]);

        return $result;
    }
}
