<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class GptMessageData extends Data
{
    public function __construct(
        public string $role,
        public string $content,
    )
    {}
}
