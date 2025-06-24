<?php

namespace App\Http\Controllers;

use App\DTO\CustomerData;
use App\DTO\TelegramMessageData;
use App\Enums\BotTypes;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\Customer;
use App\Models\GPTBot;
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
        $result = Telegram::setWebhook(['url' => config('app.url') . '/webhook/' . config('telegram.bots.mybot.token')]);

        return response()->json($result);
    }

    public function unsetWebhook()
    {
        $result = Telegram::deleteWebhook();

        return response()->json($result);
    }

    public function handleWebhook($token, Request $request)
    {
        Log::debug('handleWebhook', ['data' => $request->all()]);
        Telegram::commandsHandler(true);
        if (isset($request->callback_query) || (isset($request->message['entities']) && $request->message['entities'][0]['type'] == 'bot_command')) {
            return false;
        }

        $bot = new Api($token);
        $update = $bot->getWebhookUpdate();
        $update_message = $update->getMessage();
        $customer = Customer::firstOrCreate([
            'telegram_id' => $update_message->getChat()->id,
        ], [
            'name' => $update_message->getChat()->name,
        ]);
        $current_bot = GPTBot::find(Cache::get($update_message->getChat()->id . '_current_bot')) ?? GPTBot::whereType(BotTypes::GREETER)->first();
        $message = new TelegramMessageData($update_message->getChat()->id, $update_message->getText(), MessageSources::Telegram, $current_bot->id);
        MessageReceivedEvent::dispatch($update_message);

        if (Cache::has($update_message->getChat()->id . '_next_bot')) {
            $next_bot = GPTBot::find(Cache::get($update_message->getChat()->id . '_next_bot'));
            Cache::forget($update_message->getChat()->id . '_next_bot');
        } else {
            $spreader = GPTBot::whereType(BotTypes::SPREADER)->first();
        }
        //TODO: логика подставления next bot или опредлеления следующего бота по контексту предыдущих сообщений

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
