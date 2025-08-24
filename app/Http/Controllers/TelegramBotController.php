<?php

namespace App\Http\Controllers;

use App\DTO\TelegramMessageData;
use App\Enums\BotTypes;
use App\Enums\FeedbackStates;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\Customer;
use App\Models\Dialog;
use App\Models\Feedback;
use App\Models\GPTBot;
use App\Models\MainBot;
use App\Services\ChatService;
use App\Services\GptServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function __construct(protected  ChatService $chatService, protected GptServiceInterface $gptService)
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

    public function getInfo()
    {
        $result = Telegram::getWebhookInfo();

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
        Log::debug('$update_message', ['$update_message' => $update_message]);
        if (!$update_message->getText()) {
            return false;
        }

        /** @var Customer $customer */
        $customer = Customer::firstOrCreate([
            'telegram_id' => $update_message->getChat()->id,
        ], [
            'name' => $update_message->getChat()->name,
        ]);

        //Если введено название системного бота, начинаем новый диалог с ним
        if (MainBot::whereNotNull('starting_bot')->whereSystemName($update_message->getText())->exists()) {
            $this->startNewDialog($customer, $update_message->getText());

            return false;
        }

        $dialog = Cache::get($update_message->getChat()->id . '_dialog');
        if (!$dialog) {
            $main_bot = MainBot::whereNotNull('starting_bot')->first();
            $dialog = Dialog::create([
                'main_bot_id' => $main_bot?->id
            ]);
            Cache::put($update_message->getChat()->id . '_dialog', $dialog->id);
            if ($main_bot) {
                Cache::put($update_message->getChat()->id . '_main_bot', $main_bot->id);
            }
            $dialog = $dialog->id;
        }

        /** @var GPTBot $current_bot */
        $current_bot =
            GPTBot::find(Cache::get($update_message->getChat()->id . '_current_bot'))
            ??
            GPTBot::whereType(BotTypes::WELCOME->value)->first();

        $message = new TelegramMessageData($update_message->getChat()->id, $update_message->getText(), MessageSources::Telegram, $current_bot->id);
        event(new MessageReceivedEvent($message, dialog_id: $dialog));

        //Оцениваем ответ и выбираем следующего бота
        $next_bot = $this->chatService->selectNextBot($dialog, $update_message->getText(), $current_bot);
        if (isset($next_bot->id) && $next_bot->id > 0) {
            /** @var GPTBot $current_bot */
            $current_bot = GPTBot::find($next_bot->id);
            Cache::put($customer->telegram_id . '_current_bot', $next_bot->id);
            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => '**Debug**'. PHP_EOL .
                    'Бот: ' . $current_bot->name . PHP_EOL .
                    'ID: ' . $current_bot->id . PHP_EOL .
                    'Раздражение: ' . $next_bot->impatience . PHP_EOL .
                    'Общительность: ' . $next_bot->sociability,
            ]);
        } elseif (isset($next_bot->id) && $next_bot->id == 0) {
            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => '**Debug**' . PHP_EOL . 'Тема не определена' . PHP_EOL .
                    'Раздражение: ' . $next_bot->impatience . PHP_EOL .
                    'Общительность: ' . $next_bot->sociability
            ]);

            $response = $this->chatService->moderate($dialog, $update_message->getText());
            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => $response
            ]);
            $message = new TelegramMessageData($update_message->getChat()->id, $response, MessageSources::Telegram, $current_bot->id);
            event(new MessageReceivedEvent($message, 'assistant', $dialog));
            return false;
        } else {
            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => '**Debug**' . PHP_EOL .
                    'Распределитель ответил не по заданию. Он сказал: ' . $next_bot,
            ]);
        }

        $response = $this->chatService->sendMessage($message, $current_bot, $current_bot->getPrompt($dialog), $dialog, $customer);

        if (!$response) {
            return false;
        }

        $message = new TelegramMessageData($update_message->getChat()->id, $response, MessageSources::Telegram, $current_bot->id);
        event(new MessageReceivedEvent($message, 'assistant', $dialog));
        $bot->sendMessage([
            'chat_id' => $message->identifier,
            'text' => $response
        ]);
        //Работа с претензией
        if ($current_bot->type == BotTypes::FEEDBACK) {
            $message = new TelegramMessageData($update_message->getChat()->id, $current_bot->system_request, MessageSources::Telegram, $current_bot->id);
            $feedback_response = $this->chatService->sendMessage($message, $current_bot, $current_bot->getPrompt(), $dialog, $customer);
            Log::debug('$feedback_response', ['data' => $feedback_response]);
            if (json_decode($feedback_response)) {
                $feedback_response = json_decode($feedback_response);
                if ($feedback_response->claim === 0) {
                    $result = 'Не удалось понять суть претензии';
                } elseif ($feedback_response->claim === 1) {
                    $result = 'Требуется уточнить суть претензии';
                } elseif ($feedback_response->claim === 2) {
                    $result = 'Cуть претензии ясна. ' . $feedback_response->text;
                    Feedback::create([
                        'customer_id' => $customer->id,
                        'text' => $feedback_response->text,
                        'status' => FeedbackStates::NEW,
                        'bot_type' => BotTypes::FEEDBACK
                    ]);
                }
                Telegram::sendMessage([
                    'chat_id' => $customer->telegram_id,
                    'text' => '**Debug**' . PHP_EOL .
                        'Обработка претензии. ' . $result,
                ]);
            }
        }
        if ($current_bot->type == BotTypes::CALLBACK) {
            $phone = null;
            if ($customer->phone) {
                $phone = 'Номер телефона у нас есть, уточнять его не нужно: ' . $customer->phone;
            }
            $message = new TelegramMessageData($update_message->getChat()->id, $message->text, MessageSources::Telegram, $current_bot->id);
            $feedback_response = $this->chatService->sendMessage($message, $current_bot, $current_bot->system_request . $phone, $dialog, $customer);
            Log::debug('$feedback_response', ['data' => $feedback_response]);
            if (json_decode($feedback_response)) {
                $feedback_response = json_decode($feedback_response);
                if ($feedback_response->claim === 0) {
                    $result = 'Не хватает данных';
                } elseif ($feedback_response->claim === 1) {
                    $result = 'Вся информация есть. ' . $feedback_response->content;
                    if (!$customer->phone) {
                        $customer->update(['phone' => $feedback_response->phone]);
                    }
                    Feedback::create([
                        'customer_id' => $customer->id,
                        'text' => $feedback_response->content,
                        'status' => FeedbackStates::NEW,
                        'bot_type' => BotTypes::CALLBACK
                    ]);
                }
                Telegram::sendMessage([
                    'chat_id' => $customer->telegram_id,
                    'text' => '**Debug**' . PHP_EOL .
                        'Заказ обратного звонка. ' . $result,
                ]);
            };
        }
        if ($current_bot->type == BotTypes::COURIER) {
            $message = new TelegramMessageData($update_message->getChat()->id, $message->text, MessageSources::Telegram, $current_bot->id);
            $feedback_response = $this->chatService->sendMessage($message, $current_bot, $current_bot->system_request, $dialog, $customer);
            Log::debug('$courier_response', ['data' => $feedback_response]);
            if (json_decode($feedback_response)) {
                $feedback_response = json_decode($feedback_response);
                if ($feedback_response->claim === 0) {
                    $result = 'Не хватает данных';
                } elseif ($feedback_response->claim === 1) {
                    $result = 'Вся информация есть. ' . $feedback_response->content;
                    if (!$customer->phone) {
                        $customer->update(['phone' => $feedback_response->phone ?? null]);
                    }
                    Feedback::create([
                        'customer_id' => $customer->id,
                        'text' => $feedback_response->content,
                        'status' => FeedbackStates::NEW,
                        'bot_type' => BotTypes::COURIER
                    ]);
                }
                Telegram::sendMessage([
                    'chat_id' => $customer->telegram_id,
                    'text' => '**Debug**' . PHP_EOL .
                        'Заказ курьера. ' . $result,
                ]);
            };
        }
    }

    private function startNewDialog(Customer $customer, $text)
    {
        $main_bot = MainBot::whereNotNull('starting_bot')->whereSystemName($text)->first();
        $starting_bot = GPTBot::find($main_bot->starting_bot);
        $dialog = Dialog::create([
            'main_bot_id' => $main_bot->id
        ]);
        Cache::put($customer->telegram_id . '_dialog', $dialog->id);
        Cache::put($customer->telegram_id . '_current_bot', $starting_bot->id);
        Cache::put($customer->telegram_id . '_main_bot', $main_bot->id);

        //Приветствие
        $greeting = ChatService::greet($customer->telegram_id, MessageSources::Telegram, $this->gptService);
        MessageReceivedEvent::dispatch($greeting, 'assistant', $dialog->id);

        Telegram::sendMessage([
            'chat_id' => $customer->telegram_id,
            'text' => $greeting->text
        ]);
    }
}
