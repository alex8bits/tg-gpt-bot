<?php

namespace App\Enums;

enum BotTypes: string
{
    case COMMON = 'обычный';
    case WELCOME = 'приветственный';
    case SPREADER = 'распределитель';
    case MODERATOR = 'модератор';
}
