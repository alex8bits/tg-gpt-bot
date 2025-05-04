<?php

return [
    'enabled' => env('OPEN_AI_ENABLED', true),
    'api_key' => env('OPEN_AI_API_KEY'),
    'model' => env('OPEN_AI_MODEL', 'gpt-3.5-turbo'),
    'url' => env('OPEN_AI_URL', 'https://api.openai.com/v1/chat/completions'),
    'bot_url' => env('OPEN_AI_BOT_URL'),
    'prompt' => env('OPEN_AI_DEFAULT_PROMPT'),
    'max_tokens' => env('OPEN_AI_MAX_TOKENS', 300),
    'user_context_threshold' => env('OPEN_AI_USER_CONTEXT_THRESHOLD', 2),
];
