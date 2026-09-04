<?php

return [
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/responses'),
        'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-5.6-luna'),
    ],
    'limits' => [
        'daily_agent_quota' => env('AGENT_DAILY_TOKEN_QUOTA', 200_000),
    ],
];
