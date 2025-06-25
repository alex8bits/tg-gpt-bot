<?php

namespace App\DTO;

use App\Enums\MessageSources;

class TelegramMessageData extends MessengerMessageData
{
    public function __construct(
        public string           $identifier,
        public string           $text,
        public MessageSources   $source = MessageSources::Telegram,
        public ?int             $bot_id = null,
    )
    {
        parent::__construct(
            $identifier,
            $text,
            $this->source,
            $this->bot_id
        );
    }
}
