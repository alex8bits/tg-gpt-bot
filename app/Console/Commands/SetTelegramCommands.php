<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class SetTelegramCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-telegram-commands';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bot = new Api();

        $bot->setMyCommands([
            'commands' => [
                ['command' => 'start', 'description' => 'Start']
            ]
        ]);

        $this->info('Telegram commands set successfully.');
    }
}
