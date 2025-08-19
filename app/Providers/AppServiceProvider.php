<?php

namespace App\Providers;

use App\Services\GptProxyService;
use App\Services\GptDirectService;
use App\Services\GptServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Events\UpdateEvent;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('open_ai.bot_url')) {
            $this->app->bind(GptServiceInterface::class, GptProxyService::class);
        } else {
            $this->app->bind(GptServiceInterface::class, GptDirectService::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Telegram::on('callback_query', function (UpdateEvent $updateEvent) {

            $update = $updateEvent->update;
            $callbackData = $update->callbackQuery->data;
            $commandName = $this->parseCommandName($callbackData);
            $commands = Telegram::getCommands();

            if (isset($commands[$commandName])) {
                $command = $commands[$commandName];
                if (method_exists($command, 'handleCallback')) {
                    $command->handleCallback($update->callbackQuery);
                }
            }
        });
    }

    private function parseCommandName(string $data): string
    {
        return explode(' ', ltrim($data, '/'))[0];
    }
}
