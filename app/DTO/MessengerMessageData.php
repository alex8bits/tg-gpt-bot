<?php

namespace App\DTO;

use App\Enums\MessageSources;
use App\Models\GPTBot;
use Spatie\LaravelData\Data;

class MessengerMessageData extends Data
{
    public function __construct(
        public string           $identifier,
        public string           $text,
        public MessageSources   $source = MessageSources::Telegram,
        public ?int             $bot = null,
    )
    {}
}
