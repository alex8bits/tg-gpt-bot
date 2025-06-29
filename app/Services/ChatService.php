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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class ChatService
{
    public function __construct(protected GptServiceInterface $gptService)
    {}

    public function sendMessage(MessengerMessageData $messageData, GPTBot $bot, $prompt = null, $dialog_id = null, Customer $customer = null)
    {
        if (!config('open_ai.enabled')) {
            return false;
        }
        /** @var Customer $customer */
        $customer = $customer ?: Customer::firstOrCreate([$messageData->source->identifierField() => $messageData->identifier]);
        $user_messages = $dialog_id ? $customer->messages()->whereDialogId($dialog_id) : $customer->messages();
        $user_messages = $user_messages->where('gpt_bot_id', $bot->id)->get();
        $messages = [];
        $messages[] = new GptMessageData('system', $prompt ?? config('open_ai.prompt'));
        /** @var Message $message */
        foreach ($user_messages as $user_message) {
            $messages[] = new GptMessageData($user_message->role, $user_message->content);
        }
        $messages[] = new GptMessageData('user', $messageData->text);

        $response = $this->gptService->sendMessages($messages, $prompt);

        return $response[0];
    }

    public function selectNextBot($dialog_id)
    {
        $messagesModel = Message::whereDialogId($dialog_id)->get();
        $messages = [];
        foreach ($messagesModel as $item) {
            $messages[] = GptMessageData::from($item);
        }
        $themes = GPTBot::common()->select('id', 'theme')->get();
        /** @var GPTBot $spreader */
        $spreader = GPTBot::spreader()->first();
        $response = $this->gptService->sendMessages($messages, $spreader->prompt . '. Темы: ' . json_encode($themes));
        $result = json_decode($response[0]);

        return $result->id;
    }

    public static function greet($chat_id, MessageSources $source, $gptService)
    {
        if (!config('open_ai.enabled')) {
            return false;
        }

        /** @var GPTBot $greeter */
        $greeter = GPTBot::whereType(BotTypes::WELCOME)->first();

        /** @var Customer $customer */
        $customer = Customer::firstOrCreate([$source->identifierField() => $chat_id]);

        $messages = [];
        $messages[] = new GptMessageData('system', $prompt ?? config('open_ai.prompt'));

        $greeting_text = $customer->name ? 'Пользователя зовут ' . $customer->name . '. ' : '';
        $greeting_text .= $greeter->prompt;
        $messages[] = new GptMessageData('user', $greeting_text);

        $response = $gptService->sendMessages($messages);

        $message = new TelegramMessageData($chat_id, $response[0], MessageSources::Telegram, $greeter->id);

        return $message;
    }

    public static function firstMessage($chat_id, MessageSources $source, $gptService)
    {
        /** @var GPTBot $bot */
        $bot = GPTBot::whereType(BotTypes::COMMON)->inRandomOrder()->first();
        Cache::put($chat_id . '_current_bot', $bot->id);

        /** @var Customer $customer */
        $customer = Customer::firstOrCreate([$source->identifierField() => $chat_id]);

        $messages = [];
        $messages[] = new GptMessageData('system', config('open_ai.prompt'));
        $messages[] = new GptMessageData('user', $bot->prompt);

        $response = $gptService->sendMessages($messages);

        $message = new TelegramMessageData($chat_id, $response[0], MessageSources::Telegram, $bot->id);

        return $message;
    }
}
