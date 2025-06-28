<?php

namespace App\Telegram\Commands;

use App\DTO\TelegramMessageData;
use App\Enums\BotTypes;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\Dialog;
use App\Models\GPTBot;
use App\Services\ChatService;
use App\Services\GptServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\CallbackQuery;

class RememberUsCommand extends Command
{
    protected string $name = 'random_scenario';
    protected string $description = 'Помните нас';

    public function __construct(protected GptServiceInterface $gptService, protected ChatService $chatService)
    {
    }

    public function handle()
    {
        $update = $this->getUpdate();

        $customer_tg_id = $update->getMessage()->getChat()->getId();

        $dialog = Dialog::create();
        Cache::put($customer_tg_id . '_dialog', $dialog->id);

        //Приветствие
        $greeting = ChatService::greet($customer_tg_id, MessageSources::Telegram, $this->gptService);
        MessageReceivedEvent::dispatch($greeting, 'assistant', $dialog->id);

        Telegram::sendMessage([
            'chat_id' => $customer_tg_id,
            'text' => $greeting->text
        ]);

        $appeal = ChatService::firstMessage($customer_tg_id, MessageSources::Telegram, $this->gptService);
        MessageReceivedEvent::dispatch($appeal, 'assistant', $dialog->id);

        Telegram::sendMessage([
            'chat_id' => $customer_tg_id,
            'text' => $appeal->text
        ]);
    }
}
