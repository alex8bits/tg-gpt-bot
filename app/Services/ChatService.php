<?php

namespace App\Services;

use App\DTO\GptMessageData;
use App\DTO\MessengerMessageData;
use App\DTO\TelegramMessageData;
use App\Enums\BotTypes;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\Customer;
use App\Models\GPTBot;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

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

        return $response[0];
    }

    public function selectNextBot()
    {
        //TODO: собираем тематики ботов для распределителя и вы бираем следующего в зависисти от ответа пользователя
    }

    public static function greet($chat_id, MessageSources $source, $gptService)
    {
        /** @var GPTBot $greeter */
        $greeter = GPTBot::whereType(BotTypes::WELCOME)->first();

        /** @var Customer $customer */
        $customer = Customer::firstOrCreate([$source->identifierField() => $chat_id]);

        $messages = [];
        $messages[] = new GptMessageData('system', $prompt ?? config('open_ai.prompt'));

        $greeting_text = '';
        if ($customer->name) {
            $greeting_text .= 'Пользователя зовут ' . $customer->name . '. ';
        }
        $greeting_text .= $greeter->prompt;
        $messages[] = new GptMessageData('user', $greeting_text);

        if (!config('open_ai.enabled')) {
            return false;
        }

        $response = $gptService->sendMessages($messages);

        $message = new TelegramMessageData($chat_id, $response[0], MessageSources::Telegram, $greeter->id);

        return $message;
    }
}
