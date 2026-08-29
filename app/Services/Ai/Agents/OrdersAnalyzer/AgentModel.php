<?php

namespace App\Services\Ai\Agents\OrdersAnalyzer;

use App\Services\Ai\Agents\Agent;
use App\Services\Ai\Agents\ResponseWrappers\ResponseWrapper;
use App\Services\Ai\Agents\ResponseWrappers\TokensLoggerWrapper;
use App\Services\Ai\Agents\TrackingMeta;
use App\Services\Ai\Exceptions\TooManyTurnsException;
use App\Services\Ai\Tools\AnalysisReport\AnalysisReportTool;
use App\Services\Ai\Tools\GetCurrentTime\GetCurrentTimeTool;
use App\Services\Ai\Tools\LoadExistingPizzaPresets\LoadExistingPizzaPresetsTool;
use App\Services\Ai\Tools\LoadExistingToppings\LoadExistingToppingsTool;
use App\Services\Ai\Tools\LoadOrdersDetailsForPeriod\LoadOrdersDetailsForPeriodTool;
use App\Services\Ai\Tools\ToolBox;
use App\Services\Ai\Tools\UpdateHotFlags\UpdateHotFlagsTool;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class AgentModel implements Agent
{
    public const PERIOD_DAY = 'day';

    public const PERIOD_WEEK = 'week';

    private const AGENT_NAME = 'OrdersAnalyzer';

    private const GUIDELINES_PATH = 'app/Services/Ai/OrdersAnalyzer/INSTRUCTIONS.md';

    private const MAX_AGENT_TURNS_PER_RUN = 20;

    /**
     * @var string
     */
    private string $modelName;

    /**
     * @var string
     */
    private string $period;

    /**
     * @var array
     */
    private array $history = [];

    /**
     * @var ToolBox
     */
    private ToolBox $toolBox;

    /**
     * @var int
     */
    private int $turns = 0;

    private TrackingMeta $metadata;

    public function __construct(?string $modelName = null, string $period = self::PERIOD_DAY)
    {
        $this->modelName = $modelName ?? config('ai.openai.default_model');
        $this->period = $period;
        $this->metadata = new TrackingMeta(self::AGENT_NAME);
        $this->toolBox = new ToolBox([
            new GetCurrentTimeTool,
            new LoadExistingPizzaPresetsTool,
            new LoadExistingToppingsTool,
            new LoadOrdersDetailsForPeriodTool,
            new UpdateHotFlagsTool,
            new AnalysisReportTool,
        ]);
    }

    /**
     * @return string
     */
    public function getAgentName(): string
    {
        return self::AGENT_NAME;
    }

    /**
     * @return string
     *
     * @throws ConnectionException
     * @throws RequestException
     * @throws TooManyTurnsException
     */
    public function runModel(): ResponseWrapper
    {
        $this->addToHistory([
            'role' => 'user',
            'content' => "Analyze orders for {$this->period} and give me summary info on them.",
        ]);

        while (true) {
            if ($this->turns > self::MAX_AGENT_TURNS_PER_RUN) {
                throw new TooManyTurnsException('Model took more than ' . self::MAX_AGENT_TURNS_PER_RUN . ' turns and likely got into infinite loop.');
            }
            $this->turns++;

            $response = $this->makeRequest();
            foreach ($response->getOutputItems() as $outputItem) {
                $this->addToHistory($outputItem);
            }

            if (!$response->hasFunctionCalls()) {
                return $response;
            }

            foreach ($response->getFunctionCalls() as $functionCall) {
                $toolResult = $this->toolBox->useTool($functionCall, $response->getTrackingMeta());
                $this->addToHistory($toolResult);
            }
        }
    }

    /**
     * @return string
     */
    protected function initialInstruction(): string
    {
        return <<<'TXT'
            You are an order analyst for a pizza shop. You read the shop's recent order history
            and turn it into two concrete outcomes: an updated set of featured ("hot") presets,
            and recommendations for new presets the shop should add to its menu.

            Base every claim on the data returned by the tools. If the data is thin, say so
            rather than inventing a trend. Follow the processing rules below exactly.
            TXT;
    }

    /**
     * @return string
     */
    protected function personality(): string
    {
        $instructions = $this->initialInstruction();
        $guidelinesPath = base_path(self::GUIDELINES_PATH);
        if (file_exists($guidelinesPath)) {
            $instructions .= PHP_EOL . PHP_EOL . file_get_contents($guidelinesPath);
        }

        return $instructions;
    }

    /**
     * @param array $dialogItem
     */
    private function addToHistory(array $dialogItem): void
    {
        $this->history[] = $dialogItem;
    }

    /**
     * @return ResponseWrapper
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    private function makeRequest(): ResponseWrapper
    {
        $response = Http::withToken(config('ai.openai.key'))
            ->post(config('ai.openai.url'), [
                'model' => $this->modelName,
                'instructions' => $this->personality(),
                'input' => $this->history,
                'tools' => $this->toolBox->definitions(),
                'metadata' => $this->metadata->toArray(),
            ])
            ->throw()
            ->json();

        return new ResponseWrapper((new TokensLoggerWrapper($response, $this->metadata))->getRawResponse());
    }
}
