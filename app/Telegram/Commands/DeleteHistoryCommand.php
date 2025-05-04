<?php

namespace App\Telegram\Commands;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\CallbackQuery;

class DeleteHistoryCommand extends Command
{
    protected string $name = 'delete_history';
    protected string $description = 'Удалить переписку';

    public function handle()
    {
        $update = $this->getUpdate();
        $chat_id = $update->getMessage()->getChat()->getId();
        $user = User::whereTelegramId($chat_id)->first();
        $user?->messages()->delete();
        $user->update([
            'rating' => config('open_ai.user_context_threshold')
        ]);

        $this->replyWithMessage([
            'text' => 'История переписки очищена',
            'reply_markup' => Keyboard::remove(),
        ]);
    }

    public function handleCallback(CallbackQuery $query)
    {
        $id = $query->from->id;
        $user = User::whereTelegramId($id)->first();
        $user?->messages()->delete();
        $user->update([
            'rating' => config('open_ai.user_context_threshold')
        ]);

        Telegram::answerCallbackQuery([
            'callback_query_id' => $query->id,
            'text' => 'История переписки очищена',
        ]);
    }
}
