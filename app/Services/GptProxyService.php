<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GptProxyService implements GptServiceInterface
{
    protected $bot_url;
    protected $prompt;
    protected $model;
    protected $classifier_model;
    protected $max_tokens;
    protected $temperature;

    public function __construct()
    {
        $this->bot_url = config('open_ai.bot_url');
        $this->prompt = config('open_ai.prompt');
        $this->model = config('open_ai.model');
        $this->classifier_model = config('open_ai.classifier_model');
        $this->max_tokens = (int)(config('open_ai.max_tokens'));
        $this->temperature = (config('open_ai.temperature'));
    }

    public function sendMessages(array $messages, string $prompt = null): array
    {
        $messages[0]->content = $prompt ?? $this->prompt;

        $response = Http::post($this->bot_url, [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature,
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

    public function sendMessagesToClassifier(array $messages, $schema, string $prompt = null): array
    {
        $messages[0]->content = $prompt ?? $this->prompt;

        $response = Http::post($this->bot_url, [
            'model' => $this->classifier_model,
            'messages' => $messages,
            'max_completion_tokens' => $this->max_tokens,
            'temperature' => 1,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => $schema
            ]
        ]);

        if ($response->failed()) {
            Log::emergency('Ошибка запроса: ' . $response->body());
            throw new \Exception('Ошибка запроса: ' . $response->body());
        }
        $data = $response->json();
        Log::debug('GptProxyService classifier answers', ['data' => $data]);

        $answers = [];
        foreach ($data['choices'] as $choice) {
            $answers[] = $choice['message']['content'];
        }

        return $answers;
    }
}
