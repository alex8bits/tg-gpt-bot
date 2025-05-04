<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';

    public function handle()
    {
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button('/remember_us')
            ]);

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
