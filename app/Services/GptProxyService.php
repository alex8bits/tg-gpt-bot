<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GptProxyService implements GptServiceInterface
{
    protected $bot_url;
    protected $prompt;
    protected $model;
    protected $max_tokens;

    public function __construct()
    {
        $this->bot_url = config('open_ai.bot_url');
        $this->prompt = config('open_ai.prompt');
        $this->model = config('open_ai.model');
        $this->max_tokens = (int)(config('open_ai.max_tokens'));
    }

    public function sendMessages(array $messages, string $prompt = null): array
    {
        $messages[0]->content = $prompt ?? $this->prompt;

        $response = Http::post($this->bot_url, [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->max_tokens,
        ]);

        if ($response->failed()) {
            Log::emergency('Ошибка запроса: ' . $response->body());
            throw new \Exception('Ошибка запроса: ' . $response->body());
        }
        $data = $response->json();
        Log::debug('GptProxyService answers', ['data' => $data]);

        $answers = [];
        foreach ($data['choices'] as $choice) {
            $answers[] = $choice['message']['content'];
        }

        return $answers;
    }
}
