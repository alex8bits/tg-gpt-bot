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

        /** @var Customer $customer */
        $customer = Customer::firstOrCreate([
            'telegram_id' => $update_message->getChat()->id,
        ], [
            'name' => $update_message->getChat()->name,
        ]);
        $dialog = Cache::get($update_message->getChat()->id . '_dialog');

        $current_bot = GPTBot::find(Cache::get($update_message->getChat()->id . '_current_bot')) ?? GPTBot::whereType(BotTypes::WELCOME)->first();
        $message = new TelegramMessageData($update_message->getChat()->id, $update_message->getText(), MessageSources::Telegram, $current_bot->id);
        event(new MessageReceivedEvent($message, dialog_id: $dialog));

        //Оцениваем ответ и выбираем следующего бота
        $next_bot = $this->chatService->selectNextBot($dialog);
        if ($next_bot == 0) {
            //TODO: moderate
        } elseif ($next_bot != $current_bot->id) {
            $current_bot = GPTBot::find($next_bot);
            Cache::put($customer->telegram_id . '_current_bot', $next_bot);
        }
        $response = $this->chatService->sendMessage($message, $current_bot, $current_bot->prompt, $dialog, $customer);

        if (!$response) {
            return false;
        }

        $message = new TelegramMessageData($request->message['from']['id'], $response, MessageSources::Telegram);
        MessageReceivedEvent::dispatch($message, 'assistant', $dialog);
        $bot->sendMessage([
            'chat_id' => $message->identifier,
            'text' => $response
        ]);
    }
}
