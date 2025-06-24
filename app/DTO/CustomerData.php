<?php

namespace App\DTO;

use App\Enums\MessageSources;
use Spatie\LaravelData\Data;
use Telegram\Bot\Objects\Update;

class CustomerData extends Data
{
    public function __construct(
        public string $identifier,
        public ?string $name = null,
    )
    {}

    public static function fromUpdate(Update $update): self
    {
        return new self(
            identifier: $update->getChat()->id,
            name: $update->getChat()->first_name,
        );
    }
}
