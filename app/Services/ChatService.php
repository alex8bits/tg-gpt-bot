<?php

namespace App\Services;

use App\DTO\GptMessageData;
use App\DTO\MessengerMessageData;
use App\Enums\MessageSources;
use App\Models\Customer;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(protected GptServiceInterface $gptService)
    {}

    public function sendMessage(MessengerMessageData $messageData, $prompt = null)
    {
        $customer = Customer::firstOrCreate([$messageData->source->identifierField() => $messageData->identifier]);
        if ($customer->rating < 1) {
            return false;
        }
        $user_messages = $customer->messages;
        $messages = [];
        $messages[] = new GptMessageData('system', $prompt ?? config('open_ai.prompt'));
        /** @var Message $message */
        foreach ($user_messages as $user_message) {
            $messages[] = new GptMessageData($user_message->role, $user_message->content);
        }
        $messages[] = new GptMessageData('user', $messageData->text);

        if (!config('open_ai.enabled')) {
            return false;
        }

        $moderate_messages = $messages;
        $moderate_messages[] = new GptMessageData('user', 'оцени по шкале от 0 до 10, на сколько предыдущее сообщение соответствовало контексту общения, где 0 - пользователь говорит на другую тему, 10 - он полностью в контексте. Ответ дай в json например: {"score":10}');
        $moderator_response = $this->gptService->sendMessages($moderate_messages, $prompt);
        Log::debug('moderator_response', ['response' => $moderator_response]);
        try {
            $score = json_decode($moderator_response[0]);
            if ($score->score < 3) {
                $customer->decrement('rating');
                if ($customer->rating === 0) {
                    return "Извините, больше Вас не обеспокоим";
                }
            }
        } catch (\Exception $e) {
            Log::warning('could not get score');
        }

        Log::debug('user rating', ['value' => $customer->rating]);

        $response = $this->gptService->sendMessages($messages, $prompt);

        //TODO: логика выбора наиболее подходящего ответа

        return $response[0];
    }
}
