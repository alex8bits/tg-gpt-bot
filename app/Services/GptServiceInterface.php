<?php

namespace App\Services;

interface GptServiceInterface
{
    public function sendMessages(array $messages, string $prompt = null): array;
}
