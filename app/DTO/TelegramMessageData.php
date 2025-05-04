<?php

namespace App\DTO;

use App\Enums\MessageSources;
use Spatie\LaravelData\Data;

class TelegramMessageData extends MessengerMessageData
{
    public function __construct(
        public string         $identifier,
        public string         $text,
        public MessageSources $source = MessageSources::Telegram,
    )
    {
        parent::__construct(
            $identifier,
            $text,
            $this->source
        );
    }
}
