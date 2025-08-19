<?php

namespace App\Services;

interface GptServiceInterface
{
    public function sendMessages(array $messages, string $prompt = null): array;
    public function sendMessagesToClassifier(array $messages, $schema, string $prompt = null): array;
}
