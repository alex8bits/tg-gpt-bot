<?php

namespace App\Services;

use App\DTO\GptMessageData;
use App\DTO\MessengerMessageData;
use App\DTO\TelegramMessageData;
use App\Enums\BotTypes;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\Customer;
use App\Models\Dialog;
use App\Models\GPTBot;
use App\Models\MainBot;
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
        $welcome = GPTBot::whereType(BotTypes::WELCOME)->first()?->id;
        /** @var Customer $customer */
        $customer = $customer ?: Customer::firstOrCreate([$messageData->source->identifierField() => $messageData->identifier]);
        $user_messages = $dialog_id ? $customer->messages()->whereDialogId($dialog_id) : $customer->messages();
        $user_messages = $user_messages->whereIn('gpt_bot_id', [$welcome, $bot->id])->get();
        $messages = [];
        $messages[] = new GptMessageData('system', $prompt ?? config('open_ai.prompt'));
        foreach ($user_messages as $user_message) {
            $messages[] = new GptMessageData($user_message->role, $user_message->content);
        }
        $messages[] = new GptMessageData('user', $messageData->text);
        Log::info('messages', ['data' => $messages]);
        Log::info('ChatService->sendMessage');
        $response = $this->gptService->sendMessages($messages, $prompt);

        return $response[0];
    }

    public function moderate($dialog_id, $last_message)
    {
        /** @var Dialog $dialog */
        $dialog = Dialog::find($dialog_id);
        /** @var MainBot $main_bot */
        $main_bot = MainBot::find($dialog->main_bot_id);
        if (!$main_bot) {
            $dialog->update(['main_bot_id' => MainBot::first()->id]);
        }
        $messagesModel = Message::whereDialogId($dialog_id)->get();
        $messages = [];
        foreach ($messagesModel as $index => $item) {
            if ($index == 0) continue;
            $messages[] = GptMessageData::from($item);
        }
        $messages[] = new GptMessageData('user', $last_message);

        /** @var GPTBot $moderator */
        $moderator = GPTBot::moderator()->whereIn('id', $main_bot->getBotsIds())->first();

        if (!$moderator) {
            return 'Ошибка: нет модератора для основного бота "' . $main_bot->name . '", id:' . $main_bot->id;
        }

        $prompt = $main_bot->prompt . ' ' . $moderator->getPrompt();
        Log::info('ChatService->moderate');
        $response = $this->gptService->sendMessages($messages, $prompt);
        $result = $response[0];
        Log::debug('moderate response', [
            'result' => $result,
            'response' => $response,
            //'prompt' => $prompt,
            'messages' => $messages,
        ]);

        $moderator_response = "Отвечает модератор `$moderator->name` id: $moderator->id" . PHP_EOL;

        return $moderator_response . $result;
    }

    public function selectNextBot($dialog_id, $last_message = null, GPTBot $current_bot = null)
    {
        /** @var Dialog $dialog */
        $dialog = Dialog::find($dialog_id);
        /** @var MainBot $main_bot */
        $main_bot = MainBot::find($dialog->main_bot_id);
        if (!$main_bot) {
            $dialog->update(['main_bot_id' => MainBot::first()->id]);
        }
        $messagesModel = Message::whereDialogId($dialog_id)->latest()->limit(5)->get()->reverse();
        $messages = [];
        foreach ($messagesModel as $index => $item) {
            if ($index == 0) continue;
            $messages[] = GptMessageData::from($item);
        }
        $messages[] = new GptMessageData('user', $last_message);
        $themes = GPTBot::whereIn('id', $main_bot->getBotsIds())->select('id', 'theme')->get();
        $themes_string = '';
        foreach ($themes as $theme) {
            $themes_string .= $theme->id . ': ' . $theme->theme . '. ';
        }
        $last_theme = $current_bot ? ' Последняя тема была: ' . $current_bot->theme : '';
        /** @var GPTBot $spreader */
        $spreader = GPTBot::spreader()->first();
        $prompt = $spreader->getPrompt($dialog_id) . '. Темы: ' . $themes_string . $last_theme;
        $schema = [
            'name' => 'theme_detecting',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'id выбранной темы'
                    ],
                    'impatience' => [
                        'type' => 'integer',
                        'description' => 'уровень раздражения пользователя от 0 (доброжелателен) до 100 (максимально раздражён)'
                    ],
                    'sociability' => [
                        'type' => 'integer',
                        'description' => 'желание пользователя продолжать общение от 0 до 100'
                    ]
                ]
            ],

        ];
        Log::info('ChatService->selectNextBot');
        $response = $this->gptService->sendMessagesToClassifier($messages, $schema, $prompt);
        $result = json_decode($response[0]) ?? $response[0];
        Log::debug('selectNextBot response', [
            'result' => $result,
            'response' => $response,
            //'prompt' => $prompt,
            'messages' => $messages,
        ]);

        return $result;
    }

    public static function greet($chat_id, MessageSources $source, $gptService, GPTBot $greeter = null)
    {
        if (!config('open_ai.enabled')) {
            return false;
        }

        /** @var GPTBot $greeter */
        $greeter = $greeter ?? GPTBot::whereType(BotTypes::WELCOME)->first();

        /** @var Customer $customer */
        $customer = Customer::firstOrCreate([$source->identifierField() => $chat_id]);

        $messages = [];
        $messages[] = new GptMessageData('system', $greeter->getPrompt() ?? config('open_ai.prompt'));

        $greeting_text = $customer->name ? 'Пользователя зовут ' . $customer->name . '. ' : '';
        $greeting_text .= $greeter->getPrompt();
        $messages[] = new GptMessageData('user', $greeting_text);

        Log::info('ChatService->greet');
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
        $messages[] = new GptMessageData('user', $bot->getPrompt());

        Log::info('ChatService->firstMessage');
        $response = $gptService->sendMessages($messages);

        $message = new TelegramMessageData($chat_id, $response[0], MessageSources::Telegram, $bot->id);

        return $message;
    }
}
