<?php

namespace App\Listeners;

use App\Events\DebugEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendDebugMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DebugEvent $event): void
    {
        $message = $event->message;
        if (!$message || !config('telegram.service_chat') || config('telegram.service_chat') == '') {
            return;
        }

        Log::debug('sendDebugMessage', ['message' => $message]);

        Telegram::sendMessage([
            'chat_id' => config('telegram.service_chat'),
            'text' => $message
        ]);
    }
}
