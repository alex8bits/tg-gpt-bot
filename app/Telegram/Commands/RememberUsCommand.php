<?php

namespace App\Telegram\Commands;

use App\DTO\TelegramMessageData;
use App\Enums\BotTypes;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
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

    public function __construct(protected GptServiceInterface $gptService)
    {
    }

    public function handle()
    {
        $update = $this->getUpdate();

        $id = $update->getMessage()->getChat()->getId();

        $message = ChatService::greet($id, MessageSources::Telegram, $this->gptService);

        MessageReceivedEvent::dispatch($message, 'assistant');
        Telegram::sendMessage([
            'chat_id' => $message->identifier,
            'text' => $message->text
        ]);

        /** @var GPTBot $bot */
        $bot = GPTBot::whereType(BotTypes::COMMON)->inRandomOrder()->first();
        Cache::put($id . '_current_bot', $bot->id);

        $message = new TelegramMessageData($id, $bot->prompt, MessageSources::Telegram, $bot->id);
        Telegram::sendMessage([
            'chat_id' => $message->identifier,
            'text' => $message->text
        ]);
    }
}
