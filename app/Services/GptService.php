<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GptService implements GptServiceInterface
{
    protected $url;
    protected $api_key;
    protected $model;
    protected $classifier_model;
    protected $prompt;
    protected $max_tokens;
    protected $temperature;

    public function __construct()
    {
        $this->url = config('open_ai.url');
        $this->api_key = config('open_ai.api_key');
        $this->model = config('open_ai.model');
        $this->classifier_model = config('open_ai.classifier_model');
        $this->prompt = config('open_ai.prompt');
        $this->max_tokens = (int)(config('open_ai.max_tokens'));
        $this->temperature = config('open_ai.temperature');
    }

    public function sendMessages(array $messages, string $prompt = null): array
    {
        $messages[0]['content'] = $prompt ?? $this->prompt;

        $response = Http::withToken($this->api_key)->post($this->url, [
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
        $answers = [];
        foreach ($data['choices'] as $choice) {
            $answers[] = $choice['message']['content'];
        }

        return $answers;
    }

    public function sendMessagesToClassifier(array $messages, $schema, string $prompt = null): array
    {
        $messages[0]['content'] = $prompt ?? $this->prompt;

        $response = Http::withToken($this->api_key)->post($this->url, [
            'model' => $this->classifier_model,
            'messages' => $messages,
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature,
            'response_format' => [
                'type' => 'json_schema',
                'schema' => $schema
            ]
        ]);
        if ($response->failed()) {
            Log::emergency('Ошибка запроса: ' . $response->body());
            throw new \Exception('Ошибка запроса: ' . $response->body());
        }
        $data = $response->json();
        $answers = [];
        foreach ($data['choices'] as $choice) {
            $answers[] = $choice['message']['content'];
        }

        return $answers;
    }
}
