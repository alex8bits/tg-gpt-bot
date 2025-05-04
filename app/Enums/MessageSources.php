<?php

namespace App\Enums;

enum MessageSources
{
    case Telegram;

    public function identifierField()
    {
        return match ($this) {
            self::Telegram => 'telegram_id',
        };
    }
}
