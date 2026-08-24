<?php

namespace App\Services\Ai\Tools;

use App\Services\Ai\Exceptions\BasicException as AiException;
use Arr;
use Throwable;

final class ToolBox
{
    /**
     * @var array|AbstractTool[]
     */
    private array $tools = [];

    /**
     * @param array|AbstractTool[] $tools
     */
    public function __construct(array $tools)
    {
        foreach ($tools as $tool) {
            $this->tools[$tool->getName()] = $tool;
        }
    }

    public function definitions(): array
    {
        return array_values(array_map(fn ($tool) => $tool->definition(), $this->tools));
    }

    /**
     * @param array $toolCall
     *
     * @return array
     */
    public function useTool(array $toolCall): array
    {
        $name = Arr::get($toolCall, 'name');
        $callId = Arr::get($toolCall, 'call_id');
        $rawArguments = Arr::get($toolCall, 'arguments');

        $arguments = json_decode($rawArguments, true);

        if (Arr::has($this->tools, $name)) {
            try {
                $callResult = Arr::get($this->tools, $name)->use($arguments);
            } catch (AiException $aiException) {
                return [
                    'call_id' => $callId,
                    'type' => 'function_call_output',
                    'output' => $aiException->getMessage(),
                ];
            } catch (Throwable $throwable) {
                report($throwable);

                return [
                    'call_id' => $callId,
                    'type' => 'function_call_output',
                    'output' => 'Error occurred during function call.',
                ];
            }

            return [
                'call_id' => $callId,
                'type' => 'function_call_output',
                'output' => $callResult,
            ];
        }

        return [
            'call_id' => $callId,
            'type' => 'function_call_output',
            'output' => 'No tool found for this call.',
        ];
    }
}
