<?php

namespace App\DTO;

use App\Enums\MessageSources;
use App\Models\GPTBot;
use Spatie\LaravelData\Data;

class TelegramMessageData extends MessengerMessageData
{
    public function __construct(
        public string           $identifier,
        public string           $text,
        public MessageSources   $source = MessageSources::Telegram,
        public ?int             $bot = null,
    )
    {
        parent::__construct(
            $identifier,
            $text,
            $this->source,
            $this->bot
        );
    }
}
