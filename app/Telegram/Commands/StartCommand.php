<?php

namespace App\Telegram\Commands;

use App\Models\MainBot;
use Illuminate\Support\Str;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';

    public function handle()
    {
        $main_bots = MainBot::all();
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        /** @var MainBot $bot */
        foreach ($main_bots as $bot) {
            $command = Str::replace('//', '/', '/' . $bot->system_name);
            $keyboard->row([
                Keyboard::button($command)
            ]);
        }

        if (!app()->isProduction()) {
            $keyboard->row([
                Keyboard::button('/delete_history')
            ]);
        }

        $this->replyWithMessage([
            'text' => 'Выбери действие',
            'reply_markup' => $keyboard,
        ]);
    }
}
