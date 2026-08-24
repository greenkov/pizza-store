<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class BasicModel
{
    /**
     * @var string
     */
    protected string $modelName;

    public function __construct(?string $modelName = null)
    {
        $this->modelName = $modelName ?? config('ai.openai.default_model');
    }

    /**
     * @return string
     */
    protected function initialInstruction(): string
    {
        return 'You are helpful assistant.';
    }

    private function makeRequest(): mixed
    {
        return Http::withToken(config('ai.openai.key'))
            ->post(config('ai.openai.url'), [
                'model' => $this->modelName,
                'instructions' => $this->initialInstruction(),
                'input' => [
                    ['role' => 'user', 'content' => 'How are you?'],
                ],
                //                'tools' => [
                //                    [
                //                        'type' => 'function',
                //                        'name' => 'get_current_time',
                //                        'description' => 'Returns current system time on a laptop as an ISO8601 string.',
                //                    ],
                //                    [
                //                        'type' => 'function',
                //                        'name' => 'read_file',
                //                        'description' => "Reads a file within project root folder and returns it's content.",
                //                        'parameters' => [
                //                            'type' => 'object',
                //                            'properties' => [
                //                                'path' => [
                //                                    'type' => 'string',
                //                                    'description' => 'Relative path to file.',
                //                                ],
                //                            ],
                //                            'required' => ['path'],
                //                            'additionalProperties' => false,
                //                        ],
                //                        'strict' => true,
                //                    ],
                //                ],
            ])
            ->throw()
            ->json();
    }
}
