<?php

namespace App\Listeners;

use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\Customer;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StoreMessageListener
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
    public function handle(MessageReceivedEvent $event): void
    {
        Customer::firstOrCreate([
            $event->messageData->source->identifierField() => $event->messageData->identifier
        ]);
        /** @var Customer $customer */
        $customer = Customer::where($event->messageData->source->identifierField(), $event->messageData->identifier)->first();
        Log::debug('store message listener', ['data' => $event->messageData, 'customer' => $customer]);

        Message::create([
            'customer_id' => $customer->refresh()->id,
            'dialog_id' => $event->dialog_id,
            'role' => $event->role,
            'content' => $event->messageData->text,
            'gpt_bot_id' => $event->messageData->bot_id
        ]);
    }
}
