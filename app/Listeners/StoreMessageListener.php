<?php

namespace App\Listeners;

use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use App\Models\Customer;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        $customer = Customer::firstOrCreate([
            $event->messageData->source->identifierField() => $event->messageData->identifier
        ]);

        Message::create([
            'customer_id' => $customer->id,
            'role' => $event->role,
            'content' => $event->messageData->text,
        ]);
    }
}
