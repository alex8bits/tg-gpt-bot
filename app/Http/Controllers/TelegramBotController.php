<?php

namespace App\Http\Controllers;

use App\DTO\TelegramMessageData;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Services\ChatService;
use App\Services\GptServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function __construct(protected  ChatService $chatService)
    {}

    public function setWebhook()
    {
        $result = Telegram::setWebhook(['url' => config('app.url') . '/api/v1/botwebhook/' . config('telegram.bots.mybot.token')]);

        return response()->json($result);
    }

    public function unsetWebhook()
    {
        $result = Telegram::deleteWebhook();

        return response()->json($result);
    }

    public function handleWebhook($token, Request $request, GptServiceInterface $gptService)
    {
        Telegram::commandsHandler(true);
        if (isset($request->callback_query) || (isset($request->message['entities']) && $request->message['entities'][0]['type'] == 'bot_command')) {
            return false;
        }

        $bot = new Api($token);

        $message = new TelegramMessageData($request->message['from']['id'], $request->message['text'], MessageSources::Telegram);
        MessageReceivedEvent::dispatch($message);

        $response = $this->chatService->sendMessage($message, Cache::get($request->message['from']['id'] . '_prompt'));
        if (!$response) {
            return false;
        }

        $message = new TelegramMessageData($request->message['from']['id'], $response, MessageSources::Telegram);
        MessageReceivedEvent::dispatch($message, 'assistant');
        $bot->sendMessage([
            'chat_id' => $message->identifier,
            'text' => $response
        ]);
    }
}
