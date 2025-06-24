<?php

namespace App\Telegram\Commands;

use App\DTO\TelegramMessageData;
use App\Enums\BotTypes;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\GPTBot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\CallbackQuery;

class RememberUsCommand extends Command
{
    protected string $name = 'remember_us';
    protected string $description = 'Помните нас';

    public function handle()
    {
        $update = $this->getUpdate();

        $id = $update->getMessage()->getChat()->getId();

        /** @var GPTBot $greeter */
        $greeter = GPTBot::whereType(BotTypes::GREETER)->first();
        /** @var GPTBot $next */
        $next = GPTBot::whereType(BotTypes::COMMON)->first();

        $text = $greeter->prompt;

        $message = new TelegramMessageData(
            identifier: $id,
            text: $text,
            source: MessageSources::Telegram,
            bot: $greeter);
        Log::debug('messageData', ['data' => $message]);
        MessageReceivedEvent::dispatch($message, 'assistant');

        Cache::put($id . "_prompt", config('open_ai.prompt') . ' ' . $greeter->prompt);
        Cache::put($id . "_current_bot", $greeter->id);
        Cache::put($id . "_next_bot", $next->id);

        Telegram::sendMessage([
            'chat_id' => $id,
            'text' => $text
        ]);
    }
}
